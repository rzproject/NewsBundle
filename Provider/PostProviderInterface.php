<?php
namespace Rz\NewsBundle\Provider;

use Sonata\CoreBundle\Model\MetadataInterface;
use Sonata\NewsBundle\Model\PostInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\Form\FormBuilder;

interface PostProviderInterface
{
    /**
     *
     * @param PostInterface $post
     *
     * @return void
     */
    public function preUpdate(PostInterface $post);

    /**
     *
     * @param PostInterface $post
     *
     * @return void
     */
    public function postUpdate(PostInterface $post);

    /**
     * @param PostInterface $post
     *
     * @return void
     */
    public function preRemove(PostInterface $post);

    /**
     * @param PostInterface $post
     *
     * @return void
     */
    public function postRemove(PostInterface $post);

    /**
     * build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function buildCreateForm(FormMapper $formMapper);

    /**
     * build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function buildEditForm(FormMapper $formMapper);

    /**
     * @param PostInterface $post
     *
     * @return void
     */
    public function prePersist(PostInterface $post);

    /**
     *
     * @param PostInterface $post
     *
     * @return void
     */
    public function postPersist(PostInterface $post);

    /**
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     *
     * @param array $templates
     */
    public function setTemplates(array $templates);

    /**
     *
     * @return array
     */
    public function getTemplates();

    /**
     * @param string $name
     *
     * @return string
     */
    public function getTemplate($name);

    /**
     * @param ErrorElement   $errorElement
     * @param PostInterface $post
     */
    public function validate(ErrorElement $errorElement, PostInterface $post);

    /**
     * @return array
     */
    public function getFormSettingsKeys(FormMapper $formMapper);

    public function load(PostInterface $post);
}
