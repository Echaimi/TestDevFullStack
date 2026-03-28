<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /** @return list<Order> */
    public function findByCustomerOrdered(Customer $customer): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :c')
            ->setParameter('c', $customer)
            ->orderBy('o.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
