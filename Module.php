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
        $routes = &$config['router']['routes'];
        foreach ($routes as $key => &$route) {
            if ($key == 'admin') continue;
            if (!isset($route['options']['multilocale']) 
                  || !isset($route['options']['multilocale'])
                  || $route['options']['multilocale'] == false) {
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
            $route['options']['defaults'] = array_merge($route['options']['defaults'], array('locale' => 'en'));
        }
        $e->getConfigListener()->setMergedConfig($config);
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
        $locale = $routeMatch->getParam('locale');
        if ($locale === null) return 0;  //do nothing

        $config = $e->getApplication()->getServiceManager()->get('config');
        $config = $config['libra_locale'];
        //replace alias by existent locale
        if (key_exists($locale, $config['locales'])) {
            $locale = $config['locales'][$locale];
            //set params for modules get param
            $routeMatch->setParam('locale', $locale);
        }

        $newLocale = Locale::lookup($config['locales'], $locale, false, $config['default']);
        if ($newLocale !== $locale) {
            $routeMatch->setParam('locale', $newLocale);
            $router = $e->getRouter();
            $url = $router->assemble($routeMatch->getParams(), array('name' => $routeMatch->getMatchedRouteName()));
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(303);
            return $response;
        }

        return 0; //return success
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
    }

    public function setOptions($e)
    {
        $config = $e->getConfigListener()->getMergedConfig();
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
