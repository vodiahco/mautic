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
            'grg.com' => [
                "host" => "smtp.mailtrap.io",
                'username' => '12a268598f711f',
                'password' => '13bc9ae57abd15'
            ],
            'delegate.com' => [
                'username' => 'hotels@grg.com',
                'password' => '@password'
            ],
        ]
    ],
    'services' => [
        'other' => [
            'mautic.grg.helper.mailer' => [
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