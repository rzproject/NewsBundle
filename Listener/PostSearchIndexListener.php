<?php

/*
 * This file is part of the RzSearchBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ZendSearch\Lucene\Document;
use Rz\SearchBundle\Listener\AbstractSearchIndexListener;
use Sonata\NewsBundle\Model\PostInterface;


class PostSearchIndexListener extends AbstractSearchIndexListener
{
    protected $entityId;
    protected $configManager;
    protected $searchClient;
    protected $container;

    /**
     * Constructor
     *
     * @param $id
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Rz\SearchBundle\Model\ConfigManagerInterface $configManager
     */
    public function __construct($entityId, ContainerInterface $container){
        $this->entityId = $entityId;
        $this->container = $container;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();		
        if($entity instanceof PostInterface) {			
			if(method_exists($entity, 'getNeedIndexer') && $entity->getNeedIndexer() === false){
				return;
			}			
			if($this->getConfigManager()->hasIndex($this->entityId)) {
				if($indexManager = $this->getIndexManager()) {
					try {
						$indexManager->processIndexData('insert', $entity, $this->entityId);
					} catch (\Exception $e) {
						throw $e;
					}
				}
			}      
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();		
        if($entity instanceof PostInterface) {			
			if(method_exists($entity, 'getNeedIndexer') && $entity->getNeedIndexer() === false){
				return;
			}
			if($this->getConfigManager()->hasIndex($this->entityId)) {
				if($indexManager = $this->getIndexManager()) {
					try {
						$indexManager->processIndexData('update', $entity, $this->entityId);
					} catch (\Exception $e) {
						throw $e;
					}
				}
			}			
        }
    }
}
