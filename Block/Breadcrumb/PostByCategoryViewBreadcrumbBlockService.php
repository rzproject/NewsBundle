<?php

namespace Rz\NewsBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\FactoryInterface;


class PostByCategoryViewBreadcrumbBlockService extends CategoryListBreadcrumbBlockService
{
    protected $postManager;

    protected $isEnabledController;

    /**
     * @param string $context
     * @param string $name
     * @param EngineInterface $templating
     * @param MenuProviderInterface $menuProvider
     * @param FactoryInterface $factory
     * @param null $categoryManager
     * @param null $postManager
     * @param bool $isEnabledController
     */
    public function __construct($context,
                                $name,
                                EngineInterface $templating,
                                MenuProviderInterface $menuProvider,
                                FactoryInterface $factory,
                                $categoryManager = null,
                                $postManager = null,
                                $isEnabledController = true)
    {
        parent::__construct($context, $name, $templating, $menuProvider, $factory, $categoryManager);
        $this->isEnabledController = $isEnabledController;
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
        if ($this->isEnabledController) {
            $menu = parent::getMenu($blockContext);
        } else {
            $menu = parent::getRootMenu($blockContext);

            if ($category = $blockContext->getBlock()->getSetting('category')) {
                $this->addMenu($category, $menu);
                while($this->menuData != null || $this->menuData != array()) {
                    $cat = array_pop($this->menuData);

                    if($page = $cat->getPage()) {
                        $menu->addChild($cat->getName(), array(
                            'route'           => 'page_slug',
                            'routeParameters' => array(
                                'path' => $page->getUrl()
                            ),
                        ));
                    } else {
                        $menu->addChild($cat->getName(), array('uri' => '#'));
                    }
                }
            }
        }

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
