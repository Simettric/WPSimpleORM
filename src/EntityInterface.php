<?php
/**
 *
 *
 * @author Asier Marqués <asiermarques@gmail.com>
 */

namespace Simettric\WPSimpleORM;


interface EntityInterface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();
}