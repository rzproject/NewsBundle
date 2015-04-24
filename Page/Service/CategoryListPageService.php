<?php

namespace Rz\NewsBundle\Page\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sonata\SeoBundle\Seo\SeoPageInterface;

use Rz\PageBundle\Page\Service\DefaultPageService;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\TemplateManagerInterface;

class CategoryListPageService extends DefaultPageService
{
    /**
     * @var TemplateManagerInterface
     */
    protected $templateManager;

    /**
     * @var SeoPageInterface
     */
    protected $seoPage;

    /**
     * Constructor
     *
     * @param string $name Page service name
     * @param TemplateManagerInterface $templateManager Template manager
     * @param SeoPageInterface $seoPage SEO page object
     * @param null $router
     */
    public function __construct($name, TemplateManagerInterface $templateManager, SeoPageInterface $seoPage = null, $router = null)
    {
        $this->name            = $name;
        $this->templateManager = $templateManager;
        $this->seoPage         = $seoPage;
        $this->router          = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null)
    {
        $this->updateSeoPage($page);
        $response = $this->templateManager->renderResponse($page->getTemplateCode(), $parameters, $response);
        return $response;
    }

    /**
     * Updates the SEO page values for given page instance
     *
     * @param PageInterface $page
     */
    protected function updateSeoPage(PageInterface $page)
    {
        if (!$this->seoPage) {
            return;
        }

        if ($page->getTitle()) {
            $this->seoPage->setTitle($page->getTitle() ?: $page->getName());
        }

        if ($page->getMetaDescription()) {
            $this->seoPage->addMeta('name', 'description', $page->getMetaDescription());
        }

        if ($page->getMetaKeyword()) {
            $this->seoPage->addMeta('name', 'keywords', $page->getMetaKeyword());
        }

        if($page->getOgTitle()) {
            $this->seoPage->addMeta('property', 'og:title', $page->getOgTitle());
        }

        $this->seoPage->addMeta('property', 'og:type', $page->getOgType() ? $page->getOgType(): 'article');

        if($page->isCms()) {
            $this->seoPage->addMeta('property', 'og:url',  $this->router->generate($page, array(), true));
        }


        if($page->getOgDescription()) {
            $this->seoPage->addMeta('property', 'og:description', $page->getOgDescription());
        }

        $this->seoPage->addHtmlAttributes('prefix', 'og: http://ogp.me/ns#');

        $this->seoPage->setLinkCanonical($this->router->generate('rz_news_archive', array(), true));
    }
}
