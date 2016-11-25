<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM\Admin;


use Simettric\WPSimpleORM\AbstractEntity;

class EntityRelationsHooks
{

    /**
     * @var AbstractEntity
     */
    private $entityInstance;

    function __construct($entityClass)
    {
        $this->entityInstance = new $entityClass;
        if(!$this->entityInstance instanceof AbstractEntity)
            throw new \Exception( get_class().' needs an ' . AbstractEntity::class );
    }

    function registerHooks()
    {
        $adminMenuPage = new EntityRelationAdminMenu(get_class($this->entityInstance));
        $adminMenuPage->registerSubscribers();

        $metabox = new EntityRelationsMetaBox(get_class($this->entityInstance));
        $metabox->registerSubscribers();
    }

}