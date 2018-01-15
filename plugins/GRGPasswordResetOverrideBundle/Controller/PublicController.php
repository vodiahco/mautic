<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 10/01/2018
 * Time: 13:25.
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Symfony\Component\Form\FormError;

class PublicController extends FormController
{
    public function passwordResetAction()
    {
        /**
         * @var \Mautic\UserBundle\Model\UserModel
         */
        $model = $this->getModel('user');
        /**
         * @var \MauticPlugin\GRGPasswordResetOverrideBundle\Model\PasswordResetModel
         */
        $resetModel = $this->getModel('grg_password_reset_override.password_reset');
        $data       = ['identifier' => ''];
        $action     = $this->generateUrl('mautic_user_passwordreset');
        $form       = $this->get('form.factory')->create('passwordreset', $data, ['action' => $action]);

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            if ($isValid = $this->isFormValid($form)) {
                //find the user
                $data = $form->getData();
                $user = $model->getRepository()->findByIdentifier($data['identifier']);

                if ($user == null) {
                    //silently log error
                    //notify user of success irrespective of outcome to mitigate against giving away user status information
                    return $this->notifyOnPasswordReset();
                } else {
                    $passwordEntity = $resetModel->prepareToken($user);
                    $resetModel->validateToken($passwordEntity);
                    if ($passwordEntity->getToken() && $passwordEntity->getId()) {
                        $resetModel->sendResetEmail($user, $passwordEntity->getToken());

                        return $this->notifyOnPasswordReset();
                    } else {
                        return $this->notifyOnPasswordReset();
                    }
                }
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
            ],
            'contentTemplate' => 'GRGPasswordResetOverrideBundle:Security:reset.html.php',
            'passthroughVars' => [
                'route' => $action,
            ],
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function passwordResetConfirmAction()
    {
        /**
         * @var \Mautic\UserBundle\Model\UserModel
         */
        $model  = $this->getModel('user');
        $data   = ['identifier' => '', 'password' => '', 'password_confirm' => ''];
        $action = $this->generateUrl('mautic_user_passwordresetconfirm');
        $form   = $this->get('form.factory')->create('passwordresetconfirm', [], ['action' => $action]);
        $token  = $this->request->query->get('token');

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            if ($isValid = $this->isFormValid($form)) {
                //find the user
                $data = $form->getData();
                /**
                 * @var \Mautic\UserBundle\Entity\User
                 */
                $user = $model->getRepository()->findByIdentifier($data['identifier']);

                if ($user == null) {
                    //should show custom message
                    $form['identifier']->addError(new FormError($this->translator->trans('mautic.user.user.passwordreset.nouserfound', [], 'validators')));
                } else {
                    if ($this->request->getSession()->has('resetToken')) {
                        $resetToken = $this->request->getSession()->get('resetToken');
                        $encoder    = $this->get('security.encoder_factory')->getEncoder($user);
                        /**
                         * @var \MauticPlugin\GRGPasswordResetOverrideBundle\Model\PasswordResetModel
                         */
                        $resetModel = $this->getModel('grg_password_reset_override.password_reset');
                        if ($resetModel->isValidToken($resetToken, $user)) {
                            $encodedPassword = $model->checkNewPassword($user, $encoder, $data['plainPassword']);
                            $user->setPassword($encodedPassword);
                            $resetModel->invalidateToken();
                            $model->saveEntity($user);

                            $this->addFlash('mautic.user.user.notice.passwordreset.success', [], 'notice', null, false);

                            $this->request->getSession()->remove('resetToken');

                            return $this->redirect($this->generateUrl('login'));
                        }

                        return $this->delegateView([
                            'viewParameters' => [
                                'form' => $form->createView(),
                            ],
                            'contentTemplate' => 'MauticUserBundle:Security:resetconfirm.html.php',
                            'passthroughVars' => [
                                'route' => $action,
                            ],
                        ]);
                    } else {
                        $this->addFlash('mautic.user.user.notice.passwordreset.missingtoken', [], 'notice', null, false);

                        return $this->redirect($this->generateUrl('mautic_user_passwordresetconfirm'));
                    }
                }
            }
        }
        $this->request->getSession()->set('resetToken', $token);

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
            ],
            'contentTemplate' => 'MauticUserBundle:Security:resetconfirm.html.php',
            'passthroughVars' => [
                'route' => $action,
            ],
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function notifyOnPasswordReset()
    {
        $this->addFlash('mautic.user.user.notice.passwordreset', [], 'notice', null, false);

        return $this->redirect($this->generateUrl('login'));
    }
}
