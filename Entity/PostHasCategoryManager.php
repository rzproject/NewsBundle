<?php

namespace Rz\NewsBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;

use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

class PostHasCategoryManager extends BaseEntityManager
{
    public function getUniqueCategories() {
        $query = $this->getRepository()
            ->createQueryBuilder('phc')
            ->select('c.id, c.name')
            ->leftJoin('phc.category', 'c')
            ->addGroupBy('c.name')
            ->getQuery()
            ->useResultCache(true, 3600);

        return $query->getResult();
    }


}