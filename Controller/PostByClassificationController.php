<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Rz\NewsBundle\Controller\AbstractNewsController;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
class PostByClassificationController extends AbstractNewsController
{

    /**
     * @param Request $request
     * @param $collectionId
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @internal param $collection
     */
    public function postsByCollectionAjaxPagerAction(Request $request, $collectionId, $blockId, $page = 1) {

        if(!$collection = $this->verifyCollection($collectionId)) {
            throw new NotFoundHttpException('Unable to find the collection');
        }

        if(!$block = $this->verifyBlock($blockId)) {
            throw new NotFoundHttpException('Unable to find the block');
        }


        //redirect to normal controller if not ajax
        if (!$this->get('request_stack')->getCurrentRequest()->isXmlHttpRequest()) {
            //TODO implement central pager for SEO purposes
            //return $this->redirect($this->generateUrl('rz_news_collection_pager', array('collection'=>$collection->getSlug(), 'page'=>$page)), 301);
        }

        try {
            $parameters = $this->getCollectionDataForView($collection, $block, $page);
        } catch(\Exception $e) {
            throw $e;
        }

        return $this->getXHRResponse($collection, $block, $parameters);
    }

    protected function getXhrResponse($collection, $block, $parameters) {
        $settings = $block->getSettings();
        $ajaxTemplate = isset($settings['ajaxTemplate']) ? $settings['ajaxTemplate'] : 'RzNewsBundle:Block:post_by_collection_ajax.html.twig';


        $templatePagerAjax = 'RzNewsBundle:Post:collection_list_default_ajax_pager.html.twig';
        $html = $this->container->get('templating')->render($ajaxTemplate, $parameters);
        $html_pager = $this->container->get('templating')->render($templatePagerAjax, $parameters);
        return new JsonResponse(array('html' => $html, 'html_pager'=>$html_pager));
    }

    protected function getCollectionDataForView($collection, $block, $page = null) {

        $parameters = array('collection' => $collection);

        if($page) {
            $parameters['page'] = $page;
        }

        $pager = $this->fetchNews($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        return $this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('collection' => $collection, 'block'=>$block));
    }

    protected function verifyCollection($collectionId) {

        $collection = $this->get('sonata.classification.manager.collection')->findOneBy(array(
            'id' => $collectionId,
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

    protected function verifyBlock($blockId) {

        $block = $this->get('sonata.page.manager.block')->findOneBy(array(
            'id' => $blockId,
        ));

        if (!$block) {
            return false;
        }

        if (!$block->getEnabled()) {
            return false;
        }

        return $block;
    }
}