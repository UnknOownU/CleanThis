<?php

namespace App\Repository;

use App\Entity\Operation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Operation>
 *
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class OperationRepository extends ServiceEntityRepository
{
    private $entityManager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Operation::class);
        $this->entityManager = $entityManager;
    }
    //  méthode pour trouver le salarié avec le plus de missions
    public function getMissionStatistics(): array
    {
        $allStats = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS numMissions', 'u.name AS name', 'u.roles')
            ->join('o.salarie', 'u')
            ->groupBy('u.name')
            ->getQuery()
            ->getResult();

        return array_filter($allStats, function ($stat) {
            $userRoles = $stat['roles']; // C'est un tableau de rôles
            return in_array('ROLE_APPRENTI', $userRoles) || in_array('ROLE_SENIOR', $userRoles) || in_array('ROLE_ADMIN', $userRoles);
        });
    }

    // public function getTotalSales(): float
    // {
    //     // Liste des colonnes contenant des prix
    //     $priceColumns = ['o.price'];
    //     //requête pour calculer la somme des prix
    //     $qb = $this->createQueryBuilder('o');
    //     $qb->select('SUM(' . implode(' + ', $priceColumns) . ') as totalSales');
    //     // Exécution de la requête et récupération du résultat
    //     $result = $qb->getQuery()->getSingleScalarResult();
    //     // Retour du chiffre d'affaires total
    //     return (float) $result;
    // }

    // public function getSalesByDate(): array
    // {
    //     return $this->createQueryBuilder('o')
    //         ->select('SUM(o.price) AS totalSales', 'SUBSTRING(o.created_at, 1, 7) AS monthYear')
    //         ->groupBy('monthYear')
    //         ->orderBy('monthYear', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    public function findByOperationTypeStatistics(): array
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS numOperations', 'o.type AS type')
            ->groupBy('o.type')
            ->getQuery()
            ->getResult();
    }

    public function findMissionStatusStatistics(): array
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS numMissions', 'o.status AS status')
            ->groupBy('o.status')
            ->getQuery()
            ->getResult();
    }
    public function getTotalSales(): float
{
    // Liste des colonnes contenant des prix
    $priceColumns = ['o.price']; 
    //requête pour calculer la somme des prix
    $qb = $this->createQueryBuilder('o');
    $qb->select('SUM(' . implode(' + ', $priceColumns) . ') as totalSales');
    // Exécution de la requête et récupération du résultat
    $result = $qb->getQuery()->getSingleScalarResult();
    // Retour du chiffre d'affaires total
    return (float) $result;
}
public function getSalesByDate(): array
{
    return $this->createQueryBuilder('o')
        ->select('SUM(o.price) AS totalSales', "SUBSTRING(o.created_at, 1, 7) AS monthYear")
        ->groupBy('monthYear')
        ->orderBy('monthYear', 'ASC')
        ->getQuery()
        ->getResult();
}

public function getPodiumEmployees(): array
{
    $qb = $this->createQueryBuilder('o');

    // Obtention du premier jour du mois actuel
    $startOfMonth = new \DateTime('first day of last month');
    $startOfMonth->setTime(0, 0, 0);
    // Obtention du dernier jour du mois actuel
    $endOfMonth = new \DateTime('last day of last month');
    $endOfMonth->setTime(23, 59, 59);
    $qb->select('COUNT(o.id) AS numOperations', 'u.name AS name')
        ->join('o.salarie', 'u')
        ->where('o.created_at >= :startOfMonth')
        ->andWhere('o.created_at <= :endOfMonth')
        ->setParameter('startOfMonth', $startOfMonth)
        ->setParameter('endOfMonth', $endOfMonth)
        ->groupBy('u.name')
        ->orderBy('numOperations', 'DESC')
        ->setMaxResults(3);
        dump($qb->getQuery()->getSQL());
    return $qb->getQuery()->getResult();
}




}
