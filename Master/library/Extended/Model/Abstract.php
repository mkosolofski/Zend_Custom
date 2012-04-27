<?php
/**
 * Contains the Extended_Model_Abstract object.
 *
 * @package Extended
 * @subpackage Model
 */

/**
 * The Extended_Model_Abstract object which is extended by all models.
 * 
 * @package Extended
 * @subpackage Model
 */
abstract class Extended_Model_Abstract
{
    /**
     * Returns the value of a given field name.
     * 
     * @param string $name The field name to get the value of.
     * @return mixed The field value.
     * @throws Extended_Model_Exception Invalid $name or no value set.
     */
    public function __get($name)
    {
        if (!$this->validateField(array($name => ''), false)) {
            throw new Extended_Model_Exception('The given field does not exist. Name: ' . (string)$name);
        }

        if (!array_key_exists($name, $this->_data)) {
            throw new Extended_Model_Exception('The given field is not set. Name: ' . (string)$name);
        }

        return $this->_data[$name];
    }

    /**
     * Updates a model field with a given value.
     * 
     * @param string $name The field name to update. 
     * @param mixed $value The value to update the field with.
     * @throws Extended_Model_Exception Invalid $name or $value.
     * @return Model_Abstract An instance of the object.
     */
    public function __set($name, $value)
    {
        if (!$this->validateField(array($name => $value), true)) {
            throw new Extended_Model_Exception(
                'Invalid field name. The given field is not updatable. ' .
                'Name: ' . (string)$name . ', Value: ' . (string)$value
            );
        }

        $this->_data[$name] = $value;
        return $this;
    }

    /**
     * Unsets a field in the model.
     * 
     * @param string $name The name of the field to unset.
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }

    /**
     * Returns an associative array of field data.
     * 
     * @return array The field data.
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Returns the primary keys of the associated table.
     * 
     * @return array An array of primary keys.
     */
    public function getPrimaryKeys()
    {
       return $this->_primaryKeys; 
    }

    /**
     * Returns an associative array of model data.
     * 
     * @return array The model data.
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Validates a given field name and value.
     *
     * @param array $nameValue A name value field pair to validate.
     * @param bool $validateValue Optional. If true, the value is validated. 
     * @return bool true if valid, false otherwise.
     */
    abstract public function validateField($nameValue, $validateValue = true);

    /**
     * The model data.
     *
     * @var array An associative array of model data.
     */
    protected $_data = array();

    /**
     * The primary key field name of the associated table.
     * 
     * @var string
     */
    protected $_primaryKey;
}
