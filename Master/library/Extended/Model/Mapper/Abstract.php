<?php
/**
 * Contains Model_Mapper_Abstract
 *
 * @package Extended
 * @subpackage Model_Mapper
 */

/**
 * The abstract object for all Model_Mapper_* objects.
 * 
 * @package Extended
 * @subpackage Model_Mapper
 */
abstract class Extended_Model_Mapper_Abstract
{
    /**
     * Saves a given model into its associated table.
     * 
     * @param Model_Abstract $model The model to save.
     * @throws Website_Parameter_Exception Invalid $model parameter.
     * @throws Extended_Model_Exception Table not updated.
     * @return int|null The primary key of the record inserted.
     */
    public function save($model)
    {
        $modelName = $this->_getModelName();
        if (!($model instanceof $modelName)) {
            throw new Website_Parameter_Exception('This given $model is not an instance of ' . $modelName);
        }

        $table = $this->_getTable();
        $tableData = $model->getData();
        
        $updateWhere = array();
        foreach ($model->getPrimaryKeys() as $index => $key) {
            if (!isset($tableData[$key])) {
                $updateWhere = array();
                break;
            }

            $updateWhere[$key . ' = ?'] = $model->$key;
        }
        
        if (empty($updateWhere)) {
            return $table->insert($tableData);

        } else {
            $result = $table->update(
                $tableData,
                $updateWhere
            );
            if ($result < 1) {
                throw new Extended_Model_Exception('The model was not updated. Model: ' . $modelName);
            }
        }
    }

    /**
     * Returns a new model given primary key(s).
     * 
     * @param array $primaryKeys The primary keys of the table.
     * @return Model_Abstract The model for the given primary key.
     * @throws Website_Paramter_Exception Invalid $primaryKey parameter.
     * @throws Extended_Model_Exception Could not find entry given the primary key.
     */
    public function find($primaryKeys)
    {
        if (!is_array($primaryKeys)) {
            throw new Website_Parameter_Exception('Expected $primaryKeys to be an associative array of keys.');
        }
        
        $table = $this->_getTable(); 
        $select = $table->select();
        
        $model = $this->getModel();
        foreach ($model->getPrimaryKeys() as $index => $key) {

            if (!array_key_exists($key, $primaryKeys)) {
                throw new Website_Parameter_Exception(
                    'Missing primary keys. Expect: ' . implode(',', array_values($model->getPrimaryKeys()))
                );
            }

            $select->where($key . ' = ?', $primaryKeys[$key]);
        }
        
        $row = $table->fetchRow($select);
        if (is_null($row)) {
            throw new Extended_Model_Exception(
                'No record found for the given primary keys: ' . json_encode($primaryKeys)
            );
        }
        
        foreach ($row->toArray() as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }

    /**
     * Return is a given field and value are valid.
     * 
     * @param string $fieldName The field name to validate.
     * @param mixed $fieldValue The field value to validate.
     * @return bool True if field is valid, false otherwise.
     */
    public function isValidField($fieldName, $fieldValue)
    {
        if (!$this->getModel()->validateField(array($fieldName => $fieldValue))) {
            return false;
        }

        return true;
    }

    /**
     * Returns a new model object associated to the mapper.
     * 
     * @return Model_Abstract The table object. 
     */
    public function getModel()
    {
        $modelName = $this->_getModelName();
        return new $modelName;
    }

    /**
     * Returns the name of the model associated to the mapper.
     * 
     * @return string The name of the model.
     */
    protected function _getModelName()
    {
        $nameComponents = explode('_', get_class($this));
        return $nameComponents[0] . '_' . $nameComponents[2];
    }

    /**
     * Returns a new table object associated to the mapper.
     * 
     * @return Table_Abstract The table object. 
     */
    protected function _getTable()
    {
        $tableName = $this->_getTableName();
        return new $tableName;
    }
    
    /**
     * Returns the name of the table associated to the mapper.
     * 
     * @return string The name of the table.
     */
    protected function _getTableName()
    {
        $nameComponents = explode('_', get_class($this));
        return 'Table_' . $nameComponents[2];
    }
}
