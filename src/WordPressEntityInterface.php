<?php
/**
 * This interface is used in order to define required static fields in your entity
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM;


interface WordPressEntityInterface
{


    const ONE_TO_MANY  = 'one_to_many';
    const MANY_TO_ONE  = 'many_to_one';
    const MANY_TO_MANY = 'many_to_many';
    const ONE_TO_ONE   = 'one_to_one';

    /**
     * @return string
     */
    public static function getEntityPostType();


}