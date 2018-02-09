<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 08/02/2018
 * Time: 15:14
 */

return [
    'name'        => 'GRG Smtp Mappings',
    'description' => 'SMPT setting based on email sender',
    'version'     => '1.0',
    'author'      => 'Victor O.',
    'parameters' => [
        'grg_smtp_transports' => [
            'hotels.com' => [
                "host" => "gmail",
                'username' => 'hotels@grg.com',
                'password' => '@password'
            ],
            'delegate.com' => [
                'username' => 'hotels@grg.com',
                'password' => '@password'
            ],
        ]
    ],
    'services' => [
        'other' => [
            'mautic.helper.mailer' => [
                'class'     => \MauticPlugin\GRGSmtpMappingBundle\Helper\MailHelper::class,
                'arguments' => [
                    'mautic.factory',
                    'mailer',
                ],
            ],
            'mailer' => [
                'class'     => \MauticPlugin\GRGSmtpMappingBundle\Mailer\Mailer::class,
                'arguments' => [
                    'swiftmailer.transport.real',
                    '%mautic.grg_smtp_transports%'
                    ]
            ],
        ]
    ],
];