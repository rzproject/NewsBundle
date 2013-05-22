<?php

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\TagAdmin as BaseTagAdmin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class TagAdmin extends BaseTagAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, array('footable'=>array('attr'=>array('data_toggle'=>true))))
            ->add('slug', null, array('footable'=>array('attr'=>array('data_hide'=>'phone,tablet'))))
            ->add('enabled', null, array('editable' => true, 'footable'=>array('attr'=>array('data_hide'=>'phone'))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('posts', null ,array('field_options'=>array('selectpicker_enabled'=>true, 'selectpicker_data_size' => 3, 'selectpicker_dropup' => true),
                                       'operator_options'=>array('selectpicker_enabled'=>true, 'selectpicker_data_size' => 3, 'selectpicker_dropup' => true)))
        ;
    }
}
