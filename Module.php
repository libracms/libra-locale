<?php

namespace LibraLocale;

use Locale;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    // Disable locale for route
    const DISABLE_LOCALE = 'disable_locale';

    // Parameter name that will be set into RouteMatchs
    const LOCALE_ROUTE_PARAM = 'locale';

    /**
     * Runtime locale alias
     * @var string
     */
    public static $alias;

    /**
     * Runtime locale tag
     * @var string
     */
    public static $locale;

    protected static $options;

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'setOptions'), 1010);
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'addLocaleToRoutes'), 1000);

        // a little hacky but it avoid rewriting of many codes
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'overrideViewHelperUrl'));
    }

    // override url view helper with new locale aware
    public function overrideViewHelperUrl(ModuleEvent $e)
    {
        $serviceLocator = $e->getParam('ServiceManager');
        $viewHelperManager = $serviceLocator->get('ViewHelperManager');
        // Configure URL view helper with router
        $viewHelperManager->setFactory('url', function ($sm) use ($serviceLocator) {
            $helper = new View\Helper\Url;
            $router = \Zend\Console\Console::isConsole() ? 'HttpRouter' : 'Router';
            $helper->setRouter($serviceLocator->get($router));

            $match = $serviceLocator->get('application')
                ->getMvcEvent()
                ->getRouteMatch()
            ;

            if ($match instanceof \Zend\Mvc\Router\RouteMatch) {
                $helper->setRouteMatch($match);
            }

            return $helper;
        });
    }

    public function addLocaleToRoutes(ModuleEvent $e)
    {
        $config = $e->getConfigListener()->getMergedConfig(false);
        $routes = &$config['router']['routes'];

        if (static::getOption('redirect_from_locale_tag')) {
            $availableValueArray = array_unique(
                array_merge(
                    array_keys(static::getLocales()),
                    array_values(static::getLocales())
                )
            );
        } else {
            $availableValueArray = array_keys(static::getLocales());
        }
        $localeConstraint = '(' . implode($availableValueArray, '|') . ')';
        //$localeConstraint = '[a-zA-Z][a-zA-Z0-9_-]*';

        foreach ($routes as $key => &$route) {
            if ($key === 'admin') continue;
            if (isset($route['options'][self::DISABLE_LOCALE])
                && $route['options'][self::DISABLE_LOCALE] !== false
            ) {
                continue;
            }

            if (strtolower($route['type']) === 'literal' || $route['type'] === 'Zend\Mvc\Router\Http\Literal') {
                $route['type'] = 'Segment';
            }
            if (strtolower($route['type']) === 'segment' || $route['type'] === 'Zend\Mvc\Router\Http\Segment') {
                if (strpos($route['options']['route'], ':locale') === false) {
                    $route['options']['route'] = '[/:locale]' . $route['options']['route'];
                }
                if (!isset($route['options']['constraints'])) $route['options']['constraints'] = array();
                $route['options']['constraints'] = array_merge(
                    $route['options']['constraints'],
                    array('locale' => $localeConstraint)
                );
            } elseif (strtolower($route['type']) === 'regex' || $route['type'] === 'Zend\Mvc\Router\Http\Regex') {
                if (strpos($route['options']['route'], '<locale>') === false) {
                    $route['options']['route'] = '(/(?P<locale>'.$localeConstraint.'))?' . $route['options']['route'];
                    $route['options']['spec'] = '/locale' . $route['options']['spec'];  //@todo needs to make optional on assemling
                }
            } else {
                //throw thomesing
            }

            if (!isset($route['options']['defaults'])) $route['options']['defaults'] = array();
            $route['options']['defaults'] = array_merge(
                $route['options']['defaults'],
                array('locale' => static::getDefaultLocaleAlias())
            );
        }

        $e->getConfigListener()->setMergedConfig($config);
    }

    protected function _redirect(MvcEvent $e, $localeAlias = null, $routeName = null)
    {
        $routeMatch = $e->getRouteMatch();
        $statusCode = static::getOption('redirect_code');
        if ($localeAlias !== null) {
            $routeMatch->setParam(self::LOCALE_ROUTE_PARAM, $localeAlias);
        }
        $router = $e->getRouter();
        if ($routeName === null) {
            $routeName = $routeMatch->getMatchedRouteName();
        }

        $routeMatchParams = $routeMatch->getParams();
        if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
            $routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
            unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
        }
        if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
            unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
        }

        $url = $router->assemble($routeMatchParams, array('name' => $routeName));
        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(isset($statusCode) ? $statusCode : 302);
        return $response;
    }

    public function redirectFromLocaleTag(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $localeAlias = $routeMatch->getParam(self::LOCALE_ROUTE_PARAM);
        if ($localeAlias === null) return 0;  //do nothing

        // redirects to the closest alias if you typed a locale tag
        if (!key_exists($localeAlias, static::getLocales())) {
            $locale = $localeAlias;
            //$newLocale = Locale::lookup(static::getLocales(), $locale, false, static::getDefaultLocaleAlias());
            $newAlias = array_search($locale, static::getLocales());
            //$newAlias = static::getAliasByLocale($newLocale);
            if ($newAlias !== $localeAlias) {  // avoiding infinite redirect
                return $this->_redirect($e, $newAlias);
            }
        }

        return 0; //return success
    }

    /**
     * set locale system-wide
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function setDefaultLocale(MvcEvent $e)
    {
        static::$alias = $e->getRouteMatch()->getParam(self::LOCALE_ROUTE_PARAM);
        static::$locale = static::getLocaleByAlias(static::$alias);
        if (Locale::setDefault(static::$locale) === false) {
            throw new \RuntimeException(sprintf(
                'Not valid locale %s to set it as default',
                static::$locale
            ));
        }
    }

    /**
     * executes on boostrap
     * @param \Zend\Mvc\MvcEvent $e
     * @return null
     */
    public function onBootstrap(MvcEvent $e)
    {
        if (static::getOption('redirect_from_locale_tag')) {
            $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'redirectFromLocaleTag'));
        }
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'setDefaultLocale'));
        return;
    }

    public function setOptions(ModuleEvent $e)
    {
        $config = $e->getConfigListener()->getMergedConfig(false);
        static::$options = $config['libra_locale'];
    }

    public static function getOption($option)
    {
        if (!isset(static::$options[$option])) {
            return null;
        }
        return static::$options[$option];
    }

    /**
     * Get list of of available and allowed aliases and locales in format
     * 'alias' => 'locale tag',
     * @return array
     */
    public static function getLocales()
    {
        return static::getOption('locales');
    }

    /**
     * Return locale tag by alias.
     * If alias doesn't exist will return default locale
     *
     * @param string $alias
     * @return sting
     */
    public static function getLocaleByAlias($alias)
    {
        $locales = static::getLocales();
        if (!array_key_exists($alias, $locales)) {
            return false;
            //$alias = static::getDefaultLocaleAlias();
        }

        return $locales[$alias];
    }

    public static function getAliasByLocale($locale)
    {
        $key = array_search($locale, static::getLocales());
        return is_string($key) ? $key : $locale;
    }

    public static function getDefaultLocaleAlias()
    {
        $aliasOrTag = static::getOption('default');
        // explicit take locale alias
        $alias = static::getAliasByLocale($aliasOrTag);

        return $alias;
    }

    public function getControllerPluginConfig()
    {
        return array(
            'invokables' => array(
                'url' => 'LibraLocale\Mvc\Controller\Plugin\Url',
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
