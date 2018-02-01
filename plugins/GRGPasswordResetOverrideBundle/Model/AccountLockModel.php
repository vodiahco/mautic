<?php

/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 23/01/2018
 * Time: 17:02.
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use Mautic\UserBundle\Entity\User;
use MauticPlugin\GRGPasswordResetOverrideBundle\Entity\AccountLock;

class AccountLockModel extends FormModel
{
    const ACCOUNT_LOCKOUT_TIME = 30 * 60; //30 minutes
    const ACCOUNT_LOCKOUT_MAX_ATTEMPTS = 5; //max login attempts
    /**
     * @var AccountLock $accountLockEntity
     */
    protected $accountLockEntity;

    /**
     *@var  \MauticPlugin\GRGPasswordResetOverrideBundle\Repository\AccountLockRepository $accountLockRepository
     */
    protected $accountLockRepository;

    /**
     * @var User $user
     */
    protected $user;


    /**
     * @param User $user
     */
    public function initWithUser($user)
    {
        if (! $user instanceof User) {
            return;
        }
        $this->accountLockRepository = $this->em->getRepository(AccountLock::class);
        $this->user = $user;
        $this->accountLockEntity = $this->accountLockRepository->getEntityByUserId($this->user->getId());
    }

    /**
     * @return bool
     */
    public function isAccountLocked()
    {
        $entity = $this->getAccountLockEntity();
        if (! $entity) {
            return false;
        }
        $now = time();
        return ($entity->getValidity() > $now);
    }


    public function incrementAttempts()
    {
        $entity = $this->getAccountLockEntityOrCreate();
        $entity->setUserId($this->user->getId());
        $entity->incrementAttempts();
        $entity->setValidity(0);
        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * reset login attempts to zero
     */
    public function resetAttempts()
    {
        $entity = $this->getAccountLockEntity();
        if ($entity) {
            $entity->resetAttempts();
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    /**
     * check if account should lock
     * @return bool
     */
    public function shouldAccountLock()
    {
        if ($this->accountLockEntity) {
            return $this->accountLockEntity->getAttempts() > self::ACCOUNT_LOCKOUT_MAX_ATTEMPTS;
        }
    }

    /**
     * lock account
     */
    public function lockAccount()
    {
        $validity = time() + self::ACCOUNT_LOCKOUT_TIME;
        $this->accountLockEntity->setValidity($validity);
        $this->em->persist($this->accountLockEntity);
        $this->em->flush();
    }

    /**
     * @return AccountLock|null
     */
    private function getAccountLockEntity()
    {
        if (! $this->accountLockEntity) {
            $this->accountLockEntity = $this->accountLockRepository->getEntityByUserId($this->user->getId());
        }
        return $this->accountLockEntity;
    }

    /**
     * @return AccountLock
     */
    private function getAccountLockEntityOrCreate()
    {
        return ($this->getAccountLockEntity() ? $this->accountLockEntity : new AccountLock());
    }
}
