<?php

namespace Rz\NewsBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;

use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

class PostHasMediaManager extends BaseEntityManager
{
    public function getFirstMedia($post) {
        $query = $this->getRepository()
            ->createQueryBuilder('phm')
            ->where('phm.post = :post')
            ->addOrderBy('phm.position', 'ASC')
            ->addOrderBy('phm.id', 'ASC')
            ->setMaxResults(1)
            ->setParameter('post', $post);
        return $query->getQuery()->getSingleResult();
    }
}
