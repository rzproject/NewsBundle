<?php

namespace Rz\NewsBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Rz\NewsBundle\Model\PostHasCategoryInterface;
use Sonata\NewsBundle\Model\PostInterface;
use Sonata\ClassificationBundle\Model\CategoryInterface;

class PrePersistRelationValidator extends ConstraintValidator
{

    /**
     * @param string                          $value
     * @param PasswordRequirements|Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (null === $entity || '' === $entity) {
            return;
        }

        if(!$entity instanceof PostInterface && $entity->getId() ) {
            return;
        }

        if($entity instanceof PostInterface) {
            // Category
            if($entity->getPostHasCategory()->count() > 0) {
                $categories = $entity->getPostHasCategory();
                $categs = array();
                $maps = array();
                foreach ($categories as $category) {
                    if($category instanceof PostHasCategoryInterface && $category->getCategory() != null && $id = $category->getCategory()->getId()) {
                        $categs[] = $id;
                        $maps[$id] = $category->getCategory()->getName();
                    }
                }

                $categs = array_count_values($categs);
                $errors = array();
                foreach($categs as $key=>$value) {
                    if($value > 1) {
                        $errors[] = $maps[$key];
                    }
                }

                if(count($errors) > 0) {
                    if ($this->context instanceof ExecutionContextInterface) {
                        $this->context->buildViolation($constraint->unique)
                            ->setParameter('{{ entity_name }}', 'category')
                            ->setParameter('{{ value }}', implode(", ", $errors))
                            ->atPath('postHasCategory')
                            ->addViolation();
                    } else {
                        $this->context->addViolationAt('postHasCategory', $constraint->unique, array('{{ entity_name }}' => 'category', '{{ value }}' => implode(", ", $errors)));
                    }

                }
            }

//            // Screenshots
//            if($entity->getPortfolioHasMedia()->count() > 0) {
//                $screenshots = $entity->getPortfolioHasMedia();
//                $screens = array();
//                $maps = array();
//                foreach ($screenshots as $screenshot) {
//                    if($screenshot instanceof PortfolioHasMediaInterface && $screenshot->getMedia() != null && $id = $screenshot->getMedia()->getId()) {
//                        $screens[] = $id;
//                        $maps[$id] = $screenshot->getMedia()->getName();
//                    }
//                }
//
//                $screens = array_count_values($screens);
//                $errors = array();
//                foreach($screens as $key=>$value) {
//                    if($value > 1) {
//                        $errors[] = $maps[$key];
//                    }
//                }
//
//
//
//                if(count($errors) > 0) {
//                    if ($this->context instanceof ExecutionContextInterface) {
//                        $this->context->buildViolation($constraint->unique)
//                            ->setParameter('{{ entity_name }}', 'screenshot')
//                            ->setParameter('{{ value }}', implode(", ", $errors))
//                            ->atPath('portfolioHasMedia')
//                            ->addViolation();
//                    } else {
//                        $this->context->addViolationAt('portfolioHasMedia', $constraint->unique, array('{{ entity_name }}' => 'screenshot', '{{ value }}' => implode(", ", $errors)));
//                    }
//
//                }
//            }


        }

        return;
    }
}
