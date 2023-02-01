<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updatePassword(User $user, string $newHashedPassword): void
    {
        if($user->getPlainPassword() != null) {
            $user->setPassword($newHashedPassword);
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function search($request): QueryBuilder
    {
        $query = $request->request->get('query');
        $limit = $request->request->get('limit');

        $qr = $this->createQueryBuilder('m')
            ->orderBy('m.email', 'ASC')
            ->setMaxResults($limit)
        ;

        if ($query) {
            $qr
                ->andWhere('lower(m.email) LIKE :val')
                ->orWhere('lower(m.nickname) LIKE :val')
                ->orWhere('lower(m.firstname) LIKE :val')
                ->orWhere('lower(m.lastname) LIKE :val')
                ->setParameter('val', '%' . strtolower($query) . '%')
            ;
        }

        return $qr;
    }
}
