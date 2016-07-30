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
        $formMapper
            ->tab('tab.rz_news_post_has_media_media')
                ->with('rz_news_post_has_media_media', array('class' => 'col-md-8'))->end()
            ->end()
            ->tab('tab.rz_news_post_has_media_content')
                ->with('rz_news_post_has_media_content', array('class' => 'col-md-12'))->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_post_has_media_media')
                ->with('rz_news_post_has_media_media', array('class' => 'col-md-8'))
                    ->add('title', null)
                    ->add('abstract', null, array('attr' => array('rows' => 5)))
                ->end()
            ->end()
        ;

        if (interface_exists('Sonata\MediaBundle\Model\MediaInterface')) {
            $formMapper
                ->tab('tab.rz_news_post_has_media_media')
                    ->with('rz_news_post_has_media_media', array('class' => 'col-md-8'))
                       ->add('media', 'sonata_type_model_list', array('btn_delete' => false), array())
                    ->end()
                ->end()
            ;
        }

        $formMapper
            ->tab('tab.rz_news_post_has_media_media')
                ->with('rz_news_post_has_media_media', array('class' => 'col-md-8'))
                    ->add('enabled', null, array('required' => false))
                    ->add('position', 'hidden')
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_post_has_media_content')
                ->with('rz_news_post_has_media_content', array('class' => 'col-md-8'))
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
                ->end()
            ->end()
        ;

        $formMapper

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
