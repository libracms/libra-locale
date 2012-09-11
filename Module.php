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

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'addLocaleToRoutes'), 100);
    }

    public function addLocaleToRoutes(ModuleEvent $e)
    {
        $config = $e->getConfigListener()->getMergedConfig(false);
        $routes = &$config['router']['routes'];
        foreach ($routes as $key => &$route) {
            if ($key == 'admin') continue;
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
        $routes['__home'] = array(
            'type' => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route'    => '/',
            ),
        );
        //$a = \Locale::
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
            $url = $router->assemble(array(), array('name' => 'home'));
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(301);
            return $response;
        }
    }

    public function redirectFromNonExistentLocale(MvcEvent $e)
    {
        $config = $e->getApplication()->getServiceManager()->get('config');
        $config = $config['libra_locale'];
        $routeMatch = $e->getRouteMatch();
        $locale = $routeMatch->getParam('locale');
        //if (($key = array_search($locale, $config['langtags'])) != false) {
        if (key_exists($locale, $config['langtags'])) {
            $locale = $config['langtags'][$locale];
        }
        $newLocale = Locale::lookup($config['langtags'], $locale, false, $config['default']);
        if ($locale !== $newLocale) {
            $routeMatch->setParam('locale', $newLocale);
            $router = $e->getRouter();
            $url = $router->assemble(array(), $routeMatch->getParams());
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(303);
            return $response;
        }
    }

    /**
     * executes on boostrap
     * @param \Zend\Mvc\MvcEvent $e
     * @return null
     */
    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'redirectFromEmptyPath'));
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
