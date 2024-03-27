<?php

namespace App\Repository;

use App\Entity\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Operation>
 *
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

 
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    // Définir la méthode pour trouver le salarié avec le plus de missions
    public function findSalarieWithMostMissions(): ?array
    {
        $salariePlusDeMissions = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS numMissions', 'u.name AS name')
            ->join('o.salarie', 'u')
            ->groupBy('u.name')
            ->orderBy('numMissions', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $salariePlusDeMissions;
    }

    public function getMissionStatistics(): array
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS numMissions', 'u.name AS name')
            ->join('o.salarie', 'u')
            ->groupBy('u.name')
            ->getQuery()
            ->getResult();
    }

    public function findByOperationTypeStatistics(): array
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS numOperations', 'o.type AS type')
            ->groupBy('o.type')
            ->getQuery()
            ->getResult();
    }
    // Méthode pour obtenir le montant total des ventes
    public function getTotalSales(): ?float
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.amount) as totalSales')
            ->getQuery()
            ->getSingleScalarResult();
    }
// Méthode pour récupérer les ventes par date
    public function getSalesByDate(): array
    {
        $query = $this->createQueryBuilder('o')
        ->select('o.created_at as saleDate', 'SUM(o.amount) as totalSales')
        ->groupBy('saleDate')
        ->orderBy('saleDate', 'ASC')
        ->getQuery();

        return $query->getResult();
}
    


}

