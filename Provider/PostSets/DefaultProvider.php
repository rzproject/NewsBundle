<?php

namespace Rz\NewsBundle\Provider\PostSets;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Rz\NewsBundle\Model\PostSetsInterface;

class DefaultProvider extends BaseProvider
{
    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, $object = null)
    {
        $this->buildCreateForm($formMapper, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper, $object = null)
    {
        $formMapper
            ->tab('tab.rz_news_post_sets')
                ->with('tab.group.rz_news_post_settings', array('class' => 'col-md-6'))
                    ->add('settings', 'sonata_type_immutable_array', array('keys' => $this->getFormSettingsKeys($formMapper, $object), 'required'=>false, 'label'=>false, 'attr'=>array('class'=>'rz-immutable-container')))
                ->end()
            ->end();
    }

    /**
     * @param FormMapper $formMapper
     * @param null $object
     * @return array
     */
    public function getFormSettingsKeys(FormMapper $formMapper, $object = null)
    {
        $settings = array(
            array('abstract', 'text', array('required' => false,)),
            array('content', 'sonata_formatter_type', function (FormBuilderInterface $formBuilder) {
                return array(
                    'event_dispatcher' => $formBuilder->getEventDispatcher(),
                    'format_field'     => array('format', '[format]'),
                    'source_field'     => array('rawContent', '[rawContent]'),
                    'target_field'     => '[content]',
                );
            }),
        );
        return $settings;
    }

    public function load(PostSetsInterface $object) {}

    public function validate(ErrorElement $errorElement, PostSetsInterface $object){}
}
