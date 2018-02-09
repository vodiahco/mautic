<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 10/01/2018
 * Time: 13:34.
 */

return [
    'name'        => 'GRG Password Reset Override',
    'description' => 'Override for the password reset functionality',
    'version'     => '1.3',
    'author'      => 'Victor',
    'parameters'  => [
        'grg_token'       => [
            'secret'              => 'lkjfjkjviuffygfgiru@g0r8yerikkgopugh0t9uytrojpudgj@pohuip9trthkjohjt8hut8',
            'validity'            => 30 * 60,
            'grg_password_config' => [
                'min_length'      => 8,
                'require_cap'     => true,
                'require_special' => true,
                'require_number'  => true,
            ],
        ],
    ],
    'services' => [
        'models' => [
            'mautic.grg_password_reset_override.model.password_reset' => [
                'class'     => 'MauticPlugin\GRGPasswordResetOverrideBundle\Model\PasswordResetModel',
                'arguments' => [
                    '%mautic.grg_token%',
                    'mautic.helper.mailer',
                ],
            ],
            'mautic.grg_password_reset_override.model.account_lock' => [
                'class'     => 'MauticPlugin\GRGPasswordResetOverrideBundle\Model\AccountLockModel',
            ],
        ],
        'other' => [
            // Authentication
            'mautic.security.authentication_handler' => [
                'class'     => 'MauticPlugin\GRGPasswordResetOverrideBundle\Security\Authentication\AuthenticationHandler',
                'arguments' => [
                    'router',
                    'session',
                    'mautic.grg_password_reset_override.model.account_lock'
                ],
            ],
            'mautic.user.form_authenticator' => [
                'class'     => 'MauticPlugin\GRGPasswordResetOverrideBundle\Security\Authenticator\FormAuthenticator',
                'arguments' => [
                    'mautic.helper.integration',
                    'security.password_encoder',
                    'event_dispatcher',
                    'request_stack',
                    'mautic.grg_password_reset_override.model.account_lock'
                ],
            ],
        ],
    ],
    'routes' => [
        'public' => [
            'mautic_user_passwordreset' => [
                'path'       => '/passwordreset',
                'controller' => 'GRGPasswordResetOverrideBundle:Public:passwordReset',
            ],
            'mautic_user_passwordresetconfirm' => [
                'path'       => '/passwordresetconfirm',
                'controller' => 'GRGPasswordResetOverrideBundle:Public:passwordResetConfirm',
            ],
        ],
        'main' => [
            'mautic_user_account' => [
                'path'       => '/account',
                'controller' => 'GRGPasswordResetOverrideBundle:Profile:index',
            ],
            'mautic_user_action' => [
                'path'       => '/users/{objectAction}/{objectId}',
                'controller' => 'GRGPasswordResetOverrideBundle:User:execute',
            ],
        ],
    ],

];