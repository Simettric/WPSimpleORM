<?php
/**
 * This interface is used in order to define required static fields in your entity
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM;


interface WordPressEntityInterface
{

    const RELATION_SINGLE   = 'single';
    const RELATION_MULTIPLE = 'multiple';

    /**
     * @return string
     */
    public static function getEntityPostType();


}