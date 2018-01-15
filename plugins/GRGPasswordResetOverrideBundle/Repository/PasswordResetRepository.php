<?php

/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 10/01/2018
 * Time: 13:25.
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Repository;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class PasswordResetRepository.
 */
class PasswordResetRepository extends CommonRepository
{
    /**
     * @param $token
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntityByToken($token)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.token = :token')
            ->setParameter('token', $token)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
