<?php

/*
 * This file is part of the RzNewsBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\CommentAdmin as BaseCommentAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class CommentAdmin extends BaseCommentAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$this->isChild()) {
            $formMapper->add('post', 'sonata_type_model_list');
//            $formMapper->add('post', 'sonata_type_admin', array(), array('edit' => 'inline'));
        }

        $commentClass = $this->commentManager->getClass();

        $formMapper
            ->add('name')
            ->add('email')
            ->add('url', null, array('required' => false))
            ->add('message', 'rz_ckeditor', array('config_name'=>'minimal_editor'))
            ->add('status', 'choice', array('choices' => $commentClass::getStatusList(), 'expanded' => true, 'multiple' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('email')
            ->add('message', null ,array('operator_options'=>array('selectpicker_dropup' => true)))
        ;
    }
}
