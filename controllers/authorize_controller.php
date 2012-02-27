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

class AuthorizeController extends AppController {

    var $uses = array();

    function beforeFilter() {
        $GLOBALS["BYPASS_AUTH"] = true;
        parent::beforeFilter();
    }

    function flickr() {
        if ($_GET['frob']) {
            $token = $this->flickr->auth_getToken($_GET['frob']);
            $this->User->updateAll(array('User.flickr_auth' => "'" . $token['token'] . "'", 'User.flickr_user' => "'" . $token['user']['nsid'] . "'"), array('User.id' => $this->user['id']));
        }
        $this->redirect('/' . $GLOBALS['appPath'] . '/');
        exit();
    }

    function facebook() {
        $user = $this->User->find('first', array('conditions' => array('User.id' => $this->facebook->getUser())));
        if (isset($user['User'])) {
            $this->User->updateAll(array('User.disabled' => "0", 'User.fb_access_token' => "'" . $this->facebook->getAccessToken() . "'"), array('User.id' => $this->facebook->getUser()));
        } else {
            $this->User->save(array('User' => array('id' => $this->facebook->getUser(), 'fb_access_token' => $this->facebook->getAccessToken())));
        }
        $this->redirect('/' . $GLOBALS['appPath']);
    }

    function remove() {
        $data = $this->facebook->getSignedRequest();
        $this->User->updateAll(array('User.disabled' => "1", 'User.flickr_auth' => "''", 'User.fb_access_token' => "''"), array('User.id' => $data['user_id']));
    }

}

?>
