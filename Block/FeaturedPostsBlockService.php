<?php

namespace Rz\NewsBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\CoreBundle\Entity\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\NewsBundle\Model\PostInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\Collections\ArrayCollection;


class FeaturedPostsBlockService extends BaseBlockService
{
    protected $manager;
    protected $postAdmin;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param ContainerInterface $container
     * @param \Rz\NewsBundle\Block\PostManagerInterface|\Sonata\CoreBundle\Entity\ManagerInterface $manager
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container, ManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->container      = $container;
        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $posts = $blockContext->getBlock()->getSetting('postId');

        $parameters = array(
            'posts'      => $posts,
            'context'   => $blockContext,
            'settings'  => $blockContext->getSettings(),
            'block'     => $blockContext->getBlock(),
        );

        if ($blockContext->getSetting('mode') === 'admin') {
            return $this->renderPrivateResponse($blockContext->getTemplate(), $parameters, $response);
        }

        return $this->renderResponse($blockContext->getTemplate(), $parameters, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        // simulate an association ...
        $fieldDescription = $this->getPostAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->getPostAdmin()->getClass(), 'post' );
        $fieldDescription->setAssociationAdmin($this->getPostAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(array('fieldName' => 'post',
                                                       'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY,
                                                       'targetEntity' => $this->getPostAdmin()->getClass(),
                                                       'cascade'       => array(
                                                           0 => 'persist',
                                                       )));


        // TODO: add label on config
        $builder = $formMapper->create('postId', 'sonata_type_model', array(
                                                      'sonata_field_description' => $fieldDescription,
                                                      'class'             => $this->getPostAdmin()->getClass(),
                                                      'model_manager'     => $this->getPostAdmin()->getModelManager(),
                                                      'label'             => 'Posts',
                                                      'by_reference' => false,
                                                      'multiple' => true,
                                                      'select2'=>true,
                                                      'btn_add' => false
                                                  ));

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
                                       'keys' => array(
                                           array($builder, null, array('attr'=>array('class'=>'span8'))),
                                           array('number', 'integer', array('required' => true)),
                                           array('title', 'text', array('required' => false)),
                                           array('mode', 'choice', array(
                                               'choices' => array(
                                                   'public' => 'public',
                                                   'admin'  => 'admin'
                                                )
                                            )),
                                            array('block_type', 'choice', array(
                                                'choices' => array(
                                                    'sidebar'  => 'sidebar',
                                                    'content' => 'content'
                                                )
                                            ))
                                       )
                                   ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Featured Posts';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                                   'posts'   => false,
                                   'number'     => 5,
                                   'mode'       => 'public',
                                   'title'      => 'Featured Posts',
                                   'block_type' => 'content',
                                   'template'   => 'SonataNewsBundle:Block:featured_posts.html.twig',
                                   'postId' => array()
                               ));
    }

    /**
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getPostAdmin()
    {
        if (!$this->postAdmin) {
            $this->postAdmin = $this->container->get('sonata.news.admin.post');
        }

        return $this->postAdmin;
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $posts = $block->getSetting('postId');

        if ($posts) {
            $post_temp = new ArrayCollection();

            foreach($posts as $post) {
                if ($post) {
                    $post_temp->add($this->manager->findOneBy(array('id' => $post)));
                }
            }

            $block->setSetting('postId', $post_temp);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block)
    {
        if ($block->getSetting('postId') instanceof ArrayCollection) {
            $post_temp = array();
            foreach($block->getSetting('postId') as $post) {
                array_push($post_temp, $post->getId());
            }
            $block->setSetting('postId', $post_temp);
        } else {
            $block->setSetting('postId', null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block)
    {
        if ($block->getSetting('postId') instanceof ArrayCollection) {
            $post_temp = array();
            foreach($block->getSetting('postId') as $post) {
                array_push($post_temp, $post->getId());
            }
            $block->setSetting('postId', $post_temp);
        } else {
            $block->setSetting('postId', null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            '/bundles/rznews/css/featured_post_block.css'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascripts($media)
    {
        return array('/bundles/rmzamorajquery/jquery-plugins/eqheight/jquery.eqheight.js');
    }
}
