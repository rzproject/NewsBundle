<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\CategoryManager as BaseCategoryManager;
use Sonata\NewsBundle\Model\CategoryInterface;

use Doctrine\ORM\EntityManager;

class CategoryManager extends BaseCategoryManager
{
  public function fetchCategories() {
      $categories = $this->em
                   ->createQuery(sprintf('SELECT c FROM %s c INDEX BY c.id', $this->class))
                   ->execute();
      return $categories;
  }
}
