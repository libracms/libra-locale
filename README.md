Libra CMS
================================

Libra Locale Module
--------------------------------

###Description
This module adds locale to yours modules. Aslo it set default locale site-widely.

###Using
For enabling locale for your module add to root level router of module parameter locale_aware => true in options
~~~
    'router' => array(
        'routes' => array(
            'application' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/application',
                    'locale_aware' => true,
                    'constraints' => array(
                        'alias'      => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        ....
                    ),
                    'may_terminate' => true,
                    'child_routes' => array(
                        ....
                    )
                )
            ),
        ....
~~~
Also I recomend to add to home router (where 'route' => '/')
~~~
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'locale_aware' => true,
                ),
            ),

~~~
After this you can get locale from RouteMatch parameter. In Controller by code:
~~~
    $locale = $this->params('locale');
    //or
    $locale = $this->getEvent()->getRouteMatch()->getParam('locale');
~~~

This has a switcher, use by router with param 'to':
~~~
    echo this->url('libra-locale/switch/query', array('to' => 'en'));
    echo this->url('libra-locale/switch/query', array('to' => 'ru'));
~~~

Thanks for using my module.