<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM\Admin;


use Simettric\WPSimpleORM\AbstractEntity;

class EntityRelationsMetaBox
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

    public function registerSubscribers()
    {
        add_action('add_meta_boxes', array($this, 'onAddMetaBoxes'));
    }

    public function onAddMetaBoxes()
    {
        $post_type = call_user_func(array(get_class($this->entityInstance), 'getEntityPostType')) ;
        add_meta_box( 'sim_rel_' . $post_type,
            sprintf(__( "%s relationships"), get_class($this->entityInstance)),
            array($this, 'view'),
            $post_type, 'normal', 'high' );
    }

    public function view(\WP_Post $post)
    {
        $this->entityInstance->setPost($post);


        include __DIR__ . '/../Views/Admin/meta_box.php';

    }


}