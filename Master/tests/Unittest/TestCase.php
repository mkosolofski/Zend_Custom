<?php
/**
 * Contains Unittest_TestCase 
 */

/**
 * Extends PHPUnit_Framework_TestCase for custom testing. 
 * 
 * @uses PHPUnit_Framework_TestCase
 */
class Unittest_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Run at the end of each test.
     */
    public function tearDown()
    {
        // Clear all temp tables before the beginning of each test.
        $registry = Zend_Registry::getInstance();
        if (isset($registry->testTempTables)
            && count($registry->testTempTables) > 0
        ) {
            $registry->db->query('drop tables ' . implode(',', array_keys($registry->testTempTables))); 
            $registry->testTempTables = array();
        }

        parent::tearDown();
    }
}
