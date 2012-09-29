<?php
/**
 * Contains Extended_Service_HouseSeats
 * 
 * @package Extends
 * @subPackage Service
 */

/**
 * Define the namespace
 */
namespace Extended\Service;

/**
 * Contains methods for scraping http://www.houseseats.com on ACTIVE shows.
 * 
 * @package Extends
 * @subPackage Service
 */
class HouseSeats extends \Zend_Service_Abstract
{
    /**
     * The base url. 
     */
    const URL = 'http://www.houseseats.com';

    /**
     * The site path to the login page.
     */
    const PAGE_LOGIN = '/member/index.bv';

    /**
     * The site path to the show listing page.
     */
    const PAGE_ACTIVE_SHOWS = '/member/tickets/';

    /**
     * Instantiate the object with log in credentials.
     * 
     * @param string $email The email log in credential.
     * @param string $password The password log in credential.
     * @throws Extended_Service_Exception Invalid method parameters.
     */
    public function __construct($email, $password)
    {
        if (!is_string($email) || empty($email)) {
            throw new Exception('Invalid $email parameter. Expected a non-empty string');
        }
        
        if (!is_string($password) || empty($password)) {
            throw new Exception('Invalid $email parameter. Expected a non-empty string');
        }

        $this->_email = $email;
        $this->_password = $password;
    }
    
    /**
     * Returns a numerical array of active shows.
     * 
     * @throws Extended_Service_Exception Invalid method parameter or show names not found.
     * @return array The list of active shows.
     */
    public function getShowNames()
    {
        preg_match_all(
            '/<td><a href=".*">(.*)<\/a.*/',
            $this->_getPage(self::PAGE_ACTIVE_SHOWS),
            $names
        );

        if (!isset($names)) {
            throw new Exception('Failed to get show names.');
        }
        return $names[1];
    }

    /**
     * Returns the details of an active show.
     * 
     * @param string $showName The name of the show to return the details of.
     * @throws Extended_Service_Exception Invalid method parameter or show details not found.
     * @return string The show details.
     */
    public function getShowDetails($showName)
    {
        if (!is_string($showName) || empty($showName)) {
            throw new Exception('Invalid $showName parameter. Expected a non-empty string');
        }
        
        preg_match(
            '/<td><a href="\.(.*)">' . preg_quote($showName, '/') . '<\/a.*/',
            $this->_getPage(self::PAGE_ACTIVE_SHOWS),
            $detailsLink
        );
        if (!isset($detailsLink)) {
            throw new Exception('Failed to get show details [1].');
        }

        preg_match(
            '/show description([\s\S]*)rsvp for a show/',
            strip_tags($this->_getPage(self::PAGE_ACTIVE_SHOWS . $detailsLink[1])),
            $details
        );
        
        if (!isset($details)) {
            throw new Exception('Failed to get show details [2].');
        }
        return trim($details[1]);
    }

    /**
     * Returns the image link of an active show.
     * 
     * @param string $showName The name of the show to return the image link of.
     * @throws Extended_Service_Exception Image link not found.
     * @return string The show image link.
     */
    public function getShowImageLink($showName)
    {
        return $this->_getUri('/resources/media/' . $this->getShowId($showName) . '_thumb.jpg');
    }

    /**
     * Retrurns the id of an active show.
     * 
     * @param string $showName The name of the show to return the image link of.
     * @throws Extended_Service_Exception Show id not found.
     * @return int The show id.
     */
    public function getShowId($showName)
    {
        preg_match(
            '/(\d*)">' .  preg_quote($showName, '/') . '/',
            $this->_getPage(self::PAGE_ACTIVE_SHOWS),
            $showId
        );

        if (!isset($showId)) {
            throw new Exception('Show id not found.');
        }
        return $showId[1];
    }

    /**
     * Returns the last update time of the show list as presented by the site.
     * 
     * @throws Extended_Service_Exception Failed to get last update time.
     * @return string The last update time.
     */
    public function getLastUpdateTime()
    {
        preg_match('/' . "\t\t\t" . ' as of (.*)/', $this->_getPage(self::PAGE_ACTIVE_SHOWS), $matches);

        if (!isset($matches)) {
            throw new Exception('Failed to get last update time.');
        }
        return $matches[1];
    }

    /**
     * Sets the subdomain used in the url of requests.
     * 
     * @param string $subdomain The subdomain.
     * @throws Extended_Service_Exception Invalid method parameter.
     */
    public function setSubdomain($subdomain)
    {
        if (!is_string($subdomain) || empty($subdomain)) {
            throw new Exception('Invalid $subdomain parameter. Expected a non-empty string.');
        }

        $this->_subdomain = $subdomain;
    }

    /**
     * Email log in credential. 
     * 
     * @var string
     */
    protected $_email = '';

    /**
     * Password log in credential. 
     * 
     * @var string
     */
    protected $_password = '';
    
    /**
     * The subdomain used in the request url.
     * 
     * @var string
     */
    protected $_subdomain = 'www';

    /**
     * A cache of page response bodies. 
     * 
     * @var array
     */
    protected $_pageCache = array();
    
    /**
     * A flag if the request object is logged in to the site. 
     * 
     * @var bool
     */
    protected $_loggedIn = false;

    /**
     * Returns the site response when viewing a page.
     * 
     * @param string $page The page path to get.
     * @return string The page body.
     */
    protected function _getPage($page)
    {
        if (!array_key_exists($page, $this->_pageCache)) {
            $this->_login();
            
            $this->_pageCache[$page] = $this->_request( 
                $this->_getUri($page)
            );
        }

        return $this->_pageCache[$page];
    }

    /**
     * Logs in to the site based on the email and password credentials.
     */
    protected function _login()
    {
        if ($this->_loggedIn === true) {
            return;
        }

        $this->_request(
            $this->_getUri(self::PAGE_LOGIN),
            array(
               'submit' => 'login',
               'email' => $this->_email,
               'password' => $this->_password,
               'x' => 0,
               'y' => 0,
               'lastplace' => urlencode('/')
            )
        );

        $this->_loggedIn = true;
    }

    /**
     * Performs a curl request to the site based on a given array
     * of curl options.
     * 
     * @param array $options An array of curl options. 
     * @throws Extended_Service_Exception Request failed.
     * @return string The response body. 
     */
    protected function _request($uri, $post = array())
    {
        $client = $this->getHttpClient()
            ->resetParameters()
            ->setUri($uri);
        $client->setMethod($client::GET);

        $cookieJar = $client->getCookieJar();
        if (!isset($cookieJar)) {
            $client->setCookieJar(true);
        }

        if (!empty($post)) {
            $client->setMethod($client::POST);

            foreach($post as $name => $value) {
                $response = $client->setParameterPost($name, $value);
            }
        }

        try {
            $response = $client->request();
        } catch (Zend_Http_Client_Exception $e) {
            throw new Exception($e->getMessage());
        }

        if ($response->isError()) {
            throw new Exception($response->getMessage());
        }

        return $response->getBody();
    }

    /**
     * Returns the full uri to houseseats.com
     * 
     * @param string $path A path to append to the url.
     * @return string The url.
     */
    protected function _getUri($path)
    {
        return 'http://' . $this->_subdomain . '.houseseats.com/' . $path;
    }
}
