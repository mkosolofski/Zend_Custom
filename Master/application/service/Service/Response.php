<?php
/**
 * Contains Service_Response
 *
 * @package Service
 */

/**
 * Response object for service object calls.
 *
 * @package Service
 */
class Service_Response
{
    /**
     * Sets the response message.
     * 
     * @param mixed $message The message to set.
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * Sets the response result.
     * 
     * @param bool $result The result.
     * @throws Service_Exception Invalid method parameter.
     */
    public function setResult($result)
    {
        if (!is_bool($result)) {
            throw new Service_Exception('Invalid $result param. Expected a boolean value.');
        }

        $this->_result = $result;
    }

    /**
     * Returns the response condition.
     * Example return:
     * <pre>
     *     array(
     *         'message' => mixed,
     *         'result' => bool
     *     )
     * </pre>
     *
     * @return array The response condition.
     */
    public function get()
    {
        return array(
            'message' => $this->_message,
            'result' => $this->_result
        );
    }

    /**
     * The response result.
     * 
     * @var bool
     */
    protected $_result = false;

    /**
     * The response message.
     * 
     * @var mixed
     */
    protected $_message = null;

}
