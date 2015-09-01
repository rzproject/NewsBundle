<?php

namespace Rz\NewsBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\FactoryInterface;


class CategoryListBreadcrumbBlockService extends BaseBreadcrumbMenuBlockService
{
    protected $categoryManager;
    protected $menuData;

    /**
     * @param string $context
     * @param string $name
     * @param EngineInterface $templating
     * @param MenuProviderInterface $menuProvider
     * @param FactoryInterface $factory
     * @param null $categoryManager
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, $categoryManager = null)
    {
        parent::__construct($context, $name, $templating, $menuProvider, $factory);
        $this->categoryManager = $categoryManager;
        $this->menuData = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Breadcrumb List: Category';
    }

    protected function addMenu($category) {

        if ($category && $category->getSlug() == 'news') {
            return;
        }

        array_push($this->menuData, $category);

        if ($category->getParent() && $category->getParent()->getSlug() != 'news') {
            $this->addMenu($category->getParent());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = parent::getRootMenu($blockContext);

        if ($category = $blockContext->getBlock()->getSetting('category')) {
            $this->addMenu($category, $menu);
            while($this->menuData != null || $this->menuData != array()) {
                $cat = array_pop($this->menuData);
                $menu->addChild($cat->getName(), array(
                    'route'           => 'rz_news_category',
                    'routeParameters' => array(
                        'permalink' => $this->categoryManager->getPermalinkGenerator()->generate($cat)
                    ),
                ));
            }
        }

        return $menu;
    }

    /**
     * @return mixed
     */
    public function getCategoryManager()
    {
        return $this->categoryManager;
    }

    /**
     * @param mixed $categoryManager
     */
    public function setCategoryManager($categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }


}
