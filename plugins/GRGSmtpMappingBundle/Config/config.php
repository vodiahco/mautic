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
                    '%mautic.smtp_access_credential%',
                    '@monolog.logger.mautic'
                    ]
            ],
        ]
    ],
];