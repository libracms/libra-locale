#Libra Locale Module

##Description
This module adds locale to yours modules. Also it set default locale site-widely.
It makes application locale aware, add locale short aliases like usually 
used _en_, _fr_, _ru_, instead of long locale tags like _en-US.UTF8_, _en-GB_ etc.

####Note!
You can easily add this module to your project as _LibraLocale_ keep path
for default alias and using any things in the same manner.

##Using
For enabling locale add it modules in application configuration.
To disable locale behavior for any module add to root level route of module
parameter disable_locale => true in routes options
~~~
    'router' => array(
        'routes' => array(
            'application' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/application',
                    'disable_locale' => true,
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
After this you can get locale alias from RouteMatch parameter. In Controller by code:
~~~
    $locale = $this->params('locale');
    //or
    $locale = $this->getEvent()->getRouteMatch()->getParam('locale');
~~~

It has a switcher, use by router with param 'to' for locale alias:
~~~
    echo $this->url('libra-locale/switch', array(), array('query' => array('to' => 'en')));
    echo $this->url('libra-locale/switch', array(), array('query' => array('to' => 'ru')));
    echo $this->url('libra-locale/switch', array(), array('query' => array('to' => 'fr')));
~~~

####Checking if locales was enabled
To disable locales you can:

  -  Comment out _locale_aware_ option in route. It should assume working for
     locale '*' or 'All' locales with enabled module.
  -  Disable _LibraLocale_ module.

Then to check if application support locales use:
~~~
    if (\LibraModuleManager\Module::isModulePresent('LibraLocale')) {
        //put code here
    }
~~~

__Note:__ I've just realized there may be added a flag in configuration instead
of disabling module. Will be implemented on demand.


Thanks for using it module.

##Installation
It available via composer package _libra/libra-locale_.
  -   Add to __composer.json__ required list: `"libra/libra-locale": "~0.3.0"`
  -   Enable module in __config/application.config.php__ module array by adding line: `'LibraLocale',`
  -   Copy `vendor/libra/libra-locale/config/libra-locale.global.php.dist` to `config/autoload/libra-locale.global.php`
  -   Enable it for wished module as described in use paragraph above.


##Changelog
#####0.5.0
After some experience I decided to make default locale path as '/' instead of '/locale/' hence of
rare changing of default locale. Pages can be duplicated if you have locale extlang subtag like
en-US and en-GB - will be almost identical. So search engines should recognize them as same text
in different locales (I hope it'll be in future).
There added parameters in configuration to setup redirect behavior.
Separator hyphen kept (not underscore) due to (http://www.w3.org/International/articles/language-tags/)
and (http://en.wikipedia.org/wiki/IETF_language_tag) 
and (http://docs.oracle.com/javase/7/docs/api/java/util/Locale.html) new standards.
The default locale at '/' will do smoother adding locale in projects without locale.

BC changes:
  - By enabling this module locales will be added to all http routes. 
    To disable it add __'disable_locale' => true,__ parameter to custom route.

Using:
  - Getting current locale by \Locale::getDefault();
  - Getting current locale alias by $this->params('locale'); in controller action.
    or by \LibraLocale\Module::$alias;
  - Getting list of available and allowed aliases and locales by: \LibraLocale\Module::getLocales()

Added:  
    Flag _redirect_from_locale_tag_ that allow redirect from locale tag to alias.
    Now it will accept as alias that almost unknown. By default = false,
    but for BC it will be true in libra-cms globals until ver. 0.6.0.

To get proper links by navigation helper use type in page __LibraLocale\\Navigation\\Page\\Uri__
and __LibraLocale\\Navigation\\Page\\Mvc__. Or use type of page === null and
for service container - __LibraLocale\Navigation\Service\DefaultNavigationFactory__.
It can be done by adding in _application.config.php_ file
in 'service_manager' array row:
~~~~
            'Navigation' => 'LibraLocale\Navigation\Service\DefaultNavigationFactory',
~~~~
