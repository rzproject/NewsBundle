<?php

namespace Rz\NewsBundle\Provider;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\MediaBundle\Model\GalleryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sonata\NewsBundle\Model\PostInterface;

class DefaultProvider extends BaseProvider
{

    protected $mediaAdmin;
    protected $mediaManager;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

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
            ->tab('Settings')
                ->with('rz_news_settings', array('class' => 'col-md-8',))
                    ->add('settings', 'sonata_type_immutable_array', array('keys' => $this->getFormSettingsKeys($formMapper, $object), 'required'=>false, 'label'=>'form.label_settings'))
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
            array('seoTitle', 'text', array('required' => false)),
            array('seoMetaKeyword', 'textarea', array('required' => false, 'attr'=>array('rows'=>5))),
            array('seoMetaDescription', 'textarea', array('required' => false, 'attr'=>array('rows'=>5))),
            array('ogTitle', 'text', array('required' => false, 'attr'=>array('class'=>'span8'))),
            array('ogType', 'choice', array('choices'=>$this->getMetatagChoices(), 'attr'=>array('class'=>'span4'))),
            array('ogDescription', 'textarea', array('required' => false, 'attr'=>array('class'=>'span8', 'rows'=>5))),
        );

        if (interface_exists('Sonata\MediaBundle\Model\MediaInterface')) {
            array_push($settings, array($this->getMediaBuilder($formMapper), null, array()));
        }
        return $settings;
    }

    protected function getMediaBuilder(FormMapper $formMapper) {

        $mediaAdmin = clone $this->mediaAdmin;
        // simulate an association media...
        $fieldDescription =  $mediaAdmin->getModelManager()->getNewFieldDescriptionInstance($mediaAdmin->getClass(), 'media');
        $fieldDescription->setAssociationAdmin($mediaAdmin);
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setOptions(array('link_parameters' => array('context' => 'news', 'hide_context' => true)));
        $fieldDescription->setAssociationMapping(array(
            'fieldName' => 'media',
            'type'      => \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE
        ));

        return $formMapper->create('ogImage2', 'sonata_type_model_list', array(
            'sonata_field_description' => $fieldDescription,
            'class'                    => $mediaAdmin->getClass(),
            'model_manager'            => $mediaAdmin->getModelManager()),
            array('link_parameters' => array('context' => 'news', 'hide_context' => true))
        );
    }

    public function load(PostInterface $post) {
        if (interface_exists('Sonata\MediaBundle\Model\MediaInterface')) {
            //load media
            $media = $post->getSetting('ogImage', null);
            if (is_int($media)) {
                $media = $this->mediaManager->findOneBy(array('id' => $media));
            }
            $post->setSetting('ogImage', $media);
        }
    }

    public function getOptions() {

    }

    /**
     * @return mixed
     */
    public function getMediaAdmin()
    {
        return $this->mediaAdmin;
    }

    /**
     * @param mixed $mediaAdmin
     */
    public function setMediaAdmin($mediaAdmin)
    {
        $this->mediaAdmin = $mediaAdmin;
    }

    /**
     * @return mixed
     */
    public function getMediaManager()
    {
        return $this->mediaManager;
    }

    /**
     * @param mixed $mediaManager
     */
    public function setMediaManager($mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(PostInterface $post)
    {
        $post->setSetting('ogImage', is_object($post->getSetting('ogImage')) ? $post->getSetting('ogImage')->getId() : null);
        parent::postPersist($post);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostInterface $post)
    {
        $post->setSetting('ogImage', is_object($post->getSetting('ogImage')) ? $post->getSetting('ogImage')->getId() : null);
        parent::postUpdate($post);
    }
}
