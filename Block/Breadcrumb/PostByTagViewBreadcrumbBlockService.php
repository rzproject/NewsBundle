<?php

namespace Rz\NewsBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\FactoryInterface;


class PostByTagViewBreadcrumbBlockService extends TagListBreadcrumbBlockService
{

    protected $postManager;

    /**
     * @param string $context
     * @param string $name
     * @param EngineInterface $templating
     * @param MenuProviderInterface $menuProvider
     * @param FactoryInterface $factory
     * @param null $postManager
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, $postManager = null)
    {
        parent::__construct($context, $name, $templating, $menuProvider, $factory);
        $this->postManager = $postManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Breadcrumb View: Tag';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = parent::getMenu($blockContext);

        $tag = $blockContext->getBlock()->getSetting('tag');
        $post = $blockContext->getBlock()->getSetting('post');
        $blog = $blockContext->getBlock()->getSetting('blog');

        if ($tag && $post && $blog) {
            $menu->addChild($post->getTitle(), array(
                'route'           => 'rz_news_tag_view',
                'routeParameters' => array(
                    'permalink' => $blog->getPermalinkGenerator()->generate($post, true),
                    'tag' => $tag->getSlug(),
                    '_format' => 'html'
                ),
            ));
        }

        return $menu;
    }
}
