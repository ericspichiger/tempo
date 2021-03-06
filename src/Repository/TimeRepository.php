<?php

namespace App\Repository;

/**
 * TimeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TimeRepository extends \Doctrine\ORM\EntityRepository
{
    function getByDossierExercice($idDossier, $exercice, $dateDebut, $dateFin, $forever)
    {
        if ($forever == false)
        {
            $query = $this->createQueryBuilder('t')
                ->where('t.dossier = ' . $idDossier)
                ->andWhere('t.exercice = ' . $exercice)
                ->andWhere('t.date BETWEEN :debut AND :fin')
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
            ;
        }
        else
        {
            $query = $this->createQueryBuilder('t')
                ->where('t.dossier = ' . $idDossier)
                ->andWhere('t.exercice = ' . $exercice)
            ;
        }

        return $query->getQuery()->getResult();
    }

    function getSumByDossierExercice($idDossier, $exercice, $dateDebut, $dateFin, $forever)
    {
        if ($forever == false)
        {
            $query = $this->createQueryBuilder('t')
                ->join('t.tache', 'tac')
                ->where('t.dossier = ' . $idDossier)
                ->andWhere('t.exercice = ' . $exercice)
                ->andWhere('t.date BETWEEN :debut AND :fin')
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
                ->select('tac.intitule AS intitule, tac.numero AS num, SUM(t.tempspasse) AS tempstotal')
                ->groupBy('t.tache')
            ;
        }
        else
        {
            $query = $this->createQueryBuilder('t')
                ->join('t.tache', 'tac')
                ->where('t.dossier = ' . $idDossier)
                ->andWhere('t.exercice = ' . $exercice)
                ->select('tac.intitule AS intitule, tac.numero AS num, SUM(t.tempspasse) AS tempstotal')
                ->groupBy('t.tache')
            ;
        }

        return $query->getQuery()->getResult();
    }

    public function getByCompany($company)
    {
        return $this->createQueryBuilder('t')
            ->join('t.user', 'u')
            ->join('u.company', 'c')
            ->where('u.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult()
            ;
    }
}
