<?php


namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class PostHasCategoryAdmin extends Admin
{

    protected $parentAssociationMapping = 'post';
//    protected $formOptions = array(
//        'cascade_validation' => true,
//        'error_bubbling' => false,
//        'validation_groups'=>array('admin')
//    );

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
//        $link_parameters = array();
//
//        if ($this->hasParentFieldDescription()) {
//            $link_parameters = $this->getParentFieldDescription()->getOption('link_parameters', array());
//        }
//
//        if ($this->hasRequest()) {
//            $context = $this->getRequest()->get('context', null);
//
//            if (null !== $context) {
//                $link_parameters['context'] = $context;
//            }
//        }

        if (interface_exists('Sonata\ClassificationBundle\Model\CategoryInterface')) {
            $formMapper->add('category', 'sonata_type_model_list', array('btn_delete' => false), array(
                'link_parameters' => array('context' => 'news', 'hide_context' => true, 'mode' => 'list'),
            ));
        }

        $formMapper
            ->add('enabled', null, array('required' => false))
            ->add('position', 'hidden')
        ;
    }

    /**
     * @param  \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('category.name', null, array('footable'=>array('attr'=>array('data_toggle'=>true))))
            ->add('enabled', null, array('footable'=>array('attr'=>array('data_hide'=>'phone,tablet')), 'editable' => true))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'Show' => array('template' => 'SonataAdminBundle:CRUD:list__action_show.html.twig'),
                    'Edit' => array('template' => 'SonataAdminBundle:CRUD:list__action_edit.html.twig'),
                    'Delete' => array('template' => 'SonataAdminBundle:CRUD:list__action_delete.html.twig')),
                'footable'=>array('attr'=>array('data_hide'=>'phone,tablet')),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('post')
            ->add('category')
            ->add('enabled');
    }

    /**
     * {@inheritdoc}
     */
    public function getListModes()
    {
        return parent::getListMode();
    }

    /**
     * {@inheritdoc}
     */
    public function setListMode($mode)
    {
        return parent::setListMode($mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getListMode()
    {
        return parent::getListMode();
    }
}
