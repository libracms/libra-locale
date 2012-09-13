<?php
return array(
    'router' => array(
        'routes' => array(
            'libra-locale' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/libra-locale',
                ),
                'child_routes' => array(
                    'switch' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/switch',
                            'defaults' => array(
                                //'module'     => 'libra-locale',
                                'controller' => 'libra-locale/locale',
                                'action'     => 'switch',
                            ),
                        ),
                        'may_terminate' => false,
                        'child_routes' => array(
                            'query' => array(
                                'type' => 'Query',
                                'options' => array(
                                    'defaults' => array(
                                        'to' => null,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'libra-locale/locale'           => 'LibraLocale\Controller\LocaleController',
            //for module listner __NAMESPASE__ param:
            'LibraLocale\Controller\Locale' => 'LibraLocale\Controller\LocaleController',
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    //defaults
    'libra_locale' => array(
        //default locale, not alias
        'default' => 'en-US',
        //language tags and their shotcuts
        'locales' => array(
            //alias => langtag
            // or only locale value
            'en' => 'en-US',
        ),
    ),
);