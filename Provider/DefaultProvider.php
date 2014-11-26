<?php

namespace Rz\NewsBundle\Provider;


use Sonata\NewsBundle\Model\PostInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

class DefaultProvider extends BaseProvider
{

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
    public function buildEditForm(FormMapper $formMapper)
    {
       $this->buildCreateForm($formMapper);
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper
            ->with('Settings')
                ->add('settings', 'sonata_type_immutable_array', array('keys' => $this->getFormSettingsKeys()))
            ->end();
    }

    /**
     * @return array
     */
    public function getFormSettingsKeys()
    {
        return array(
            array('template', 'choice', array('choices'=>$this->getTemplateChoices())),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(PostInterface $post)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostInterface $post)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostInterface $post)
    {
    }
}
