<?php

namespace Rz\NewsBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Rz\NewsBundle\Model\PostSetsInterface;
use Rz\NewsBundle\Model\PostSetsHasPostsInterface;

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

        if(!$entity instanceof PostSetsInterface && $entity->getId() ) {
            return;
        }

        if($entity instanceof PostSetsInterface) {

            if($entity->getPostSetsHasPosts()->count() > 0) {
                $postSetsHasPosts = $entity->getPostSetsHasPosts();
                $meds = array();
                $maps = array();
                foreach ($postSetsHasPosts as $postSetsHasPost) {
                    if($postSetsHasPost instanceof PostSetsHasPostsInterface && $postSetsHasPost->getPost() != null && $id = $postSetsHasPost->getPost()->getId()) {
                        $meds[] = $id;
                        $maps[$id] = $postSetsHasPost->getPost()->getTitle();
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
                            ->setParameter('{{ entity_name }}', 'post')
                            ->setParameter('{{ value }}', implode(", ", $errors))
                            ->atPath('postSetsHasPosts')
                            ->addViolation();
                    } else {
                        $this->context->addViolationAt('postSetsHasPosts', $constraint->unique, array('{{ entity_name }}' => 'post', '{{ value }}' => implode(", ", $errors)));
                    }
                }
            }
        }
        return;
    }
}
