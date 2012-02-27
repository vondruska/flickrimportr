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

class ImportController extends AppController {

    var $uses = array();

    function beforeFilter() {
        parent::beforeFilter();
        $this->enableFlickrCache();
    }

    function build() {
        if ($this->isAjax()) {
            $startTime = time();
            $time = false;
            $arrayKey = array_keys($_SESSION['photos']);
            $photo = $_SESSION['photos'];
            if (!isset($_SESSION['start_cache']))
                $_SESSION['start_cache'] = 0;
            for ($_SESSION['start_cache']; $_SESSION['start_cache'] < count($_SESSION['photos']); $_SESSION['start_cache']++) {
                $this->flickr->photos_getInfo($_SESSION['photos'][$_SESSION['start_cache']]);
                $return['photos'][] = $photo[$_SESSION['start_cache']];
                if (time() - $startTime > 5) {
                    $time = true;
                    break;
                }
            }
            if ($time)
                $return['status'] = 'in progress';
            else {
                $return['status'] = 'completed';
                $return['percent'] = '100';
                if (isset($_SESSION['start_cache']))
                    unset($_SESSION['start_cache']);
            }
            if (!isset($return['percent']))
                $return['percent'] = round(($_SESSION['start_cache'] / count($_SESSION['photos'])) * 100);
            $this->set('output', json_encode($return));
            $this->render('ajax');
        }
    }

    function cache($photoid) {
        $this->enableFlickrCache();
        $this->flickr->photos_getInfo($photoid);
        $this->set('output', json_encode(array('status' => 'completed')));
        $this->render('ajax');
    }

    function go() {
        //$settings = $this->User->find('all', array('conditions' => array('User.id' => $this->user)));

        if (!empty($this->params['form']['user_id'])) {
            $accounts = $this->facebook->api('/me/accounts');

            foreach ($accounts['data'] as $account) {
                echo $this->params['form']['user_id'];
                if ($account['id'] == $this->params['form']['user_id']) {
                    $access_token = $account['access_token'];
                    $import_object_id = $account['id'];
                }
            }
        } else {
            $access_token = $this->facebook->getAccessToken();
            $import_object_id = "me";
        }

        $this->Job->save(
                array('Job' =>
                    array(
                        'user_id' => $this->user['id'],
                        'access_token' => $access_token,
                        'import_object_id' => $import_object_id,
                        'status' => 1,
                        'title' => isset($this->params['form']['album_name']) ? $this->params['form']['album_name'] : "",
                        'description' => isset($this->params['form']['album_desc']) ? $this->params['form']['album_desc'] : "",
                        'album_id' => ($this->params['form']['album_from'] == "facebook" && isset($this->params['form']['album_id']) && is_numeric($this->params['form']['album_id'])) ? $this->params['form']['album_id'] : 0,
                        'album_link' => (isset($album[0]['aid'])) ? $album[0]['link'] : '',
                        'album_privacy' => (isset($this->params['form']['album_privacy'])) ? $this->params['form']['album_privacy'] : 'EVERYONE'
                    )
                )
        );
        $jobId = $this->Job->id;

        foreach ($_SESSION['photos'] as $photo) {
            $data[] = array(
                'job_id' => $jobId,
                'flickr_id' => $photo,
                'description' => (isset($this->params['form']['desc_' . $photo])) ? $this->params['form']['desc_' . $photo] : "",
                'status' => 1
            );
        }
        $this->Photo->saveAll($data);
        $this->Process->start($jobId);

        $_SESSION['photos'] = array(); // clear photos
        $this->redirect('/' . $GLOBALS['appPath'] . '/jobs/');
    }

    function review() {
        if ($_POST['action'] == "remove_photo") {
            $this->layout = 'ajax';
            $this->viewPath = 'ajax';
            $return = array();
            $key = array_search($_POST['photo_id'], $_SESSION['photos']);
            unset($_SESSION['photos'][$key]);
            $return['status'] = ($key === false) ? 'not found' : 'completed';
            $return['key'] = $key;
            $return['id'] = $_POST['photo_id'];
            $this->set('output', json_encode($return));
            $this->render('ajax');
        } else {
            if (count($_SESSION['photos']) == 0) {
                $this->set('errorMessage', 'No photos are queued for import. Please add some photos and return to this page.');
            } else {
                if (isset($_SESSION['quick'])) {
                    $this->set('quick', $_SESSION['quick']);
                    $_SESSION['quick'] = null;
                }

                foreach ($_SESSION['photos'] as $photo) {
                    $flickr = $this->flickr->photos_getInfo($photo);
                    $tags = array();
                    foreach ($flickr['tags']['tag'] as $tag) {
                        $tags[] = $tag['raw'];
                    }
                    $array[$flickr['id']] = array('title' => $flickr['title'], 'id' => $flickr['id'], 'url' => $this->flickr->buildPhotoURL($flickr, "small"), 'description' => strip_tags($flickr['description']), 'flickr_url' => $flickr['urls']['url'][0]['_content'], 'date_taken' => "Taken on " . date("M. jS, Y \a\\t H:i", strtotime($flickr['dates']['taken'])), 'tags' => implode(",", $tags));
                }
                $this->set('photos', $array);
                $array = array();

                $photosets = $this->flickr->photosets_getList();
                foreach ($photosets['photoset'] as $photoset) {
                    $array[$photoset['id']] = array('title' => $photoset['title'], 'id' => $photoset['id'], 'description' => $photoset['description']);
                }
                $this->set('photosets', $array);
                $array = array();

                $facebook_albums = $this->facebook->api("/me/albums", 'get', array('limit' => '1000'));
                foreach ($facebook_albums['data'] as $album) {
                    $array[$album['id']] = array('name' => $album['name'], 'aid' => $album['id']);
                }
                $this->set('facebook_albums', $array);
                $array = array();

                $accounts = $this->facebook->api("/me/accounts");
                foreach ($accounts['data'] as $key => $value) {
                    if (isset($value['access_token']) && !empty($value['name'])) {
                        $page_albums = $this->facebook->api("/" . $value['id'] . "/albums", array('limit' => 1000));

                        foreach ($page_albums['data'] as $album_key => $album_value) {
                            if ($album_value['type'] == 'normal')
                                $page_album_array[$album_value['id']] = array('name' => $album_value['name'], 'aid' => $album_value['id']);
                        }


                        $array[$value['id']] = array('id' => $value['id'], 'albums' => $page_album_array, 'name' => $value['name'] . " [" . $value['category'] . "]");
                        $page_album_array = array();
                    }
                }
                $this->set('page_albums', $array);
                $array = array();
            }
        }
    }

}

?>
