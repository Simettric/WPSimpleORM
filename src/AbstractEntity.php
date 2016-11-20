<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 14/11/16
 * Time: 17:17
 */

namespace Simettric\WPSimpleORM;


use Simettric\WPSimpleORM\Exception\IncorrectPostTypeException;

abstract class AbstractEntity
{

    const RELATION_SINGLE   = 'single';
    const RELATION_MULTIPLE = 'multiple';

    /**
     * @var \WP_Post | null
     */
    private $post;

    /**
     * @var array
     */
    private $meta_fields=array();

    /**
     * @var string
     */
    private $meta_prefix='sim_simple_orm';

    /**
     * @var BaseRepository
     */
    protected $repository;


    private $relations=array();

    private $relatedTo=array();



    public function __construct(\WP_Post $post=null)
    {
        if($post && $post->post_type != $this->getPostType())
            throw new IncorrectPostTypeException(get_class($this) . ' must to be related to a WP_Post with a "' . $this->getPostType().'" post_type');



        $this->configure();

        if($post){
            $this->setPost($post);
        }
    }

    public function getId()
    {
        return $this->post->ID;
    }

    public function getTitle()
    {
        return $this->post->post_title;
    }


    abstract public function configure();

    abstract public function getPostType();

    public static function isEntityPostType(\WP_Post $post)
    {

        try{

            $class_name = get_called_class();
            new $class_name($post);
            return true;

        }catch (IncorrectPostTypeException $e)
        {
            return false;
        }
    }

    public function setPost(\WP_Post $post)
    {
        $this->post = $post;
    }


    public function setRepository(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \WP_Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return array
     */
    public function getMetaFields()
    {
        return $this->meta_fields;
    }

    /**
     * @return string
     */
    public function getMetaPrefix()
    {
        return $this->meta_prefix;
    }

    /**
     * @return array
     */
    public function getConfiguredRelations()
    {
        return $this->relations;
    }

    /**
     * @param $field
     * @return mixed|null
     */
    protected function get($field)
    {

        if(isset($this->meta_fields[$field]))
            return $this->meta_fields[$field];

        if($field_value = get_post_meta($this->post->ID, $this->getMetaPrefix()."_".$field, true))
        {
            $this->set($field, $field_value);
            return $this->meta_fields[$field];
        }

        return null;
    }

    /**
     * @param $field
     * @param $value
     */
    protected function set($field, $value)
    {
        $this->meta_fields[$field] = $value;

        if($this->post)
            update_post_meta($this->getPost()->ID, $this->getMetaPrefix() ."_". $field, $value);
    }

    /**
     * @param $entityName
     * @param string $type
     * @throws \Exception
     */
    protected function configureRelation($entityName, $type=self::RELATION_MULTIPLE)
    {
        if(($type != self::RELATION_MULTIPLE) && ($type != self::RELATION_SINGLE))
        {
            throw new \Exception('Unexpected relation type');
        }

        $this->relations[$entityName] = $type;
    }

    /**
     * @param AbstractEntity $entity
     * @return $this
     * @throws \Exception
     */
    protected function addRelatedTo(AbstractEntity $entity)
    {
        $entity_name = get_class($entity);
        if(!isset($this->relations[$entity_name]))
        {
            throw new \Exception('Relation with "'.$entity_name.'" is not configured');
        }

        if($this->relations[$entity_name]==static::RELATION_SINGLE)
        {
            $this->relatedTo[$entity_name] = $entity;
            if($this->post){
                update_post_meta($this->getPost()->ID, $this->getMetaPrefix() ."_". $entity_name, $entity->post->ID);
            }

        }

        if($this->relations[$entity_name]==static::RELATION_MULTIPLE)
        {
            $this->relatedTo[$entity_name][$entity->getPost()->ID] = $entity;

            add_post_meta($this->getPost()->ID, $this->getMetaPrefix() ."_". $entity_name, $entity->post->ID);
            update_post_meta($entity->post->ID, $this->getMetaPrefix() ."_inv_". get_class($this), $this->getPost()->ID);
        }

        return $this;
    }

    /**
     * @param $entity_name
     * @throws \Exception
     */
    protected function getRelatedTo($entity_name)
    {

        if(!isset($this->relations[$entity_name]))
        {
            throw new \Exception('Relation with "'.$entity_name.'" is not configured');
        }

        if($this->relations[$entity_name]==static::RELATION_SINGLE)
        {
            $post_id = get_post_meta($this->getPost()->ID, $this->getMetaPrefix() ."_". $entity_name, true);
            return new $entity_name(get_post($post_id));
        }

        if($this->relations[$entity_name]==static::RELATION_MULTIPLE)
        {
            if(!$this->repository)
            {
                $this->repository = new BaseRepository($entity_name);
            }

            return $this->repository->getMultipleRelated($this);
        }

    }

    /**
     * @param AbstractEntity $entity
     * @throws \Exception
     */
    protected function removeRelatedTo(AbstractEntity $entity)
    {
        $entity_name = get_class($entity);
        if(!isset($this->relations[$entity_name]))
        {
            throw new \Exception('Relation with "'.$entity_name.'" is not configured');
        }

        if($this->relations[$entity_name]==static::RELATION_SINGLE)
        {
            delete_post_meta($this->getPost()->ID, $this->getMetaPrefix() ."_". $entity_name);
        }

        if($this->relations[$entity_name]==static::RELATION_MULTIPLE)
        {
            delete_post_meta($this->getPost()->ID, $this->getMetaPrefix() ."_". $entity_name, $entity->getPost()->ID);
            delete_post_meta($entity->post->ID, $this->getMetaPrefix() ."_inv_". get_class($this), $this->getPost()->ID);
        }

    }

}
