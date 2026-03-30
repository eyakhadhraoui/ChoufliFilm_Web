<?php

namespace App\Repository;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

use Doctrine\ORM\EntityManagerInterface;
/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function verification($email, $pass, EntityManagerInterface $em)
    {
        $request = $em->createQuery('SELECT s FROM App\Entity\User s WHERE s.email = :email AND s.confirm_password = :pass');
        $request->setParameter('email', $email);
        $request->setParameter('pass', $pass);
    
        $result = $request->getResult();
    
      
        return !empty($result);
    }

    public function findActiveUsers(int $page =1 ,int $limit = 1): array
    {
        $limit=abs($limit);
        $result= [] ;
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('deleted', 0)
            ->setMaxResults($limit)
            ->setFirstResult(($page*$limit)-$limit);
            $paginator = new Paginator($query);
            $data = $paginator->getQuery()->getResult();
             if (empty($data)){
                return $query->getQuery()->getResult();  
             }
        $pages = ceil($paginator->count() /$limit);
        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page']= $page;
        $result['limit'] =$limit;

            return $result;
    }
    public function showall(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('deleted', 0)
            ->getQuery()
            ->getResult();
    }
    public function findInactiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('deleted', 1)
            ->getQuery()
            ->getResult();
            
    }
    public function generateVerificationCode()
    {
        return substr(md5(uniqid(mt_rand(), true)), 0, 6); 
    }

    public function countUsersByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.roles, COUNT(u.id) as count')
            ->groupBy('u.roles')
            ->getQuery()
            ->getResult();
    }

    public function getBannedUsersByMonth(): array
    {
        return $this->createQueryBuilder('u')
            ->select("SUBSTRING(u.bannedAt, 1, 3) AS month, COUNT(u.id) AS count") // Prendre le mois (ex: 'Nov')
            ->where('u.banned = 1') 
            ->andWhere('u.bannedAt IS NOT NULL')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();
    }






}


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

