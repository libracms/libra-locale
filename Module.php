<?php

namespace LibraLocale;

use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\ModuleManager\ModuleEvent;
use Zend\Mvc\MvcEvent;
use Locale;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    //const option for enable locale router and others
    const LOCALE_AWARE = 'locale_aware';
    const LOCALE_ROUTE_PARAM = 'locale';

    public static $locale;
    protected static $homeRouteName;
    protected static $options;

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'addLocaleToRoutes'), 1000);
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'setOptions'), 1);
    }

    public function addLocaleToRoutes(ModuleEvent $e)
    {
        $config = $e->getConfigListener()->getMergedConfig(false);
        $this->setOptions($e);
        $routes = &$config['router']['routes'];
        foreach ($routes as $key => &$route) {
            if ($key == 'admin') continue;
            if (!isset($route['options'][self::LOCALE_AWARE])
                  || !isset($route['options'][self::LOCALE_AWARE])
                  || $route['options'][self::LOCALE_AWARE] == false) {
                continue;
            }
            
            //add redirect for home
            if ($route['options']['route'] == '/') {
                static::$homeRouteName = $key;
                $routes['__home'] = array(
                    'type' => 'Zend\Mvc\Router\Http\Literal',
                    'options' => array(
                        'route'    => '/',
                    ),
                );
            }

            if (strpos($route['options']['route'], ':locale') === false) {
                $route['options']['route'] = '/:locale' . $route['options']['route'];
            }
            if (strtolower($route['type']) == 'literal' || $route['type'] == 'Zend\Mvc\Router\Http\Literal') {
                $route['type'] = 'Segment';
            }
            if (!isset($route['options']['constraints'])) $route['options']['constraints'] = array();
            $route['options']['constraints'] = array_merge($route['options']['constraints'], array('locale' => '[a-zA-Z][a-zA-Z_-]*'));

            if (!isset($route['options']['defaults'])) $route['options']['defaults'] = array();
            $defaultLocale = $config['libra_locale']['default'];
            if (static::hasLocaleAlias($defaultLocale)) $defaultLocale = static::getLocaleAlias($defaultLocale);
            $route['options']['defaults'] = array_merge($route['options']['defaults'], array('locale' => $defaultLocale));
        }
        $e->getConfigListener()->setMergedConfig($config);
        static::$options = null; //don't use while not set at priority 1
        return null;
    }

    /**
     * Redirect from singe domain name to locale home page
     * @param \Zend\Mvc\MvcEvent $e
     * @return type
     */
    public function redirectFromEmptyPath(MvcEvent $e)
    {
        if ($e->getRouteMatch()->getMatchedRouteName() == '__home') {
            $router = $e->getRouter();
            $url = $router->assemble(array(), array('name' => static::$homeRouteName));
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(301);
            return $response;
        }
    }

    public function redirectFromNonExistentLocale(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $locale = $routeMatch->getParam(self::LOCALE_ROUTE_PARAM);
        if ($locale === null) return 0;  //do nothing

        //redirect if this locale has alias for having only one instance of URI.
        if (static::hasLocaleAlias($locale)) {
            $routeMatch->setParam(self::LOCALE_ROUTE_PARAM, static::getLocaleAlias($locale));
            $statusCode = static::getOption('redirect_code');
            goto redirect;
        }

        //replace alias by existent locale
        if (key_exists($locale, static::getLocales())) {
            $locales = static::getLocales();
            $locale = $locales[$locale];
            //set params for modules get param
            $routeMatch->setParam(self::LOCALE_ROUTE_PARAM, $locale);
        }

        $newLocale = Locale::lookup(static::getLocales(), $locale, false, static::getOption('default'));
        if ($newLocale !== $locale) {
            $routeMatch->setParam(self::LOCALE_ROUTE_PARAM, $newLocale);
redirect:   $router = $e->getRouter();
            $url = $router->assemble($routeMatch->getParams(), array('name' => $routeMatch->getMatchedRouteName()));
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(isset($statusCode) ? $statusCode : 302);
            return $response;
        }

        return 0; //return success
    }

    public function setDefaultLocale(MvcEvent $e)
    {
        $locale = $e->getRouteMatch()->getParam(self::LOCALE_ROUTE_PARAM);
        static::$locale = $locale;
        Locale::setDefault($locale);
    }

    /**
     * executes on boostrap
     * @param \Zend\Mvc\MvcEvent $e
     * @return null
     */
    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'redirectFromEmptyPath'));
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'redirectFromNonExistentLocale'));
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

    public static function getLocales()
    {
        return static::getOption('locales');
    }

    public static function hasLocaleAlias($locale)
    {
        $key = array_search($locale, static::getLocales());
        return is_string($key) ? true : false;
    }

    public static function getLocaleAlias($locale)
    {
        $key = array_search($locale, static::getLocales());
        return is_string($key) ? $key : $locale;
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
