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
        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if ($preResponse !== null) {
            return $preResponse;
        }

        if ($listMode = $request->get('_list_mode', 'mosaic')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();


        $collectiontManager = $this->get('sonata.classification.manager.collection');
        $slugify = $this->get($this->container->getParameter('rz.news.slugify_service'));

        $contextManager = $this->get('sonata.classification.manager.context');
        $defaultContext = $this->container->getParameter('rz.news.post.default_context');
        $context = $contextManager->findOneBy(array('id'=>$slugify->slugify($defaultContext)));

        if (!$context && !$context instanceof \Sonata\ClassificationBundle\Model\ContextInterface) {
            $context = $contextManager->generateDefaultContext($defaultContext);
        }

        $currentCollection = null;
        $defaultCollection = $this->container->getParameter('rz.news.post.default_collection');


        if ($collection = $request->get('collection')) {
            $currentCollection = $collectiontManager->findOneBy(array('slug'=>$slugify->slugify($collection), 'context'=>$context));
        } else {
            $currentCollection = $collectiontManager->findOneBy(array('slug'=>$slugify->slugify($defaultCollection), 'context'=>$context));
        }

        $collections = $collectiontManager->findBy(array('context'=>$context));

        if (!$currentCollection &&
            !$currentCollection instanceof \Sonata\ClassificationBundle\Model\CollectionInterface &&
            count($collections) === 0) {
            $currentCollection = $collectiontManager->generateDefaultCollection($context, $defaultCollection);
            $collections = $collectiontManager->findBy(array('context'=>$context));
        }

        if (count($collections)>0) {
            if (!$currentCollection) {
                list($currentCollection) = $collections;
            }

            if ($this->admin->getPersistentParameter('collection')) {
                $collection = $collectiontManager->findOneBy(array('context'=>$context, 'slug'=>$this->admin->getPersistentParameter('collection')));
                if ($collection && $collection instanceof \Sonata\ClassificationBundle\Model\CollectionInterface) {
                    $datagrid->setValue('collection', null, $collection->getId());
                } else {
                    throw $this->createNotFoundException($this->get('translator')->trans('page_not_found', array(), 'SonataAdminBundle'));
                }
            } else {
                $datagrid->setValue('collection', null, $currentCollection->getId());
            }
        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), array(
            'action'              => 'list',
            'current_collection'  => $currentCollection,
            'collections'         => $collections,
            'form'                => $formView,
            'datagrid'            => $datagrid,
            'csrf_token'          => $this->getCsrfToken('sonata.batch'),
        ), null, $request);
    }


    public function cloneAction(Request $request = null)
    {
        $request = $this->getRequest();
        $templateKey = 'edit';

        $id     = $request->get($this->admin->getIdParameter());
        $target = $this->admin->getObject($id);

        if (!$target) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        // Be careful, you may need to overload the __clone method of your object
        // to set its id to null !
        $object = clone $target;

        $object->setTitle($object->getTitle(). ' (Clone)');

        $preResponse = $this->preCreate($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //TODO: remove this check for 4.0
            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($object);
            }
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {
                $this->admin->checkAccess('create', $object);

                try {
                    $object = $this->admin->create($object);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result'   => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->admin->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->admin->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form'   => $view,
            'object' => $object,
        ), null);
//
//        $clonedObject->setName($object->getName().' (Clone)');
//
//        $this->admin->create($clonedObject);
//
//        $this->addFlash('sonata_flash_success', 'Cloned successfully');
//
//        return new RedirectResponse($this->admin->generateUrl('list'));
//
//        // if you have a filtered list and want to keep your filters after the redirect
//        // return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }
}
