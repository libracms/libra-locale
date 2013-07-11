#Libra Locale Module

##Description
This module adds locale to yours modules. Also it set default locale site-widely.
It makes application locale aware, add locale short aliases like usually used _en_, _fr_, _ru_, eanstead full _en_US_, _en_GB_.

####Note!
You can easily add this module to your project as LibraLocale change routes of individual modules only
where you enabled it. Any other modules remain untouched.

##Using
For enabling locale for your module add to root level router of module parameter locale_aware => true in routes options
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
I recommend to add it to _home_ router (where 'route' => '/')
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

It has a switcher, use by router with param 'to':
~~~
    echo $this->url('libra-locale/switch', array(), array('query' => array('to' => 'en')));
~~~
or
~~~
    echo $this->url('libra-locale/switch', array(), array('query' => array('to' => 'ru')));
~~~
or
~~~
    echo $this->url('libra-locale/switch', array(), array('query' => array('to' => 'fr')));
~~~
Thanks for using it module.

##Installation
It available via composer package _libra/libra-locale_. 
-   Add to __composer.json__ required list: `"libra/libra-locale": "~0.3.0"`
-   Enable module in __config/application.config.php__ module array by adding line: `'LibraLocale',`
-   Copy `vendor/libra/libra-locale/config/libra-locale.global.php.dist` to `config/autoload/libra-locale.global.php`
-   Enable it for wished module as described in use paragraph above.
