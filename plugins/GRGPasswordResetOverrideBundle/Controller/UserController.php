<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\UserBundle\Form\Type as FormType;
use MauticPlugin\GRGPasswordResetOverrideBundle\Controller\Traits\ValidationMessageTrait;

/**
 * Class UserController.
 */
class UserController extends FormController
{
    use ValidationMessageTrait;

    /**
     * this action overrides the route controller for: mautic_user_action (MauticUserBundle:User:execute)
     * Generate's form and processes new post data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        if (!$this->get('mautic.security')->isGranted('user:users:create')) {
            return $this->accessDenied();
        }

        /** @var \Mautic\UserBundle\Model\UserModel $model */
        $model = $this->getModel('user.user');
        $resetModel = $this->getModel('grg_password_reset_override.password_reset');

        //retrieve the user entity
        $user = $model->getEntity();

        //set the return URL for post actions
        $returnUrl = $this->generateUrl('mautic_user_index');

        //set the page we came from
        $page = $this->get('session')->get('mautic.user.page', 1);

        //get the user form factory
        $action = $this->generateUrl('mautic_user_action', ['objectAction' => 'new']);
        $form   = $model->createForm($user, $this->get('form.factory'), $action);
        
        //check password
        $passwordValid = true;

        //Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            $passwordValid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                //check to see if the password needs to be rehashed
                $submittedPassword = $this->request->request->get('user[plainPassword][password]', null, true);
                $encoder           = $this->get('security.encoder_factory')->getEncoder($user);
                if ($resetModel->isValidPasswordFormat($submittedPassword)) {
                    $password = $resetModel->hashPassword($user, $encoder, $submittedPassword);
                    $passwordValid = true;
                }
                if ($valid = $this->isFormValid($form) && $passwordValid) {
                    //form is valid so process the data
                    $user->setPassword($password);
                    $model->saveEntity($user);

                    //check if the user's locale has been downloaded already, fetch it if not
                    $installedLanguages = $this->coreParametersHelper->getParameter('supported_languages');

                    if ($user->getLocale() && !array_key_exists($user->getLocale(), $installedLanguages)) {
                        /** @var \Mautic\CoreBundle\Helper\LanguageHelper $languageHelper */
                        $languageHelper = $this->factory->getHelper('language');

                        $fetchLanguage = $languageHelper->extractLanguagePackage($user->getLocale());

                        // If there is an error, we need to reset the user's locale to the default
                        if ($fetchLanguage['error']) {
                            $user->setLocale(null);
                            $model->saveEntity($user);
                            $message     = 'mautic.core.could.not.set.language';
                            $messageVars = [];

                            if (isset($fetchLanguage['message'])) {
                                $message = $fetchLanguage['message'];
                            }

                            if (isset($fetchLanguage['vars'])) {
                                $messageVars = $fetchLanguage['vars'];
                            }

                            $this->addFlash($message, $messageVars);
                        }
                    }

                    $this->addFlash('mautic.core.notice.created', [
                        '%name%'      => $user->getName(),
                        '%menu_link%' => 'mautic_user_index',
                        '%url%'       => $this->generateUrl('mautic_user_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $user->getId(),
                        ]),
                    ]);
                }
            }

            if ($cancelled || ($passwordValid && $valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticUserBundle:User:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_user_index',
                        'mauticContent' => 'user',
                    ],
                ]);
            } elseif ($passwordValid && $valid && !$cancelled) {
                return $this->editAction($user->getId(), true);
            }
        }
        
        if (! $passwordValid) {
            $this->setPasswordFormatErrorMessage($form->get('plainPassword')->get("password"));
        }

        return $this->delegateView([
            'viewParameters'  => ['form' => $form->createView()],
            'contentTemplate' => 'MauticUserBundle:User:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_user_new',
                'route'         => $action,
                'mauticContent' => 'user',
            ],
        ]);
    }

    /**
     * Generates edit form and processes post data.
     *this action overrides the route controller for: mautic_user_action (MauticUserBundle:User:execute)
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        if (!$this->get('mautic.security')->isGranted('user:users:edit')) {
            return $this->accessDenied();
        }
        $model = $this->getModel('user.user');
        $user  = $model->getEntity($objectId);
        $resetModel = $this->getModel('grg_password_reset_override.password_reset');

        //set the page we came from
        $page = $this->get('session')->get('mautic.user.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('mautic_user_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticUserBundle:User:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_user_index',
                'mauticContent' => 'user',
            ],
        ];

        //user not found
        if ($user === null) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.user.user.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        } elseif ($model->isLocked($user)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $user, 'user.user');
        }

        $action = $this->generateUrl('mautic_user_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $model->createForm($user, $this->get('form.factory'), $action);

        //check password
        $passwordValid = true;

        ///Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                //check to see if the password needs to be rehashed
                $submittedPassword = $this->request->request->get('user[plainPassword][password]', null, true);
                if ($resetModel->shouldCheckPassword($submittedPassword)) {
                    $passwordValid = false;
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                    if ($resetModel->isValidPasswordFormat($submittedPassword)) {
                        $password = $resetModel->hashPassword($user, $encoder, $submittedPassword);
                        $user->setPassword($password);
                        $passwordValid = true;
                    }
                }

                if ($valid = $this->isFormValid($form) && $passwordValid) {
                    //form is valid so process the data
                    $model->saveEntity($user, $form->get('buttons')->get('save')->isClicked());

                    //check if the user's locale has been downloaded already, fetch it if not
                    $installedLanguages = $this->coreParametersHelper->getParameter('supported_languages');

                    if ($user->getLocale() && !array_key_exists($user->getLocale(), $installedLanguages)) {
                        /** @var \Mautic\CoreBundle\Helper\LanguageHelper $languageHelper */
                        $languageHelper = $this->factory->getHelper('language');

                        $fetchLanguage = $languageHelper->extractLanguagePackage($user->getLocale());

                        // If there is an error, we need to reset the user's locale to the default
                        if ($fetchLanguage['error']) {
                            $user->setLocale(null);
                            $model->saveEntity($user);
                            $message     = 'mautic.core.could.not.set.language';
                            $messageVars = [];

                            if (isset($fetchLanguage['message'])) {
                                $message = $fetchLanguage['message'];
                            }

                            if (isset($fetchLanguage['vars'])) {
                                $messageVars = $fetchLanguage['vars'];
                            }

                            $this->addFlash($message, $messageVars);
                        }
                    }

                    $this->addFlash('mautic.core.notice.updated', [
                        '%name%'      => $user->getName(),
                        '%menu_link%' => 'mautic_user_index',
                        '%url%'       => $this->generateUrl('mautic_user_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $user->getId(),
                        ]),
                    ]);
                }
            } else {
                //unlock the entity
                $model->unlockEntity($user);
            }

            if ($cancelled || ($passwordValid && $valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect($postActionVars);
            }
        } else {
            //lock the entity
            $model->lockEntity($user);
        }

        //set error
        if (! $passwordValid) {
            $this->setPasswordFormatErrorMessage($form->get('plainPassword')->get("password"));
        }

        return $this->delegateView([
            'viewParameters'  => ['form' => $form->createView()],
            'contentTemplate' => 'GRGPasswordResetOverrideBundle:User:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_user_index',
                'route'         => $action,
                'mauticContent' => 'user',
            ],
        ]);
    }
}
