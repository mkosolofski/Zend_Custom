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
        $suite->addTest(Unittest_Controller_All::suite());
        $suite->addTest(Unittest_Extended_All::suite());
        $suite->addTest(Unittest_Website_All::suite());
        return $suite;
    }
}
