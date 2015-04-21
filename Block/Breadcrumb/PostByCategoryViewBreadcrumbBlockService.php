<?php

namespace Rz\NewsBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\FactoryInterface;


class PostByCategoryViewBreadcrumbBlockService extends CategoryListBreadcrumbBlockService
{
    protected $postManager;

    /**
     * @param string $context
     * @param string $name
     * @param EngineInterface $templating
     * @param MenuProviderInterface $menuProvider
     * @param FactoryInterface $factory
     * @param null $categoryManager
     * @param null $postManager
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, $categoryManager = null, $postManager = null)
    {
        parent::__construct($context, $name, $templating, $menuProvider, $factory, $categoryManager);
        $this->postManager = $postManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Breadcrumb View: Post';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = parent::getMenu($blockContext);
        $category = $blockContext->getBlock()->getSetting('category');
        $post = $blockContext->getBlock()->getSetting('post');
        $blog = $blockContext->getBlock()->getSetting('blog');
        if ($category && $post && $blog) {
            $menu->addChild($post->getTitle(), array(
                'route'           => 'rz_news_category_view',
                'routeParameters' => array(
                    'permalink' => $blog->getPermalinkGenerator()->generate($post, true),
                    'category' => $this->categoryManager->getPermalinkGenerator()->generate($category),
                    '_format' => 'html'
                ),
            ));
        }
        return $menu;
    }

    /**
     * @return null
     */
    public function getPostManager()
    {
        return $this->postManager;
    }

    /**
     * @param null $postManager
     */
    public function setPostManager($postManager)
    {
        $this->postManager = $postManager;
    }
}
