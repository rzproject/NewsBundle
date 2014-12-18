<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
class NewsCollectionController extends AbstractNewsController
{

    const NEWS_LIST_TYPE_COLLECTION = 'collection';

    /**
     * @param $collection
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function collectionAction($collection){
        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        return $this->renderCollectionList($collection);
    }


    /**
     * @param $page
     * @param $collection
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     *
     */
    public function collectionPagerAction($collection, $page) {
        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        return $this->renderCollectionList($collection, $page);
    }

    public function collectionAjaxPagerAction($collection, $page) {

        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        //redirect to normal controller if not ajax
        if (!$this->get('request_stack')->getCurrentRequest()->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('rz_news_collection_pager', array('collection'=>$collection->getSlug(), 'page'=>$page)), 301);
        }

        $parameters = array('collection' => $collection);
        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNews($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        $parameters = $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('collection' => $collection));

        //for now reuse the template name TODO:implement on settings
        $template = $collection->getSetting('template');
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
            $template = $defaultTemplate[sprintf('rz_news.template.%s_%s', self::NEWS_LIST_TYPE_COLLECTION,  'html')];
            $templates = $this->getAjaxTemplates($template);
            $templateAjax = $templates['ajax_template'];
            $templatePagerAjax = $templates['ajax_pager'];
            $html = $this->container->get('templating')->render($templateAjax, $parameters);
            $html_pager = $this->container->get('templating')->render($templatePagerAjax, $parameters);
            return new JsonResponse(array('html' => $html, 'html_pager'=>$html_pager));
        }
    }

    /**
     *
     * @param $collection
     * @param $permalink
     *
     *
     * @throws \Exception
     * @return Response
     */
    public function collectionViewAction($collection, $permalink)
    {

        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        if ($post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('sonata.news.blog'))) {
            try {
                return $this->renderCollectionView($post, $collection);
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    protected function renderCollectionView($post, $collection) {

        if (!$post || !$post->isPublic()) {
            throw new NotFoundHttpException('Unable to find the post');
        }

        if ($seoPage = $this->getSeoPage()) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $seoPage
                ->setTitle($post->getTitle())
                ->addMeta('name', 'description', $post->getAbstract())
                ->addMeta('property', 'og:title', $post->getTitle())
                ->addMeta('property', 'og:type', 'blog')
                ->addMeta('property', 'og:url',  $this->generateUrl('rz_news_collection_view', array(
                    'collection'  => $collection->getSlug(),
                    'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                    '_format' => $request->getRequestFormat()
                ), true))
                ->addMeta('property', 'og:description', $post->getAbstract())
                ->setLinkCanonical($this->generateUrl('rz_news_view', array(
                    'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                    '_format' => $request->getRequestFormat()
                ), true))
            ;
        }

        //set default template
        $template = $this->getFallbackTemplate();


        $viewTemplate = $post->getSetting('template');
        if($viewTemplate) {
            if ($this->getTemplating()->exists($template)) {
                $template = $viewTemplate;
            } else {
                //get generic template
                $pool = $this->getNewsPool();
                $defaultTemplateName = $pool->getDefaultTemplateNameByCollection($pool->getDefaultDefaultCollection());
                $defaultViewTemplate = $pool->getTemplateByCollection($defaultTemplateName);

                if($defaultViewTemplate) {
                    $template = $viewTemplate['path'];
                }
            }
        }

        return $this->render($template, array(
            'post' => $post,
            'form' => false,
            'blog' => $this->get('sonata.news.blog')
        ));
    }

    protected function renderCollectionList($collection, $page = null) {

        $parameters = array('collection' => $collection);
        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNews($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        $parameters = $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('collection' => $collection, 'is_ajax_pagination'=>$this->container->getParameter('rz_news.settings.ajax_pagination')));

        $template = $collection->getSetting('template');

        if($template && $this->getTemplating()->exists($template) ) {
            return $this->render($template, $parameters);
        } else {
            return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_COLLECTION);
        }
    }

    protected function verifyCollection($collection) {
        $collection = $this->get('sonata.classification.manager.collection')->findOneBy(array(
            'slug' => $collection,
            'enabled' => true
        ));

        if (!$collection) {
            return false;
        }

        if (!$collection->getEnabled()) {
            return false;
        }

        return $collection;
    }

    protected function getAjaxTemplates($template) {
        return array('ajax_template'=>preg_replace('/.html.twig/', '_ajax.html.twig', $template),
                     'ajax_pager'=>preg_replace('/.html.twig/', '_ajax_pager.html.twig', $template));
    }
}