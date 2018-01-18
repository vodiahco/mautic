<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 18/01/2018
 * Time: 16:13
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Controller\Traits;


use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

trait ValidationMessageTrait
{
    /**
     * @param FormInterface $form
     * @param $translationId
     */
    protected function setFormErrorFromTranslation(FormInterface $form, $translationId)
    {
        $message = $this->translator->trans($translationId);
        $form->addError(new FormError($message));
    }

    /**
     * @param FormInterface $form
     */
    protected function setPasswordFormatErrorMessage(FormInterface $form)
    {
        $this->setFormErrorFromTranslation($form, "mautic.user.user.form.help.passwordrequirements");
    }
}