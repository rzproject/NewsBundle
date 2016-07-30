<?php

namespace Rz\NewsBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

class PostHasCategoryManager extends BaseEntityManager
{
    public function getUniqueCategories()
    {
        $query = $this->getRepository()
            ->createQueryBuilder('phc')
            ->select('c.id, c.name, p.name as parent')
            ->leftJoin('phc.category', 'c')
            ->leftJoin('c.parent', 'p')
            ->addGroupBy('c.name')
            ->getQuery()
            ->useResultCache(true, 3600);

        return $query->getResult();
    }
}
