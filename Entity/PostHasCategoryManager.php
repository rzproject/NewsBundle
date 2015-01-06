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
            ->select('c.id, c.name, c.slug')
            ->leftJoin('phc.category', 'c')
            ->addGroupBy('c.name');

        return $query->getQuery()->getResult();
    }
}
