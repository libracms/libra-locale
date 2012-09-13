<?php

namespace LibraLocale\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Request;
use Zend\Mvc\Router\RouteMatch;
use LibraLocale\Module;

class LocaleController extends AbstractActionController
{
    public function switchAction()
    {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            $basePath = $this->getServiceLocator()->get('ViewRenderer')->basePath();
            return $this->redirect()->toUrl($basePath . '/');
        }

        $uri = $_SERVER['HTTP_REFERER'];
        $newLocale = $this->getRequest()->getQuery('to', Module::getOption('default'));
        $router = $this->getEvent()->getRouter();
        $request = new Request();
        $request->setUri($uri);
        $routeMatch = $router->match($request);

        if (!$routeMatch instanceof RouteMatch) {
            return $this->redirect()->toUrl($uri);
        }
        
        $params = $routeMatch->getParams();
        if (isset($params['locale'])) $params['locale'] = $newLocale; //don't override locale not care module param
        return $this->redirect()->toRoute($routeMatch->getMatchedRouteName(), $params);
    }

}
