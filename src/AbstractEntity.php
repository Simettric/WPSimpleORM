<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM;


use Simettric\WPSimpleORM\Exception\IncorrectPostTypeException;

abstract class AbstractEntity implements WordPressEntityInterface, EntityInterface
{

    /**
     * @var \WP_Post | null
     */
    private $post;

    /**
     * @var array
     */
    private $meta_fields=array();



    /**
     * @var BaseRepository
     */
    private $repository;


    private $relations=array();

    private $relatedTo=array();

    private $inversedRelated=array();



    public function __construct(\WP_Post $post=null)
    {

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

        return $this->post ? $this->post->post_title : "";
    }


    abstract public function configure();


    public function getPostType()
    {
        return static::getEntityPostType();
    }

    /**
     * @param \WP_Post $post
     * @return bool
     */
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
        if($post->post_type != $this->getPostType())
            throw new IncorrectPostTypeException(get_class($this) . ' must to be related to a WP_Post with a "' . $this->getPostType().'" post_type');

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
        return static::getStaticMetaPrefix();
    }

    public function getRelationMetaKey()
    {
        return static::getStaticRelationMetaKey();
    }


    public static function getStaticMetaPrefix()
    {
        return 'sim_simple_orm_';
    }

    protected static function getMetaClassName()
    {
        return str_replace('\\', '', get_called_class());
    }

    public static function getStaticRelationMetaKey()
    {
        return self::getStaticMetaPrefix() . self::getMetaClassName();
    }

    /**
     * @return array
     */
    public function getConfiguredRelations()
    {
        return $this->relations;
    }

    /**
     * @return array
     */
    public function getConfiguredInversedRelations()
    {
        return $this->inversedRelated;
    }

    /**
     * @param $field
     * @return mixed|null
     */
    protected function get($field)
    {

        if(isset($this->meta_fields[$field]))
            return $this->meta_fields[$field];

        if(!$this->post) return;

        if($field_value = get_post_meta($this->post->ID, $this->getMetaPrefix().$field, true))
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
            update_post_meta($this->getPost()->ID, $this->getMetaPrefix(). $field, $value);
    }

    /**
     * @return BaseRepository
     */
    protected function getRepository()
    {
        if(!$this->repository)
        {
            $this->repository = new BaseRepository(get_class($this));
        }

        return $this->repository;
    }

    /**
     * @param $entityName
     * @param string $type
     * @throws \Exception
     */
    protected function configureRelation($entityName, $type=self::ONE_TO_MANY)
    {
        if((false === in_array($type, array(self::ONE_TO_MANY, self::ONE_TO_ONE, self::MANY_TO_ONE, self::MANY_TO_MANY))))
        {
            throw new \Exception('Unexpected relation type');
        }

        $this->relations[$entityName] = $type;
    }

    /**
     * @param $entityName
     * @param string $type
     * @throws \Exception
     */
    protected function configureInverseRelation($entityName, $type=self::ONE_TO_MANY)
    {
        if(false === in_array($type, array(self::ONE_TO_MANY, self::ONE_TO_ONE, self::MANY_TO_ONE, self::MANY_TO_MANY)))
        {
            throw new \Exception('Unexpected relation type');
        }

        $this->inversedRelated[$entityName] = $type;
    }

    /**
     * @param AbstractEntity $entity
     * @return $this
     * @throws \Exception
     */
    protected function addRelatedTo(AbstractEntity $entity)
    {

        $this->getRepository()->addRelatedTo($entity, $this);

        return $this;
    }


    /**
     * @param AbstractEntity $entity
     * @throws \Exception
     */
    protected function removeRelatedTo(AbstractEntity $entity)
    {

        $this->getRepository()->removeRelatedTo($entity, $this);

    }

}
