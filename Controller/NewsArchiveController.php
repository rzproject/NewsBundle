<?php

namespace Rz\NewsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class NewsArchiveController
 * @package Rz\NewsBundle\Controller
 */
class NewsArchiveController extends AbstractNewsController
{

    /**
     * @internal param int $page
     * @return Response
     */
    public function archiveAction()
    {
        $pager = $this->fetchNews(array());
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest()));
    }

    /**
     * @param int $page
     * @return Response
     */
    public function archivePagerAction($page = 1)
    {
        $pager = $this->fetchNews(array('page' => $page));
        return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest()));
    }

    /**
     * @param string $year
     * @param string $month
     *
     * @return Response
     */
    public function archiveMonthlyAction($year, $month)
    {
        if (preg_match('/^\d{4}$/', $year) && preg_match('/^\d{2}$/', $month)) {
            $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, $month, 1), 'month')));
            return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'monthly')));
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    /**
     *
     */
    public function archiveMonthlyPagerAction($page, $year, $month)
    {
        if (preg_match('/^\d{4}$/', $year) && preg_match('/^\d{2}$/', $month)) {
            $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, $month, 1), 'month'), 'page' => $page));
            return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'monthly')));
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    /**
     * @param string $year
     *
     * @return Response
     */
    public function archiveYearlyAction($year)
    {
        if (preg_match('/^\d{4}$/', $year)) {
            $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, 1, 1), 'year')));
            return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'yearly')));
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    /**
     * @param $page
     * @param string $year
     *
     * @return Response
     */
    public function archiveYearlyPagerAction($page, $year)
    {
        if (preg_match('/^\d{4}$/', $year)) {
            $pager = $this->fetchNews(array('date' => $this->getPostManager()->fetchPublicationDateQueryParts(sprintf('%d-%d-%d', $year, 1, 1), 'year'), 'page' => $page));
            return $this->renderNewsArchive($this->buildParameters($pager, $this->get('request_stack')->getCurrentRequest(), array('type' => 'yearly')));
        } else {
            throw new NotFoundHttpException('Invalid URL');
        }
    }

    /**
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderNewsArchive(array $parameters = array())
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $template = $this->container->get('rz_admin.template.loader')->getTemplates();
        $response = $this->render($template[sprintf('rz_news.template.archive_%s', $request->getRequestFormat())], array_merge($parameters, array('is_controller_enabled' => $this->container->getParameter('rz_classification.enable_controllers'))));
        if ('rss' === $request->getRequestFormat()) {
            $response->headers->set('Content-Type', 'application/rss+xml');
        }
        return $response;
    }
}
