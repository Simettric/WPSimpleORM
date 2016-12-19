<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM;


use Simettric\WPSimpleORM\Exception\IncorrectPostTypeException;

abstract class AbstractUserEntity implements WordPressEntityInterface, EntityInterface
{



    /**
     * @var \WP_User | null
     */
    protected $user;


    /**
     * @var BaseRepository
     */
    private $repository;


    /**
     * @var array
     */
    private $meta_fields=array();


    public function __construct(\WP_User $user)
    {

        $this->user = $user;
    }

    public function getId()
    {
        return $this->user->ID;
    }

    public function getFirstName()
    {

        return $this->user->first_name;
    }

    public function getLastName()
    {

        return $this->user->last_name;
    }

    public function getDisplayName()
    {

        return $this->user->display_name;
    }

    public function getUsername()
    {

        return $this->user->user_login;
    }

    public function getEmail()
    {

        return $this->user->user_email;
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
     * @return \WP_User
     */
    public function getWPUser()
    {
        return $this->user;
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
        return static::getORMMetaPrefix();
    }

    public static function getORMMetaPrefix()
    {
        return 'sim_simple_orm';
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


        if($field_value = get_user_meta($this->user->ID, $this->getMetaPrefix()."_".$field, true))
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

        update_user_meta($this->getWPUser()->ID, $this->getMetaPrefix() ."_". $field, $value);
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





}
