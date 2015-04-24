<?php

namespace Rz\NewsBundle\Block\Breadcrumb;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;

abstract class BaseCategoryBreadcrumbBlockService extends BaseBreadcrumbMenuBlockService
{
    /**
     * {@inheritdoc}
     */
    protected function getRootMenu(BlockContextInterface $blockContext)
    {
        $menu = parent::getRootMenu($blockContext);

        $menu->addChild('rz_news_category_view', array(
            'route'  => 'rz_news_category_view',
            'extras' => array('translation_domain' => 'RzNewsBundle')
        ));

        return $menu;
    }
}
