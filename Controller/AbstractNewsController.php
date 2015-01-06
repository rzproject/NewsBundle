<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

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


    protected function getFallbackTemplate() {
        $viewTemplate = $this->container->get('rz_admin.template.loader')->getTemplates();
        return $viewTemplate['rz_news.template.view'];
    }

    protected function getAjaxTemplates($template) {
        return array('ajax_template'=>preg_replace('/.html.twig/', '_ajax.html.twig', $template),
            'ajax_pager'=>preg_replace('/.html.twig/', '_ajax_pager.html.twig', $template));
    }

    protected function getAjaxResponse($object, $parameters, $type) {
        //for now reuse the template name TODO:implement on settings
        $template = $object->getSetting('template');
        $templates = $this->getAjaxTemplates($template);
        $templateAjax = $templates['ajax_template'];
        $templatePagerAjax = $templates['ajax_pager'];

        if($template && $this->getTemplating()->exists($template) &&
            $templateAjax && $this->getTemplating()->exists($templateAjax) &&
            $templatePagerAjax && $this->getTemplating()->exists($templatePagerAjax)) {

            $html = $this->container->get('templating')->render($templateAjax, $parameters);
            $html_pager = $this->container->get('templating')->render($templatePagerAjax, $parameters);
            return new JsonResponse(array('html' => $html, 'html_pager'=>$html_pager));

        } else {
            $defaultTemplate = $this->container->get('rz_admin.template.loader')->getTemplates();
            $template = $defaultTemplate[sprintf('rz_news.template.%s_%s', $type,  'html')];
            $templates = $this->getAjaxTemplates($template);
            $templateAjax = $templates['ajax_template'];
            $templatePagerAjax = $templates['ajax_pager'];
            $html = $this->container->get('templating')->render($templateAjax, $parameters);
            $html_pager = $this->container->get('templating')->render($templatePagerAjax, $parameters);
            return new JsonResponse(array('html' => $html, 'html_pager'=>$html_pager));
        }
    }

    protected function buildPostViewNavi($post) {

        $posts = $this->container->get('sonata.news.manager.post')->getAllPostForSingleNavi();

        $next = null;
        $prev = null;

        $total = count($posts);
        $current = $this->searchForSlug($post->getSlug(), $posts)+1;

        if((int) $current == 1 && $total > 1) {
            $next = $posts[$current];
        } elseif((int) $current > 1 && $total == $current) {
            $prev = $posts[$current-2];
        } elseif((int) $current > 1 && $total > $current) {
            $next = $posts[$current];
            $prev = $posts[$current-2];
        }

        return array('next'=>$next, 'prev'=>$prev);
    }

    protected function searchForSlug($slug, $array) {
        foreach ($array as $key => $val) {
            if ($val['slug'] === $slug) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @return \Sonata\NewsBundle\Model\CommentManagerInterface
     */
    protected function getPostHasMediaManager()
    {
        return $this->get('rz_news.manager.post_has_media');
    }
}
