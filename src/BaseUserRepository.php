<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM;


class BaseUserRepository
{
    /**
     * @var string
     */
    private $entity_name;

    public function __construct($entity_name)
    {
        $this->entity_name = $entity_name;
    }

    /**
     * @param $id
     * @return AbstractUserEntity|null
     */
    public function find($id)
    {
        if($wp_user = get_userdata($id))
        {
            return new $this->entity_name($wp_user);
        }
        return null;
    }


}