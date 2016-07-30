<?php

namespace Rz\NewsBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

class SuggestedArticlesManager extends BaseEntityManager
{
    public function getFirstMedia($post)
    {
        $query = $this->getRepository()
            ->createQueryBuilder('ra')
            ->where('ra.post = :post')
            ->addOrderBy('ra.position', 'ASC')
            ->addOrderBy('ra.id', 'ASC')
            ->setMaxResults(1)
            ->setParameter('post', $post)
            ->getQuery()
            ->useResultCache(true, 3600);
        return $query->getSingleResult();
    }
}
