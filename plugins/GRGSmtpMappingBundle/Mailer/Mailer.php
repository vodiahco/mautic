<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 09/02/2018
 * Time: 13:37
 */

namespace MauticPlugin\GRGSmtpMappingBundle\Mailer;


use Swift_Transport;

class Mailer extends \Swift_Mailer
{

    public function __construct(Swift_Transport $transport)
    {
        parent::__construct($transport);
    }
}