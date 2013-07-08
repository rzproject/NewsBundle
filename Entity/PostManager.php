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
}
