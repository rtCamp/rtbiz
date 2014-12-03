<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Protocol\Smtp\Auth;

use Zend\Mail\Protocol\Smtp;

/**
 * Performs CRAM-MD5 authentication
 */
class Oauth2 extends Smtp
{
    /**
     * @var string
     */
    protected $_xoauth2_request;

    

    /**
     * Constructor.
     *
     * All parameters may be passed as an array to the first argument of the
     * constructor. If so,
     *
     * @param  string|array $host   (Default: 127.0.0.1)
     * @param  null|int     $port   (Default: null)
     * @param  null|array   $config Auth-specific parameters
     */
    public function __construct($host = '127.0.0.1', $port = null, $config = null)
    {
       if (is_array($host)) {
            if (isset($host['xoauth2_request'])) {
                $this->_xoauth2_request = $host['xoauth2_request'];
            }
        }
 
        parent::__construct($host, $port, $config);
    }


    /**
     * @todo Perform xOuath authentication with supplied credentials
     *
     */
    public function auth()
    {
        // Ensure AUTH has not already been initiated.
                // Ensure AUTH has not already been initiated.
        parent::auth();
        $this->_send('AUTH XOAUTH2 '.$this->_xoauth2_request);
        $this->_expect(235);
        $this->_auth = true;
    }

}