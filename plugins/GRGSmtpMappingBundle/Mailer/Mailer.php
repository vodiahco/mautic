<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 09/02/2018
 * Time: 13:37
 */

namespace MauticPlugin\GRGSmtpMappingBundle\Mailer;


use Swift_Mime_Message;
use Swift_RfcComplianceException;
use Swift_SmtpTransport;
use Swift_Transport;
use Monolog\Logger;

class Mailer extends \Swift_Mailer
{

    protected $config;
    protected $configKeys;
    protected $currentHost = "";
    private $logger;

    /**
     * @param Swift_Transport $transport
     * @param $config
     */
    public function __construct(Swift_Transport $transport, $config, Logger $logger)
    {
        $this->logger = $logger;
        $this->config = $config;
        parent::__construct($transport);
        $this->initConfig();
    }

    /**
     * @param Swift_Mime_Message $message
     * @param null $failedRecipients
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;
        $transport = $this->getValidTransport($message);
        $this->logger->log("debug", "Transport host is is: -" .print_r($transport->getHost(), 1));
        if (!$transport->isStarted()) {
            $transport->start();
        }
        $sent = 0;

        try {
            $sent = $transport->send($message, $failedRecipients);
        } catch (Swift_RfcComplianceException $e) {
            foreach ($message->getTo() as $address => $name) {
                $failedRecipients[] = $address;
            }
        }

        return $sent;
    }

    /**
     * @param Swift_Mime_Message $message
     * @return Swift_Transport|void
     */
    private function getValidTransport(Swift_Mime_Message $message)
    {
        $transport = $this->getTransport();
        $emails = $message->getFrom();

        if (is_array($emails)) {
            $this->logger->log("debug", "email is array");
            $emails = array_keys($emails);
            $this->logger->log("debug", "email is: -" .print_r($emails, 1));
            if (count($emails) > 0) {
                $fromEmail = $emails[0];
                if ($this->shouldResetTransport($fromEmail)) {
                    $transportFor = $this->getTransportForMessage($fromEmail);
                    $transport = ($transportFor) ? $transportFor : $transport;
                }
            }
        }
        return $transport;
    }

    /**
     * @param $fromEmail
     * @return bool
     */
    private function shouldResetTransport($fromEmail)
    {
        if ($fromEmail) {
            if ($fromEmail == $this->currentHost) {
                //exit early
                return false;
            }
            if (in_array($fromEmail, $this->configKeys)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $fromEmail
     * @return string
     */
    private function getEmailHost($fromEmail)
    {
        if ($fromEmail) {
            $split = explode("@", $fromEmail);
            return (count($split) > 1) ? $split[1] : "";
        }
    }

    /**
     * @param $fromEmail
     * @return Swift_SmtpTransport
     */
    private function getTransportForMessage($fromEmail)
    {
        $hostConfig = $this->config[$fromEmail];
        $smptHost = $hostConfig['host'];
        $username = $hostConfig['username'];
        $password = $hostConfig['password'];
        $encryption = (isset($hostConfig['ssl'])) ? $hostConfig['ssl'] : null;
        $transport = (new Swift_SmtpTransport($smptHost, 25, $encryption))
            ->setUsername($username)
            ->setPassword($password)
        ;
        $this->currentHost = $fromEmail;
        return $transport;
    }

    /**
     * initialize the config
     */
    private function initConfig()
    {
        if ($this->config) {
            $this->configKeys = array_keys((array) $this->config);
        } else {
            $this->configKeys = [];
        }
        $this->logger->log("debug", print_r($this->configKeys, 1));
    }
}