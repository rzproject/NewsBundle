<?php

namespace Rz\NewsBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\FactoryInterface;


class TagListBreadcrumbBlockService extends BaseBreadcrumbMenuBlockService
{
    /**
     * @param string $context
     * @param string $name
     * @param EngineInterface $templating
     * @param MenuProviderInterface $menuProvider
     * @param FactoryInterface $factory
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory)
    {
        parent::__construct($context, $name, $templating, $menuProvider, $factory);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Breadcrumb List: Tag';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $menu = parent::getRootMenu($blockContext);
        if ($tag = $blockContext->getBlock()->getSetting('tag')) {

            $menu->addChild($tag->getName(), array(
                'route'           => 'rz_news_tag',
                'routeParameters' => array(
                    'tag' => $tag
                ),
            ));
        }
        return $menu;
    }
}
