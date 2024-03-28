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
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

 
 class OperationRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Operation::class);
        $this->entityManager = $entityManager;
    }
    // Définir la méthode pour trouver le salarié avec le plus de missions
    public function getMissionStatistics(): array
    {
        $allStats = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS numMissions', 'u.name AS name', 'u.roles')
            ->join('o.salarie', 'u')
            ->groupBy('u.name')
            ->getQuery()
            ->getResult();
    
        return array_filter($allStats, function($stat) {
            $userRoles = $stat['roles']; // C'est un tableau de rôles
            return in_array('ROLE_APPRENTI', $userRoles) || in_array('ROLE_SENIOR', $userRoles) || in_array('ROLE_ADMIN', $userRoles);
        });
    }
    
    

    

    public function getTotalSales(): float
    {
        // Liste des colonnes contenant des prix
        $priceColumns = ['o.price']; // Ajoutez ici d'autres colonnes si nécessaire

        // Construction de la requête pour calculer la somme des prix
        $qb = $this->createQueryBuilder('o');
        $qb->select('SUM(' . implode(' + ', $priceColumns) . ') as totalSales');

        // Exécution de la requête et récupération du résultat
        $result = $qb->getQuery()->getSingleScalarResult();

        // Retour du chiffre d'affaires total
        return (float) $result;
    }
// Méthode pour récupérer les ventes par date
public function getSalesByDate(): array
{
    // Créer le QueryBuilder pour construire la requête SQL
    $qb = $this->createQueryBuilder('o');

    // Sélectionner la somme des prix et formater la date par mois et année
    return $this->createQueryBuilder('o')
        ->select('SUM(o.price) AS totalSales', 'SUBSTRING(o.created_at, 1, 7) AS monthYear')
        ->groupBy('monthYear')
        ->orderBy('monthYear', 'ASC')
        ->getQuery()
        ->getResult();
    // Exécuter la requête et retourner les résultats
    return $qb->getQuery()->getResult();
}


public function findByOperationTypeStatistics(): array
{
    return $this->createQueryBuilder('o')
        ->select('COUNT(o.id) AS numOperations', 'o.type AS type')
        ->groupBy('o.type')
        ->getQuery()
        ->getResult();
}
}



