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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function collectionAction($collection){
        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        try {
            $response = $this->renderCollectionList($collection);
        } catch (\Exception $e) {
            throw $e;
        }

        return $response;
    }


    /**
     * @param $collection
     *
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function collectionPagerAction($collection, $page) {
        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        try {
            $response = $this->renderCollectionList($collection, $page);
        } catch (\Exception $e) {
            throw $e;
        }

        return $response;
    }

    public function collectionAjaxPagerAction($collection, $page) {

        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        //redirect to normal controller if not ajax
        if (!$this->get('request_stack')->getCurrentRequest()->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('rz_news_collection_pager', array('collection'=>$collection->getSlug(), 'page'=>$page)), 301);
        }

        try {
            $parameters = $this->getCollectionDataForView($collection, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        return $this->getAjaxResponse($collection, $parameters, self::NEWS_LIST_TYPE_COLLECTION);
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


            $seoPage->setTitle($post->getSetting('seoTitle', null) ? $post->getSetting('seoTitle', null) : $post->getTitle());
            $seoPage->addMeta('name', 'description', $post->getSetting('seoMetaDescription', null)? $post->getSetting('seoMetaDescription', null) : $post->getAbstract());
            if($post->getSetting('seoMetaKeyword', null)) {
                $seoPage->addMeta('name', 'keywords', $post->getSetting('seoMetaKeyword', null));
            }
            $seoPage->addMeta('property', 'og:title', $post->getSetting('ogTitle', null) ? $post->getSetting('ogTitle', null) : $post->getTitle());
            $seoPage->addMeta('property', 'og:type', $post->getSetting('ogType', null) ? $post->getSetting('ogType', null): 'Article');
            $seoPage->addMeta('property', 'og:url',  $this->generateUrl('rz_news_collection_view', array(
                'collection'  => $collection->getSlug(),
                'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true),
                '_format' => $request->getRequestFormat()
            ), true));
            $seoPage->addMeta('property', 'og:description', $post->getSetting('ogDescription', null) ? $post->getSetting('ogDescription', null) : $post->getAbstract());
            $seoPage->setLinkCanonical($this->generateUrl('rz_news_view', array(
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

        try {
            $parameters = $this->getCollectionDataForView($collection, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        $template = $collection->getSetting('template');

        if ($seoPage = $this->getSeoPage()) {
            $request = $this->get('request_stack')->getCurrentRequest();

            if($collection->getSetting('seoTitle', null)) {
                $seoPage->setTitle($collection->getSetting('seoTitle', null));
            }

            if($collection->getSetting('seoMetaDescription', null)) {
                $seoPage->addMeta('name', 'description', $collection->getSetting('seoMetaDescription', null));
            }

            if($collection->getSetting('seoMetaKeyword', null)) {
                $seoPage->addMeta('name', 'keywords', $collection->getSetting('seoMetaKeyword', null));
            }

            if($collection->getSetting('ogTitle', null)) {
                $seoPage->addMeta('property', 'og:title', $collection->getSetting('ogTitle', null));
            }

            $seoPage->addMeta('property', 'og:type', $collection->getSetting('ogType', null) ? $collection->getSetting('ogType', null): 'Article');

            $seoPage->addMeta('property', 'og:url',  $this->generateUrl('rz_news_collection', array(
                'collection'  => $collection->getSlug(),
                '_format' => $request->getRequestFormat()
            ), true));

            if($collection->getSetting('ogDescription', null)) {
                $seoPage->addMeta('property', 'og:description', $collection->getSetting('ogDescription', null));
            }
        }

        if($template && $this->getTemplating()->exists($template) ) {
            return $this->render($template, $parameters);
        } else {
            return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_COLLECTION);
        }
    }

    protected function getCollectionDataForView($collection, $page = null) {
        $parameters = array('collection' => $collection);
        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNewsNative($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        return $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('collection' => $collection, 'is_ajax_pagination'=>$this->container->getParameter('rz_news.settings.ajax_pagination')));
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
}