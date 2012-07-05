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
 * Contains methods for scraping http://www.houseseats.com
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
     * Returns the last update time of the show list as presented by the site.
     * 
     * @return string The last update time.
     */
    public function getLastUpdateTime()
    {
        preg_match('/' . "\t\t\t" . ' as of (.*)/', $this->_getPage(self::PAGE_ACTIVE_SHOWS), $matches);
        return $matches[1];
    }

    /**
     * Returns the active show list on the site.
     *
     * Example return:
     * <pre>
     *     array(
     *         array(
     *             'name' => the name of the show
     *             'imageLink' => the full url to the show image
     *             'descLink' => the full url to the show description page
     *         )
     *     )
     * </pre>
     * 
     * @return array The active show list.
     */
    public function getShows()
    {
        $response = $this->_getPage(self::PAGE_ACTIVE_SHOWS);

        preg_match_all('/<td><a href="(.*)">(.*)<\/a.*/', $response, $shows);
        preg_match_all('/<td valign="top"><a href=".*"><img src="(.*)" width="100"/', $response, $showPics);
        
        $result = array();
        for($index = 0; $index < count($shows[0]); $index ++) {
            $result[] = array(
                'name' => $shows[2][$index],
                'imageLink' =>  self::URL . $showPics[1][$index],
                'descLink' => self::URL . $shows[1][$index]
            );
        }

        return $result;
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
        
        $response = $client->request();

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
