<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\CategoryManager as BaseCategoryManager;
use Sonata\NewsBundle\Model\CategoryInterface;

use Doctrine\ORM\EntityManager;

class CategoryManager extends BaseCategoryManager
{

  public function fetchCategoriesTree() {
      return $this->em->getRepository($this->class)->childrenHierarchy();
  }

  public function fetchCategories() {
        $entityManager = $this->em->getRepository($this->class);
        $query = $entityManager->createQueryBuilder('category')
                 ->orderBy('category.root', 'ASC')
                 ->addOrderBy('category.left', 'ASC')
                 ->getQuery();
        return $query->getResult();
  }
}
