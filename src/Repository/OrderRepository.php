<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param string $uniqueId
     *
     * @return Order
     *
     * @throws NonUniqueResultException
     */
    public function findByUniqueId(string $uniqueId): ?Order
    {
        $order = $this->createQueryBuilder('o')
            ->andWhere('o.uniqueId = :uniqueId')
            ->setParameter(':uniqueId', $uniqueId)
            ->getQuery()
            ->getOneOrNullResult();

        return $order;
    }
}
