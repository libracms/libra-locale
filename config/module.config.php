<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    //defaults
    'libra_locale' => array(
        //default language tag
        'default' => 'en',
        //language tags and their shotcuts
        'langtags' => array(
            //alias => langtag
            // or only langtag value
            'en' => 'en-US',
        ),
    ),
);