<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 14/02/2018
 * Time: 10:04.
 */

return [
    'name'        => 'GRG CSRF Fix',
    'description' => 'Validates all ajax POST, PUT, PATCH, DELETE request for CSRF',
    'version'     => '1.0',
    'author'      => 'Victor O.',
    'services' => [
        'other' => [
            // kernel listener
            'grg.ajax.csrf.kernel_subscriber' => [
                'class'     => 'MauticPlugin\GRGCSRFBundle\Listeners\Http\AjaxCSRFSubscriber',
                'arguments' => [
                    'security.csrf.token_manager',
                    "%kernel.environment%"
                ],
                'tag'          => 'kernel.event_subscriber',
            ],
        ]
    ],

];
