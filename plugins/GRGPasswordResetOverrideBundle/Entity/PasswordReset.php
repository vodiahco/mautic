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
class PasswordReset
{
    protected $id;

    protected $userId;

    protected $token;

    protected $validity;

    private $createdAt;

    private $updatedAt;

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
        $builder->setTable('grg_password_reset')
            ->setCustomRepositoryClass('MauticPlugin\GRGPasswordResetOverrideBundle\Repository\PasswordResetRepository');
        $builder->createField('id', 'integer')->makePrimaryKey()->generatedValue()->build();
        $builder->addField('token', 'string');
        $builder->addField('validity', 'integer');
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     *
     * @return PasswordReset
     */
    public function setToken($token)
    {
        $this->token = $token;

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
}
