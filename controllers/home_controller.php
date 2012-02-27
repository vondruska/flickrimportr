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

class HomeController extends AppController {

    var $uses = array();

    function beforeFilter() {
        parent::beforeFilter();
    }

    function index() {
        $this->set('auth', $this->flickr->test_login());
        $this->set('auth_url', $this->get_auth_url());
    }

    function options() {
        if (isset($this->params['form']['uninstall'])) {
            $this->facebook->api_client->expire_session();
            session_unset();
            $this->facebook->api(array('method' => 'auth.revokeAuthorization'));

            $this->redirect('http://www.facebook.com');
        }
        if (isset($this->params['form']['deauthorize'])) {
            $this->User->updateAll(array('User.flickr_auth' => '""', 'User.flickr_user' => '""'), array('User.id' => $this->user));

            $this->redirect('/' . $GLOBALS['appPath'] . '/');
        }

        if (isset($this->params['form']['queue']) && $this->isAjax()) {
            session_unset();

            $this->set('output', json_encode(array('status' => 'completed')));
            $this->render('ajax');
        }

        if (Configure::read('debug') > 0)
            $this->set('debug', true);
    }

    function invite() {
        if (isset($_POST["ids"])) {
            $this->set("message", "<center>Thanks for inviting " . sizeof($_POST["ids"]) . " of your friends to use <b><a href=\"http://apps.facebook.com/flickrimportr/\">FlickrImportr</a></b>.<br><br>\n<h2><a href=\"http://apps.facebook.com/flickrimportr/\">Click here to return to FlickrImportr</a>.</h2></center>");
        } else {
            // Retrieve array of friends who've already authorized the app.
            $fql = 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=' . $this->user . ') AND is_app_user = 1';
            $_friends = $this->facebook->api_client->fql_query($fql);

            // Extract the user ID's returned in the FQL request into a new array.
            $friends = array();
            if (is_array($_friends) && count($_friends)) {
                foreach ($_friends as $friend) {
                    $friends[] = $friend['uid'];
                }
            }

            // Convert the array of friends into a comma-delimeted string.
            $this->set("friends", implode(',', $friends));

            // Prepare the invitation text that all invited users will receive.
            $this->set("content", "<fb:name uid=\"" . $this->user . "\" firstnameonly=\"true\" shownetwork=\"false\"/> found an easier way to get photos on Facebook by using <a href=\"http://apps.facebook.com/flickrimportr/\">FlickrImportr</a>. \n" .
                    "<fb:req-choice url=\"" . $this->facebook->get_add_url() . "\" label=\"Start Using It Now!\"/>");
        }
    }

    function reset() {
        session_destroy();
        $this->flickr->setToken('');
        $this->redirect('/' . $GLOBALS['appPath']);
    }

}

?>