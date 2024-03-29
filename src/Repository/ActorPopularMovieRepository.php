<?php

namespace App\Repository;

use App\Entity\ActorPopularMovie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ActorPopularMovie|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActorPopularMovie|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActorPopularMovie[]    findAll()
 * @method ActorPopularMovie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActorPopularMovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActorPopularMovie::class);
    }

}
