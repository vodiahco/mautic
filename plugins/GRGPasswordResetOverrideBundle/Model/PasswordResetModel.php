<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 10/01/2018
 * Time: 17:02.
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\UserBundle\Entity\User;
use MauticPlugin\GRGPasswordResetOverrideBundle\Entity\PasswordReset;

class PasswordResetModel extends FormModel
{
    const USED_TOKEN = 0;
    protected $tokenObject;
    protected $secret;
    protected $validity;
    protected $minLength;
    protected $requireCaps;
    protected $requireNumber;
    protected $requireSpecial;
    protected $mailHelper;
    protected $passwordEntity;

    /**
     * PasswordResetModel constructor.
     *
     * @param $tokenObject
     */
    public function __construct($tokenObject, MailHelper $mailHelper)
    {
        $this->mailHelper  = $mailHelper;
        $this->tokenObject = $tokenObject;
        $this->initToken();
    }

    /**
     * @return mixed
     */
    public function getTokenObject()
    {
        return (is_array($this->tokenObject)) ? (object) $this->tokenObject : $this->tokenObject;
    }

    private function initToken()
    {
        $tokenObject = $this->getTokenObject();
        if (is_object($tokenObject)) {
            $this->secret   = $tokenObject->secret;
            $this->validity = $tokenObject->validity;
            $passwordConfig = $this->getObjectCopy($tokenObject->grg_password_config);
            if ($passwordConfig) {
                $this->minLength      = $passwordConfig->min_length;
                $this->requireCaps    = $passwordConfig->require_cap;
                $this->requireNumber  = $passwordConfig->require_number;
                $this->requireSpecial = $passwordConfig->require_special;
            }
        }
    }

    /**
     * @param $passwordConfig
     *
     * @return object
     */
    private function getObjectCopy($passwordConfig)
    {
        if (is_array($passwordConfig) || is_object($passwordConfig)) {
            $passwordConfig = (object) $passwordConfig;
        }

        return $passwordConfig;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return mixed
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * @return mixed
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * @return mixed
     */
    public function getRequireCaps()
    {
        return $this->requireCaps;
    }

    /**
     * @return mixed
     */
    public function getRequireNumber()
    {
        return $this->requireNumber;
    }

    /**
     * @return mixed
     */
    public function getRequireSpecial()
    {
        return $this->requireSpecial;
    }

    /**
     * @return int
     */
    public function getTokenExpiry()
    {
        return time() + $this->validity;
    }

    /**
     * @param User $user
     *
     * @return string
     */
    private function generateResetToken(User $user)
    {
        $now     = time();
        $secret  = str_shuffle($this->secret);
        $payload = str_shuffle($secret.$now);

        return hash('sha256', $payload);
    }

    /**
     * @param User $user
     *
     * @return PasswordReset
     */
    public function prepareToken(User $user)
    {
        $passwordEntity = new PasswordReset();
        $passwordEntity
            ->setToken($this->generateResetToken($user))
            ->setValidity($this->getTokenExpiry())
            ->setUserId($user->getId());

        return $passwordEntity;
    }

    /**
     * @param PasswordReset $passwordEntity
     *
     * @return PasswordReset
     */
    public function validateToken(PasswordReset $passwordEntity)
    {
        $em = $this->em;
        $em->persist($passwordEntity);
        $em->flush();

        return $passwordEntity;
    }

    /**
     * @param bool $flush
     *
     * @return mixed
     */
    public function invalidateToken($flush = false)
    {
        //we may as well just delete this entity
        $this->passwordEntity->setValidity(self::USED_TOKEN);
        if ($flush) {
            $em = $this->em;
            $em->flush();

            return $this->passwordEntity;
        }
    }

    /**
     * @param $token
     * @param User $user
     *
     * @return bool
     */
    public function isValidToken($token, User $user)
    {
        /**
         * @var \MauticPlugin\GRGPasswordResetOverrideBundle\Repository\PasswordResetRepository
         * @var \MauticPlugin\GRGPasswordResetOverrideBundle\Entity\PasswordReset               $passwordEntity
         */
        $repository     = $this->em->getRepository(PasswordReset::class);
        $passwordEntity = $repository->getEntityByToken($token);
        if ($passwordEntity) {
            $expiry     = $passwordEntity->getValidity();
            $resetToken = $passwordEntity->getToken();
            if (!$expiry || time() > $expiry) {
                return false;
            } elseif (!$resetToken || $resetToken != $token) {
                return false;
            } elseif (!$passwordEntity || $passwordEntity->getUserId() != $user->getId()) {
                return false;
            } else {
                $this->passwordEntity = $passwordEntity;

                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     * @param $resetToken
     */
    public function sendResetEmail(User $user, $resetToken)
    {
        $mailer     = $this->mailHelper->getMailer();
        $resetLink  = $this->router->generate('mautic_user_passwordresetconfirm', ['token' => $resetToken], true);
        $this->logger->debug($resetLink);
        $mailer->setTo([$user->getEmail() => $user->getName()]);
        $mailer->setSubject($this->translator->trans('mautic.user.user.passwordreset.subject'));
        $text = $this->translator->trans(
            'mautic.user.user.passwordreset.email.body',
            ['%name%' => $user->getFirstName(), '%resetlink%' => '<a href="'.$resetLink.'">'.$resetLink.'</a>']
        );
        $text = str_replace('\\n', "\n", $text);
        $html = nl2br($text);

        $mailer->setBody($html);
        $mailer->setPlainText(strip_tags($text));

        $mailer->send();
    }
}
