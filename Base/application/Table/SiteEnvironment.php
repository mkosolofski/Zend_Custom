<?php
/**
 * Contains Table_SiteEnvironment.
 *
 * @package Table
 */

/**
 * The "SiteEnvironment" table object.
 *
 * @package Table
 */
class Table_SiteEnvironment extends Extended_Db_Table_Abstract
{
    /**
     * The table name. 
     * 
     * @var string
     */
    protected $_name = 'siteEnvironment';

    /**
     * The schema that this table is in. 
     * 
     * @var string
     */
    protected $_schema = 'media';

    /**
     * The table that is used for unit testing. 
     * 
     * @var string
     */
    protected $_tempCreate = '
        CREATE TEMPORARY TABLE `media`.`siteEnvironment` (
            `name` varchar(30) NOT NULL,
            `value` varchar(100) NOT NULL,
            PRIMARY KEY (`name`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC';
}
