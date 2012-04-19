<?php
/**
 * Contains Unittest_All 
 */

/**
 * Contains all unit tests. 
 */
class Unittest_All
{
    /**
     * Includes unit test.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        return $suite;
    }
}
