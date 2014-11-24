<?php


namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class PostHasMediaAdmin extends Admin
{
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
        $link_parameters = array();

        if ($this->hasParentFieldDescription()) {
            $link_parameters = $this->getParentFieldDescription()->getOption('link_parameters', array());
        }

        if ($this->hasRequest()) {
            $context = $this->getRequest()->get('context', null);

            if (null !== $context) {
                $link_parameters['context'] = $context;
            }
        }

        if (interface_exists('Sonata\MediaBundle\Model\MediaInterface')) {
            $formMapper->add('media', 'sonata_type_model_list', array('btn_delete' => false), array(
                'link_parameters' => $link_parameters
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
}
