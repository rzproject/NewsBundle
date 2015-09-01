<?php

namespace Rz\NewsBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Rz\NewsBundle\Model\PostHasCategoryInterface;
use Rz\NewsBundle\Model\PostHasMediaInterface;
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

            // Check Category
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


            // Check Media
            if($entity->getPostHasMedia()->count() > 0) {
                $medias = $entity->getPostHasMedia();
                $meds = array();
                $maps = array();
                foreach ($medias as $media) {
                    if($media instanceof PostHasMediaInterface && $media->getMedia() != null && $id = $media->getMedia()->getId()) {
                        $meds[] = $id;
                        $maps[$id] = $media->getMedia()->getName();
                    }
                }

                $meds = array_count_values($meds);
                $errors = array();
                foreach($meds as $key=>$value) {
                    if($value > 1) {
                        $errors[] = $maps[$key];
                    }
                }

                if(count($errors) > 0) {
                    if ($this->context instanceof ExecutionContextInterface) {
                        $this->context->buildViolation($constraint->unique)
                            ->setParameter('{{ entity_name }}', 'media')
                            ->setParameter('{{ value }}', implode(", ", $errors))
                            ->atPath('postHasMedia')
                            ->addViolation();
                    } else {
                        $this->context->addViolationAt('postHasMedia', $constraint->unique, array('{{ entity_name }}' => 'media', '{{ value }}' => implode(", ", $errors)));
                    }

                }
            }

        }

        return;
    }
}
