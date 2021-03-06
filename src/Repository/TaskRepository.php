<?php

namespace App\Repository;

/**
 * TaskRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskRepository extends \Doctrine\ORM\EntityRepository
{
    public function findExceptItself($id, $company)
    {
        return $this->createQueryBuilder('t')
            ->where('t.id != :id')
            ->andWhere('t.company = :company')
            ->setParameter('id', $id)
            ->setParameter('company',$company)
            ->getQuery()
            ->getResult();
    }
}
