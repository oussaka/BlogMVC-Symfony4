<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Add a fetchmode Eager for categories
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilderWithCategory () {
        return $this->createQueryBuilder("p")
            ->leftJoin("p.category", "c")
            ->addSelect("c")
            ->orderBy("p.createdAt", "DESC");
    }

    public function createQueryBuilderWithUser () {
        return $this->createQueryBuilder("p")
            ->leftJoin("p.user", "u")
            ->addSelect("u")
            ->orderBy("p.createdAt", "DESC");
    }

    public function createQueryBuilderWithUserAndCategory () {
        return $this->createQueryBuilder("p")
            ->leftJoin("p.category", "c")
            ->addSelect("c")
            ->leftJoin("p.user", "u")
            ->addSelect("u")
            ->orderBy("p.createdAt", "DESC");
    }

}
