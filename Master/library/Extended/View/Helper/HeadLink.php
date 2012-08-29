<?php
/**
 * Contains Extended_View_Helper_HeadLink 
 */

/**
 * Extends the parent object so that versioning is automatically added to
 * css files.
 */
class Extended_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
{
    /**
     * Overrides parent magic __call method to set versions on css files.
     * 
     * @see Zend_View_Helper_HeadLink
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)(?P<type>Stylesheet|Alternate)$/', $method, $matches)) {
            if (strpos(strtolower($method), 'stylesheet') !== false) {
                $version = filemtime(APPLICATION_PATH . '/../public/' . $args[0]);
                $args[0] = str_replace('.css', $version . '.css', $args[0]);
            }
        }

        parent::__call($method, $args);
    }
}
