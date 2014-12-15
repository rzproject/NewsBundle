<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
abstract class AbstractNewsController extends Controller
{
    /**
     * @return \Sonata\ClassificationBundle\Model\CategoryManagerInterface
     */
    protected function getCategoryManager()
    {
        return $this->get('sonata.classification.manager.category');
    }

    /**
     * @return \Sonata\ClassificationBundle\Model\CollectionManagerInterface
     */
    protected function getCollectionManager()
    {
        return $this->get('sonata.classification.manager.collection');
    }

    /**
     * @return \Sonata\NewsBundle\Model\PostManagerInterface
     */
    protected function getPostManager()
    {
        return $this->get('sonata.news.manager.post');
    }

    /**
     * @return \Sonata\NewsBundle\Model\CommentManagerInterface
     */
    protected function getCommentManager()
    {
        return $this->get('sonata.news.manager.comment');
    }

    /**
     * @return \Sonata\NewsBundle\Model\CommentManagerInterface
     */
    protected function getCollectionPool()
    {
        return $this->get('rz_classification.pool.collection');
    }

    /**
     * @return \Sonata\NewsBundle\Model\CommentManagerInterface
     */
    protected function getNewsPool()
    {
        return $this->get('rz_news.pool');
    }

    /**
     * @return \Sonata\NewsBundle\Model\BlogInterface
     */
    protected function getBlog()
    {
        return $this->container->get('sonata.news.blog');
    }

    protected function fetchNews(array $criteria = array()) {

        if(array_key_exists('page', $criteria)) {
            $page = $criteria['page'];
            unset($criteria['page']);
        } else {
            $page = 1;
        }

        $pager = $this->getPostManager()->getNewsPager($criteria);
        $pager->setMaxPerPage($this->container->hasParameter('rz_news.settings.news_pager_max_per_page')?$this->container->getParameter('rz_news.settings.news_pager_max_per_page'): 5);
        $pager->setCurrentPage($page, false, true);
        return $pager;
    }

    protected function buildParameters($pager, $request, $parameters = array()) {

        return array_merge(array(
                'pager' => $pager,
                'blog'  => $this->get('sonata.news.blog'),
                'tag'   => false,
                'route' => $request->get('_route'),
                'route_parameters' => $request->get('_route_params'),
                'type'  => 'none')
            ,$parameters);
    }

    /**
     * @return \Sonata\SeoBundle\Seo\SeoPageInterface
     */
    public function getSeoPage()
    {
        if ($this->has('sonata.seo.page')) {
            return $this->get('sonata.seo.page');
        }

        return null;
    }

    protected function renderNewsList($parameters, $type) {

        $request = $this->get('request_stack')->getCurrentRequest();

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        $response = $this->render($template[sprintf('rz_news.template.%s_%s', $type,  $request->getRequestFormat())], $parameters);
        if ('rss' === $request->getRequestFormat()) {
            $response->headers->set('Content-Type', 'application/rss+xml');
        }
        return $response;
    }

    public function getTemplating() {
        return $this->container->get('templating');
    }

}
