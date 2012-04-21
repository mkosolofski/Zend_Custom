<?php
/**
 * Contains Extended_Service_RottenTomatoes
 *
 * @package Extended
 * @subpackage Service
 */

/**
 * Contains methods for accessing the api of http://www.rottentomatoes.com
 * 
 * @package Extended
 * @subpackage Service
 */
class Extended_Service_RottenTomatoes
{
    /**
     * The Rotten Tomatoes api version. 
     */
    const VERSION = 'v1.0';

    /**
     * The uri of the api. 
     */
    const URI = 'http://api.rottentomatoes.com/api/public'; 
    
    /**+@#
     * The available request namespaces.
     */
    const NAMESPACE_MOVIES = 'movies.json';
    /**-@#*/

    /**
     * Initializes the object with an api key.
     * 
     * @param string $apiKey The api key.
     * @throws Extended_Service_Exception Invalid api key.
     */
    public function __construct($apiKey)
    {
        if (!is_string($apiKey)
            || trim($apiKey) == ''
        ) {
            throw new Extended_Service_Exception('Invalid $apiKey parameter. Expected non-empty string');
        }

        $this->_apiKey = $apiKey;
    }

    /**
     * Performs a movie search.
     * See: http://developer.rottentomatoes.com/docs/read/json/v10/Movies_Search
     * 
     * @param string $movieName Optional. The plain text search query to search for a
     *                          movie. Remember to URI encode this!
     * @param int $pageLimit Optional. The amount of movie search results to show per page.
     * @param int $page Optional. The selected page of movie search results.
     * @return string The server response.
     */
    public function movieSearch($movieName = null, $pageLimit = null, $page = null)
    {
        $params = array();
        if (is_string($movieName)) {
            $params['q'] = $this->_getSearchFriendlyMovieName($movieName);
        }

        if (!is_int($pageLimit)
            || ($pageLimit < 1 && !is_null($pageLimit))
        ) {
            $params['page_limit'] = $pageLimit;
        }
        
        if (!is_int($page) || $page < 1) {
            $params['page'] = $page;
        }

        return $this->_request(self::NAMESPACE_MOVIES, $params);
    }

    /**
     * The api key.
     * 
     * @var string
     */
    protected $_apiKey;

    /**
     * Function description goes here
     * 
     * @param string $namespace The request namespace
     * @param array $params A key value pair of request parameters.
     * @return string The response.
     */
    protected function _request($namespace, $params = array())
    {
        $clientObj = new Zend_Http_Client(self::URI . '/' . self::VERSION . '/' . $namespace);
        $clientObj->setParameterGet('apikey', $this->_apiKey);

        foreach ($params as $key => $value) {
            $clientObj->setParameterGet($key, $value);
        }

        $response = $clientObj->request();
        return $response->getBody();
    }

    /**
     * Takes the name of a movie and makes it more search friendly.
     * 
     * @param string $movieName The name of the movie to make for search friendly.
     * @return string The search friendly movie name.
     */
    protected function _getSearchFriendlyMovieName($movieName)
    {
        return basename(
            trim(
                str_replace('_', ' ', $movieName)
            )
        );
    }
}
