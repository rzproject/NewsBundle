<?php

namespace Rz\NewsBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

class PostAdminController extends CRUDController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request = null)
    {

        $collectiontManager = $this->get('sonata.classification.manager.collection');

        $currentCollection = null;

        if ($collection = $request->get('collection')) {
            $currentCollection = $collectiontManager->findOneBy(array('slug'=>$collection));
        } else {
            $currentCollection = $collectiontManager->findOneBy(array('slug'=>'article'));
        }
        $contextManager = $this->get('sonata.classification.manager.context');
        $context = $contextManager->find('news');
        $collections = $collectiontManager->findBy(array('context'=>$context));

        if (!$currentCollection) {
            $currentCollection = array_shift($collections);
        }

        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if ($preResponse !== null) {
            return $preResponse;
        }

        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();

        if ($this->admin->getPersistentParameter('collection')) {
            $collection = $collectiontManager->findOneBy(array('slug'=>$this->admin->getPersistentParameter('collection')));
            $datagrid->setValue('collection', null, $collection->getId());
        } else {
            $datagrid->setValue('collection', null, $currentCollection->getId());
        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), array(
            'action'           => 'list',
            'current_collection'  => $currentCollection,
            'collections'         => $collections,
            'form'             => $formView,
            'datagrid'         => $datagrid,
            'csrf_token'       => $this->getCsrfToken('sonata.batch'),
        ), null, $request);
    }
}
