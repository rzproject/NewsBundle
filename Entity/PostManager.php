<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\PostManager as BasePostManager;
use Doctrine\ORM\Query\Expr\Join;
use Sonata\ClassificationBundle\Model\CollectionInterface;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;
use Sonata\NewsBundle\Model\BlogInterface;
use Sonata\NewsBundle\Model\PostInterface;
use Sonata\NewsBundle\Model\PostManagerInterface;

class PostManager extends BasePostManager
{
    /**
     * @param string        $permalink
     * @param BlogInterface $blog
     *
     * @return PostInterface
     */
    public function findOneByPermalink($permalink, BlogInterface $blog)
    {
        $repository = $this->getRepository();

        $query = $repository->createQueryBuilder('p');

        $urlParameters = $blog->getPermalinkGenerator()->getParameters($permalink);

        $parameters = array();

        if (isset($urlParameters['year']) && isset($urlParameters['month']) && isset($urlParameters['day'])) {
            $pdqp = $this->getPublicationDateQueryParts(sprintf('%d-%d-%d', $urlParameters['year'], $urlParameters['month'], $urlParameters['day']), 'day');

            $parameters = array_merge($parameters, $pdqp['params']);

            $query->andWhere($pdqp['query']);
        }

        if (isset($urlParameters['slug'])) {
            $query->andWhere('p.slug = :slug');
            $parameters['slug'] = $urlParameters['slug'];
        }

        if (isset($urlParameters['collection'])) {
            $pcqp = $this->getPublicationCollectionQueryParts($urlParameters['collection']);

            $parameters = array_merge($parameters, $pcqp['params']);

            $query
                ->leftJoin('p.collection', 'c')
                ->andWhere($pcqp['query'])
            ;
        }

        if (count($parameters) == 0) {
            return;
        }

        $query->setParameters($parameters);

        $results = $query->getQuery()
            ->useResultCache(true, 3600)
            ->getResult();

        if (count($results) > 0) {
            return $results[0];
        }

        return;
    }
}
