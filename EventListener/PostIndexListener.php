<?php

namespace Rz\NewsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Sonata\NewsBundle\Model\PostInterface;

use Ivory\LuceneSearchBundle\Model\LuceneManager;


class PostIndexListener
{
    protected $lucene;

    public function __construct(LuceneManager $lucene) {
        $this->lucene = $lucene;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof PostInterface) {
//            die('here');
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof PostInterface) {
//            var_dump($this->lucene);
//
//            die();
        }
    }
}