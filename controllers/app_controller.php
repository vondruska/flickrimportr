<?php

/*
 *  This file is part of FlickrImportr.

  FlickrImportr is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Foobar is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with FlickrImportr.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @author Steven Vondruska <vondruska@gmail.com>
 * 
 */

class AppController extends Controller {

    var $facebook;
    var $flickr;
    var $user;
    var $flickr_user;
    var $components = array('Session', 'RequestHandler', 'Process');
    var $uses = array('User', 'Photo', 'Job');
    var $helpers = array('Form', 'Flickr', 'Text', 'Time', 'Javascript');
    var $maintenance = false;

    function __construct() {
        parent::__construct();
        // Create a Facebook client API object
        $this->facebook = new Facebook(array(
                    'appId' => FACEBOOK_APP_ID,
                    'secret' => FACEBOOK_SECRET,
                    'cookie' => true,
                    'fileUpload' => true
                ));

        $this->facebook->setFileUploadSupport(true);

        // Create a flickr api object
        $this->flickr = new phpflickr($GLOBALS['flickrApiKey'], $GLOBALS['flickrSecret']);
    }

    function beforeFilter() {
        $this->set('controller', $this->params['controller']);
        $this->set('action', $this->params['action']);

        // maintenance mode check
        if ($this->maintenance == true && $this->params['controller'] != 'import' && (!isset($_POST['fb_sig_user']))) {
            include('maintenance.html');
            exit();
        }

        // hit api.flickr.com to see if it responses before trying to test API
        // TODO: implement better, caused errors at random times
        /* $c = curl_init();
          curl_setopt($c, CURLOPT_URL, 'http://api.flickr.com/');
          curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
          curl_setopt($c, CURLOPT_TIMEOUT, 3);
          curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
          if(!curl_exec($c)) {
          $this->render('/pages/flickr_down');
          } */

        // quickly test the API and make sure its returning the correct information
        /* $microtime = microtime();
          $test = $this->flickr->test_echo(array('time' => $microtime));
          if($test['time'] != $microtime) {
          $this->render('/pages/flickr_down');
          } */

        // get session
        $session = $this->facebook->getSession();

        if ($session) {
            try {
                $uid = $this->facebook->getUser();
                $this->user = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                $this->forceLogin();
            }
        } else {
            $this->forceLogin();
        }

        $query = $this->facebook->api(array(
                    'method' => 'fql.query',
                    'query' => "SELECT publish_stream,offline_access,user_photos,manage_pages FROM permissions WHERE uid = me()"));

        foreach ($query[0] as $key => $value) {
            if ($value != 1) {
                $this->forceLogin();
            }
        }

        // add json as allowed request type
        $this->RequestHandler->setContent('json', 'text/x-json');

        if (!isset($_SESSION['photos']))
            $_SESSION['photos'] = array();

        $this->flickr->setToken('');
        unset($_SESSION['phpFlickr_auth_token']);

        $this->User->updateAll(array('User.last_seen' => "NOW()", 'User.fb_access_token' => "'" . $this->facebook->getAccessToken() . "'"), array('User.id' => $this->user['id']));
        $database = $this->User->find('first', array('conditions' => array('User.id' => $this->user['id'])));

        if (!$database)
            $this->forceLogin();

        if (((isset($database['User']['flickr_auth']) && $database['User']['flickr_auth'] != "") && (isset($database['User']['flickr_user']) && $database['User']['flickr_user'] != ""))) {
            $this->flickr->setToken($database['User']['flickr_auth']);
            $this->flickr_user = $database['User']['flickr_user'];
        } elseif ($this->params['controller'] != 'home' && $this->params['controller'] != 'authorize') {
            $this->redirect('/' . $GLOBALS['appPath'] . '/');
        }

        if ($this->isAjax()) {
            $this->layout = 'ajax';
            $this->viewPath = 'ajax';
            Configure::write('debug', 0);
        }
    }

    function redirect($url, $status = null, $exit = true) {
        // Prevent the action's view from automatically rendering.
        $this->autoRender = false;

        // Return the Facebook redirect FBML.
        echo '<fb:redirect url="' . $url . '"/>';

        // Prevent any code following the redirect from being executed.
        if ($exit) {
            exit();
        }
    }

    // check to see if request is facebook ajax request
    function isAjax() {
        if ((isset($_POST['fb_sig_is_ajax']) && ($_POST['fb_sig_is_ajax'] == 1)) || (isset($_POST['fb_sig_is_mockajax']) && ($_POST['fb_sig_is_mockajax'] == 1)) || (isset($_POST['ajax']) && $_POST == true))
            return true;
    }

    function get_auth_url($perms = "read") {
        $api_sig = md5($GLOBALS['flickrSecret'] . "api_key" . $GLOBALS['flickrApiKey'] . "perms" . $perms);
        return "http://www.flickr.com/services/auth/?api_key=" . $GLOBALS['flickrApiKey'] . "&perms=" . $perms . "&api_sig=" . $api_sig;
    }

    function enableFlickrCache() {
        $this->flickr->enableCache("db", "mysql://");
    }

    function createIssue($summary, $description) {
        // TODO: implement create issue functionality
        // mail();
    }

    function forceLogin() {
        if ($this->params['controller'] != "authorize")
            $this->redirect($this->facebook->getLoginUrl(
                            array(
                                "req_perms" => 'publish_stream,offline_access,user_photos,manage_pages',
                                'next' => 'http://apps.facebook.com/' . $GLOBALS['appPath'] . '/authorize/facebook',
                                'canvas' => 1,
                                'fbconnect' => 0,
                                'display' => 'page',
                                'cancel_url' => 'http://www.facebook.com/',
                            )
                    ));
    }

}

?>
