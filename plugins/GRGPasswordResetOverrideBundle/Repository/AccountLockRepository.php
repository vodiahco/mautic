<?php

/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 10/01/2018
 * Time: 13:25.
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Repository;

use Mautic\CoreBundle\Entity\CommonRepository;
use MauticPlugin\GRGPasswordResetOverrideBundle\Entity\AccountLock;

/**
 * Class AccountLockRepository.
 */
class AccountLockRepository extends CommonRepository
{
    /**
     * @param $userId
     * @return null|AccountLock
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntityByUserId($userId)
    {
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
