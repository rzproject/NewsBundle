<?php


namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class PostHasMediaAdmin extends Admin
{
    protected $parentAssociationMapping = 'post';

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if (interface_exists('Sonata\MediaBundle\Model\MediaInterface')) {
            $formMapper->add('media', 'sonata_type_model_list', array('btn_delete' => false), array(
                'link_parameters' => array('context' => 'news', 'hide_context' => true, 'mode' => 'list'),
            ));
        }

        $formMapper
            ->add('content', 'sonata_formatter_type', array(
                'event_dispatcher' => $formMapper->getFormBuilder()->getEventDispatcher(),
                'error_bubbling' => false,
                'format_field'   => 'contentFormatter',
                'source_field'   => 'rawContent',
                'ckeditor_context' => 'news',
                'source_field_options'      => array(
                    'error_bubbling'=>false,
                    'attr' => array('rows' => 20)
                ),
                'target_field'   => 'content',
                'listener'       => true,
            ))
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
            ->add('media')
            ->add('post')
            ->add('position')
            ->add('enabled')
        ;
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

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('post')
            ->add('media.name')
            ->add('enabled');
    }
}
