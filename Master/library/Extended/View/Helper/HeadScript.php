<?php
/**
 * Contains Extended_View_Helper_HeadScript 
 */

/**
 * Extends the parent object so that versioning is automatically added to
 * JavaScript files.
 */
class Extended_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    /**
     * Overrides parent magic __call method to set versions on javascript files.
     * 
     * @see Zend_View_Helper_HeadScript
     */
    public function __call($method, $args)
    {
        $version = filemtime(APPLICATION_PATH . '/../public/' . $args[0]);
        $args[0] = str_replace('.js', $version . '.js', $args[0]);
        parent::__call($method, $args);
    }
}
