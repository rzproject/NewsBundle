<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    public function collectionAction($collection)
    {
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
    public function collectionPagerAction($collection, $page)
    {
        if(!$collection = $this->verifyCollection($collection)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        return $this->renderCollectionList($collection, $page);
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

        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        return $this->render($template['rz_news.template.view'], array(
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
        $parameters = $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('collection' => $collection));

        return $this->renderNewsList($parameters, self::NEWS_LIST_TYPE_COLLECTION);
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