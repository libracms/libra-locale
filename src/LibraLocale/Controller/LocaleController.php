<?php

namespace LibraLocale\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Router\Http\Segment;
use Zend\Http\Request;
use LibraLocale\Module;

class LocaleController extends AbstractActionController
{
    public function switchAction()
    {
        $newLocale = $this->getRequest()->getQuery('to', Module::getOption('default'));
        $uri = $_SERVER['HTTP_REFERER'];
        $segment_options = array(
            'route'    => '/[:locale_sef/[:others]]',
            'constraints' => array(
                'locale' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'others'     => '.+',
            ),
            'defaults' => array(
                'locale' => '', //for main page (/)
                'others' => '',
            ),
        );
        $router = Segment::factory($segment_options);
        $request = new Request();
        $request->setUri($uri);

        $routeMatch = $router->match($request);
        $params = $request->getUri()->getQuery();
        $others = $routeMatch->getParam('others');
        return $this->redirect()->toUrl(rtrim("/{$newLocale}/{$others}?$params", '?')); //@todo need fix for invalid uri: /ru/aaa+fd
    }

}
