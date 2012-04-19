<?php
/**
 * Contains Bootstrap
 *
 * @package Application
 */

/**
 * The application bootstrap object.
 * 
 * @package Application
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Bootstrap application.ini into config.
     */
    protected function _initConfig()
    {
        Zend_Registry::getInstance()->config = $this->getOptions();
    }

    /**
     * Bootstrap the database based on application.ini settings, Stores the
     * the database adapter instance in the Zend_Registry for later use.
     */
    protected function _initDb()
    {
//        $options = $this->getOptions();
//        $dbAdapter = Zend_Db::factory(
//            $options['resources']['db']['adapter'],
//            $options['resources']['db']['params']
//        );
//        Zend_Registry::getInstance()->db = $dbAdapter;
    }

    /**
     * Bootstrap the "view" module and set the site layout doc type.
     */
    protected function _initDocType()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }
}
