<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'on');

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
}

if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', 'testing');
}

set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path(),
            realpath(APPLICATION_PATH . '/../'),
            realpath(APPLICATION_PATH . '/../tests/application'),
            realpath(APPLICATION_PATH . '/../tests')
        )
    )
);

require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$options = $application->getOptions();
$dbAdapter = Zend_Db::factory(
    $options['resources']['db']['adapter'],
    $options['resources']['db']['params']
);

$registry = Zend_Registry::getInstance();
$registry->application = $application;
$registry->config = $application->getOptions();
$registry->db = $dbAdapter;

Zend_Loader_Autoloader::getInstance()
    ->registerNamespace('Unittest_')
    ->setFallbackAutoloader(true);

Zend_Session::$_unitTestEnabled = true;
