<?php

return array(
	'controllers' => array(
        'invokables' => array(
            'Energy\Controller\Index' => 'Energy\Controller\IndexController'
        ),
    ),
	'router' => array(
        'routes' => array(
            'Energy' => array(
                'type'    => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/energy[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Energy\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Energy' => __DIR__ . '/../view',
        ),
    ),
);
