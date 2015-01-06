<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\PostManager as ModelPostManager;
use Sonata\ClassificationBundle\Model\CollectionInterface;
use Sonata\NewsBundle\Model\BlogInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;


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

    /**
     * @param string $date  Date in format YYYY-MM-DD
     * @param string $step  Interval step: year|month|day
     * @param string $alias Table alias for the publicationDateStart column
     *
     * @return array
     */
    public function fetchPublicationDateQueryParts($date, $step, $alias = 'p')
    {
        return $this->getPublicationDateQueryParts($date, $step, $alias);
    }

    /**
     * {@inheritdoc}
     *
     * Valid criteria are:
     *    enabled - boolean
     *    date - query
     *    tag - string
     *    author - 'NULL', 'NOT NULL', id, array of ids
     *    collections - CollectionInterface
     *    mode - string public|admin
     */
    public function getNewsPager(array $criteria, array $sort = array())
    {

        if (!isset($criteria['mode'])) {
            $criteria['mode'] = 'public';
        }

        $parameters = array();
        $query = $this->getRepository()
            ->createQueryBuilder('p')
            ->select('p, t')
            ->orderBy('p.publicationDateStart', 'DESC');

        if ($criteria['mode'] == 'admin') {
            $query
                ->leftJoin('p.tags', 't')
                ->leftJoin('p.author', 'a')
                ->leftJoin('p.postHasCategory', 'phc')
                ->leftJoin('phc.category', 'cat')
            ;
        } else {
            $query
                ->leftJoin('p.tags', 't', Join::WITH, 't.enabled = true')
                ->leftJoin('p.author', 'a', Join::WITH, 'a.enabled = true')
                ->leftJoin('p.postHasCategory', 'phc',  Join::WITH, 'phc.enabled = true')
                ->leftJoin('phc.category', 'cat',  Join::WITH, 'cat.enabled = true')
            ;
        }

        if (!isset($criteria['enabled']) && $criteria['mode'] == 'public') {
            $criteria['enabled'] = true;
        }
        if (isset($criteria['enabled'])) {
            $query->andWhere('p.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        if (isset($criteria['date']) && isset($criteria['date']['query']) && isset($criteria['date']['params'])) {
            $query->andWhere($criteria['date']['query']);
            $parameters = array_merge($parameters, $criteria['date']['params']);
        }

        if (isset($criteria['tag'])) {
            $query->andWhere('t.slug LIKE :tag');
            $parameters['tag'] = (string) $criteria['tag'];
        }

        if (isset($criteria['author'])) {
            if (!is_array($criteria['author']) && stristr($criteria['author'], 'NULL')) {
                $query->andWhere('p.author IS ' . $criteria['author']);
            } else {
                $query->andWhere(sprintf('p.author IN (%s)', implode((array) $criteria['author'], ',')));
            }
        }

        if (isset($criteria['category'])) {
            if (!is_array($criteria['category'])) {
                $query->andWhere('cat.slug LIKE :category');
                $parameters['category'] = $criteria['category'];
            } else {
                $cat = null;
                foreach($criteria['category'] as $slug) {
                    $cat[] = sprintf("'%s'", $slug);
                }
                $query->andWhere(sprintf('cat.slug IN (%s)', implode((array) $cat, ',')));
            }
        }

        if (isset($criteria['collection']) && $criteria['collection'] instanceof CollectionInterface) {
            $query->andWhere('p.collection = :collectionid');
            $parameters['collectionid'] = $criteria['collection']->getId();
        }

        $query->setParameters($parameters);


        try {
            return new Pagerfanta(new DoctrineORMAdapter($query));
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param string        $permalink
     * @param BlogInterface $blog
     *
     * @return PostInterface
     */
    public function findOneByCategoryPermalink($permalink, BlogInterface $blog)
    {


        $repository = $this->getRepository();

        $query = $repository->createQueryBuilder('p');

        $urlParameters = $blog->getPermalinkGenerator()->getParametersWithCategory($permalink);

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
            return null;
        }

        $query->setParameters($parameters);

        $results = $query->getQuery()->getResult();

        if (count($results) > 0) {
            return $results[0];
        }

        return null;
    }

    /**
     * @param $post
     *
     */
    public function getNearestPost($post)
    {
        return $this->getObjectManager()->createQuery(sprintf("SELECT p FROM %s p WHERE p.id != %s and p.enabled = true ORDER BY DATE_DIFF( p.publicationDateStart, '%s' )",
            $this->getClass(),
            $post->getId(),
            $post->getPublicationDateStart()->format('Y-m-d h:i:s')))
            ->setMaxResults(2)
            ->execute();

    }

    public function getAllPostForSingleNavi() {
        $query = $this->getRepository()
            ->createQueryBuilder('p')
            ->select('p.slug')
            ->orderBy('p.publicationDateStart', 'DESC');
        return $query->getQuery()->getArrayResult();
    }
}
