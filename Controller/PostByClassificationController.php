<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NewsController
 * @package Rz\NewsBundle\Controller
 */
class PostByClassificationController extends AbstractNewsController
{

    protected function getCategoryXhrResponse($category, $block, $parameters) {
        $settings = $block->getSettings();
        $ajaxTemplate = isset($settings['ajaxTemplate']) ? $settings['ajaxTemplate'] : 'RzNewsBundle:Block:post_by_category_ajax.html.twig';
        $templatePagerAjax = isset($settings['ajaxPagerTemplate']) ? $settings['ajaxPagerTemplate'] : 'RzNewsBundle:Block:post_by_category_ajax_pager.html.twig';
        $html = $this->container->get('templating')->render($ajaxTemplate, $parameters);
        $html_pager = $this->container->get('templating')->render($templatePagerAjax, $parameters);
        return new JsonResponse(array('html' => $html, 'html_pager'=>$html_pager));
    }

    protected function getCategoryDataForView($category, $block, $page = null, $filter='latest') {

        $parameters = array('category' => $category, 'filter'=>$filter);

        if($page) {
            $parameters['page'] = $page;
        }


        $pager = $this->fetchNews($parameters);

        if ($pager->getNbResults() <= 0) {
            throw new NotFoundHttpException('Invalid URL');
        }

        return $this->buildParameters($pager,
	                                  $this->get('request_stack')->getCurrentRequest(),
	                                  array('category' => $category,
	                                        'block'=>$block,
                                            'filter'=>$filter,
											'is_ajax_pagination'=>$this->container->getParameter('rz_news.settings.ajax_pagination'),
                                            'enable_category_canonical_page'=>$this->container->getParameter('rz_classification.settings.category.enable_category_canonical_page'))
                                      );
    }

    protected function verifyCategory($categoryId) {

        $collection = $this->get('sonata.classification.manager.category')->findOneBy(array(
            'id' => $categoryId,
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


    /**
     * @param Request $request
     * @param $categoryId
     * @param $blockId
     * @param int $page
     * @param string $filter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function postsByCategoryAjaxPagerAction(Request $request, $categoryId, $blockId, $page = 1, $filter ='latest') {

        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Unable to find page');
        }

        if(!$category = $this->verifyCategory($categoryId)) {
            throw new NotFoundHttpException('Unable to find the category');
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
            $parameters = $this->getCategoryDataForView($category, $block, $page, $filter);
        } catch(\Exception $e) {
            throw $e;
        }


        return $this->getCategoryXhrResponse($category, $block, $parameters);
    }

    public function postsByCategoryAjaxFilterAction(Request $request, $categoryId, $blockId, $filter ='latest') {
       return $this->postsByCategoryAjaxPagerAction($request, $categoryId, $blockId, 1, $filter);
    }
}