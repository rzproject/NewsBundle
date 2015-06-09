<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\PostManager as ModelPostManager;
use Sonata\ClassificationBundle\Model\CollectionInterface;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\TagInterface;
use Sonata\NewsBundle\Model\BlogInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;


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
                      ->groupBy('t.id')
                      ->getQuery()
                      ->useResultCache(true, 3600);

        return $query->getArrayResult();
    }

    public function getCollections($limit = 5)
    {
        $query = $this->getRepository()
                      ->createQueryBuilder('p')
                      ->select('count(c.id) as collectionCount, c.name, c.slug')
                      ->leftJoin('p.collection', 'c')
                      ->groupBy('c.id')
                      ->setMaxResults($limit)
                      ->getQuery()
                      ->useResultCache(true, 3600);
        return $query->getArrayResult();
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
        $query = $this->buildQuery($criteria, $sort);

        $query->getQuery()
              ->useResultCache(true, 3600);
        try {
            return new Pagerfanta(new DoctrineORMAdapter($query));
        } catch (NoResultException $e) {
            return null;
        }
    }

	public function getCustomNewsPager(array $criteria, array $sort = array())
	{
		$query = $this->buildCustomQuery($criteria, $sort);

		$query->getQuery()
			->useResultCache(true, 3600);
		try {
			return new Pagerfanta(new DoctrineORMAdapter($query));
		} catch (NoResultException $e) {
			return null;
		}
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
    public function getNewsNativePager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        $query = $this->buildQuery($criteria, $sort);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }

    protected function buildQuery(array $criteria, array $sort = array()) {
        if (!isset($criteria['mode'])) {
            $criteria['mode'] = 'public';
        }

        $parameters = array();
        $query = $this->getRepository()
            ->createQueryBuilder('p')
            ->select('p, t');

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
        } elseif(isset($criteria['tag_id'])) {
	        if (!is_array($criteria['tag_id'])) {
		        $query->andWhere('t.id = :tag');
		        if($criteria['tag_id'] instanceof TagInterface) {
			        $parameters['tag'] = $criteria['tag_id']->getId();
		        } else {
			        $parameters['tag'] = $criteria['tag_id'];
		        }
	        } else {
		        $tags = null;
		        foreach($criteria['tag_id'] as $id) {
			        $tags[] = sprintf("'%s'", $id);
		        }
		        $query->andWhere(sprintf('t.id IN (%s)', implode((array) $tags, ',')));
	        }
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
                if($criteria['category'] instanceof CategoryInterface) {
                    $parameters['category'] = $criteria['category']->getSlug();
                } else {
                    $parameters['category'] = $criteria['category'];
                }
            } else {
                $cat = null;
                foreach($criteria['category'] as $slug) {
                    $cat[] = sprintf("'%s'", $slug);
                }
                $query->andWhere(sprintf('cat.slug IN (%s)', implode((array) $cat, ',')));
            }
        } elseif(isset($criteria['category_id'])) {
            if (!is_array($criteria['category_id'])) {
                $query->andWhere('cat.id = :category');
                if($criteria['category_id'] instanceof CategoryInterface) {
                    $parameters['category'] = $criteria['category_id']->getId();
                } else {
                    $parameters['category'] = $criteria['category_id'];
                }
            } else {
                $cat = null;
                foreach($criteria['category_id'] as $id) {
                    $cat[] = sprintf("'%s'", $id);
                }
                $query->andWhere(sprintf('cat.id IN (%s)', implode((array) $cat, ',')));
            }
        }

        if (isset($criteria['collection']) && $criteria['collection'] instanceof CollectionInterface) {
            $query->andWhere('p.collection = :collectionid');
            $parameters['collectionid'] = $criteria['collection']->getId();
        }

	    if($sort) {
		    foreach($sort as $field=>$order) {
			    if($field == 'publicationDateStart') {
				    $query->orderBy('p.publicationDateStart', $order);
			    }
		    }
	    } else {
		    $query->orderBy('p.publicationDateStart', 'DESC');
	    }

        $query->setParameters($parameters);

        return $query;
    }

	protected function buildCustomQuery(array $criteria, array $sort = array()) {
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
			if($criteria['tag'] instanceof TagInterface) {
				$parameters['tag'] = $criteria['tag']->getSlug();
				$query->andWhere('t.slug = :tag');
			} else {
				$parameters['tag'] = (string) $criteria['tag'];
				$query->andWhere('t.slug LIKE :tag');
			}
		} elseif(isset($criteria['tag_id'])) {
			if (!is_array($criteria['tag_id'])) {
				$query->andWhere('t.id = :tag');
				if($criteria['tag_id'] instanceof TagInterface) {
					$parameters['tag'] = $criteria['tag_id']->getId();
				} else {
					$parameters['tag'] = $criteria['tag_id'];
				}
			} else {
				$tags = null;
				foreach($criteria['tag_id'] as $id) {
					$tags[] = sprintf("'%s'", $id);
				}
				$query->andWhere(sprintf('t.id IN (%s)', implode((array) $tags, ',')));

				$query->andWhere(sprintf('t.id IN (%s)', implode((array) $tags, ',')));
				$query->addSelect(sprintf('FIELD(t.id,%s) as HIDDEN tag_field', implode((array) $tags, ',')));
				$query->addOrderBy('tag_field');
			}
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
				if($criteria['category'] instanceof CategoryInterface) {
					$parameters['category'] = $criteria['category']->getSlug();
				} else {
					$parameters['category'] = $criteria['category'];
				}
			} else {
				$cat = null;
				foreach($criteria['category'] as $slug) {
					$cat[] = sprintf("'%s'", $slug);
				}
				$query->andWhere(sprintf('cat.slug IN (%s)', implode((array) $cat, ',')));
			}
		} elseif(isset($criteria['category_id'])) {
			if (!is_array($criteria['category_id'])) {
				$query->andWhere('cat.id = :category');
				$query->orderBy('FIELD(cat.id,:category)');
				if($criteria['category_id'] instanceof CategoryInterface) {
					$parameters['category'] = $criteria['category_id']->getId();
				} else {
					$parameters['category'] = $criteria['category_id'];
				}
			} else {
				$cat = null;
				foreach($criteria['category_id'] as $id) {
					$cat[] = sprintf("'%s'", $id);
				}
				$query->andWhere(sprintf('cat.id IN (%s)', implode((array) $cat, ',')));
				$query->addSelect(sprintf('FIELD(cat.id,%s) as HIDDEN category_field', implode((array) $cat, ',')));
				$query->addOrderBy('category_field');
			}
		}

		if (isset($criteria['collection']) && $criteria['collection'] instanceof CollectionInterface) {
			$query->andWhere('p.collection = :collectionid');
			$parameters['collectionid'] = $criteria['collection']->getId();
		}

//		if($sort) {
//			foreach($sort as $field=>$order) {
//				if($field == 'publicationDateStart') {
//					$query->orderBy('p.publicationDateStart', $order);
//				}
//			}
//		} else {
//			$query->orderBy('p.publicationDateStart', 'DESC');
//		}

		#custom order by category



		$query->setParameters($parameters);

		return $query;
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

        $results = $query ->getQuery()
                          ->useResultCache(true, 3600)
                          ->getResult();

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
                 ->useResultCache(true, 3600)
                 ->execute();

    }

    public function getAllPostForSingleNavi() {
        $query = $this->getRepository()
            ->createQueryBuilder('p')
            ->select('p.slug')
            ->orderBy('p.publicationDateStart', 'DESC')
            ->getQuery()
            ->useResultCache(true, 3600);
        return $query->getArrayResult();
    }
}
