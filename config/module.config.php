<?php
return array(
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