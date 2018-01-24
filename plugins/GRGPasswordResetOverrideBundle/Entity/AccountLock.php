<?php

/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 10/01/2018
 * Time: 13:25.
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

/**
 * Class PasswordReset.
 */
class AccountLock
{
    protected $id;

    protected $userId;

    protected $validity;

    private $createdAt;

    private $updatedAt;

    protected $attempts;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('grg_account_lock')
            ->setCustomRepositoryClass('MauticPlugin\GRGPasswordResetOverrideBundle\Repository\AccountLockRepository');
        $builder->createField('id', 'integer')->makePrimaryKey()->generatedValue()->build();
        $builder->addField('validity', 'integer');
        $builder->addField('attempts', 'integer');
        $builder->createField('updatedAt', 'datetime')
            ->columnName('updated_at')
            ->build();
        $builder->createField('createdAt', 'datetime')
            ->columnName('created_at')
            ->build();
        $builder->createField('userId', 'integer')
            ->columnName('user_id')
            ->build();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     *
     * @return PasswordReset
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * @param mixed $validity
     *
     * @return PasswordReset
     */
    public function setValidity($validity)
    {
        $this->validity = $validity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     *
     * @return PasswordReset
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     *
     * @return PasswordReset
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @param mixed $attempts
     * @return AccountLock
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
        return $this;
    }

    public function incrementAttempts()
    {
        $this->attempts += 1;
    }

    public function resetAttempts()
    {
        $this->attempts = 0;
        $this->validity = 0;
    }
}
