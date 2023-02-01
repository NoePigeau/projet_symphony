<?php

namespace App\Repository;

use App\Entity\Mission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mission>
 *
 * @method Mission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mission[]    findAll()
 * @method Mission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mission::class);
    }

    public function save(Mission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Mission[] Returns an array of Mission objects
     */
    public function search($request): QueryBuilder
    {
        $query = $request->request->get('query');
        $type = $request->request->get('type');
        $limit = $request->request->get('limit');

        $qr = $this->createQueryBuilder('m')
            ->orderBy('m.name', 'ASC')
            ->setMaxResults($limit)
        ;

        if ($query) {
            $qr
                ->andWhere('m.name LIKE :val')
                ->setParameter('val', '%' . $query . '%')
            ;
        }

        if ($type) {
            $qr
                ->andWhere('m.type = :type')
                ->setParameter('type', $type)
            ;
        }

        return $qr;
    }

   public function findMissionDetail($id): ?Mission
   {
       return $this->createQueryBuilder('m')
            ->andWhere('m.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('m.agent', 'agent' )
            ->leftJoin('m.client', 'client' )
            ->getQuery()
            ->getOneOrNullResult();
    }
}
