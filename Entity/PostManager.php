<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\PostManager as ModelPostManager;
use Doctrine\ORM\Query\Expr;

use Doctrine\ORM\Query;

class PostManager extends ModelPostManager
{
    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->em->getRepository($this->class)->findAll();
    }


    public function getTagCount()
    {
        $query = $this->getRepository()
                      ->createQueryBuilder('p')
                      ->select('count(t.id) as tagCount, t.name, t.slug')
                      ->leftJoin('p.tags', 't')
                      ->groupBy('t.id');
        return $query->getQuery()->getArrayResult();
    }

    public function getCollections($limit = 5)
    {
        $query = $this->getRepository()
                      ->createQueryBuilder('p')
                      ->select('count(c.id) as collectionCount, c.name, c.slug')
                      ->leftJoin('p.collection', 'c')
                      ->groupBy('c.id')
                      ->setMaxResults($limit);
        return $query->getQuery()->getArrayResult();
    }

}
