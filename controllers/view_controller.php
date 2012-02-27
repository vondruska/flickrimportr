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

class ViewController extends AppController {

    function photosets() {
        $id = isset($this->params['id']) ? $this->params['id'] : 0;

        if ($id == null || $id == 0) {
            $this->set('photosets', $this->flickr->photosets_getList());
        } else {
            $page = (isset($this->params['page']) && is_numeric(str_replace("page-", "", $this->params['page']))) ? str_replace("page-", "", $this->params['page']) : 1;
            $photoset = $this->flickr->photosets_getInfo($id);
            if ($photoset['owner'] == $this->flickr_user)
                $flickr = $this->flickr->photosets_getPhotos($id, 'url_sq', null, null, $page);
            else
                $this->set('errorMessage', 'It appears you don\'t own this photoset. Please choose a photoset you created');

            $this->set('photos', $flickr['photoset']['photo']);
            $this->set('pages', $flickr['photoset']['pages']);
            $this->set('perpage', ($flickr['photoset']['perpage'] > $flickr['photoset']['total']) ? $flickr['photoset']['total'] : $flickr['photoset']['perpage']);
            $this->set('total', $flickr['photoset']['total']);
            $this->set('page', $page);
            $this->set('type', eregi_replace("/page-[0-9]*$", "", $this->params['url']['url']));
            $this->render('photos');
        }
    }

    function photos() {
        $page = (isset($this->params['page']) && is_numeric(str_replace("page-", "", $this->params['page']))) ? str_replace("page-", "", $this->params['page']) : 1;
        $photos = $this->flickr->photos_search(array("user_id" => "me", "per_page" => 63, "page" => $page, "extras" => 'url_sq'));

        // creating stack... to use to allow users to uncheck their selections
        $_SESSION['stack'] = array();
        foreach ($photos['photo'] as $photo) {
            $_SESSION['stack'][] = $photo['id'];
        }

        $this->set('photos', $photos['photo']);
        $this->set('pages', $photos['pages']);
        $this->set('perpage', ($photos['perpage'] > $photos['total']) ? $photos['total'] : $photos['perpage']);
        $this->set('total', $photos['total']);
        $this->set('page', $page);
        $this->set('type', eregi_replace("/page-[0-9]*$", "", $this->params['url']['url']));
    }

    function submit() {
        if (isset($this->params['id'])) { // quick import
            $_SESSION['photos'] = array();
            $photoset = $this->flickr->photosets_getInfo($this->params['id']);
            $_SESSION['quick'] = array('id' => $this->params['id'], 'name' => $photoset['title'], 'description' => $photoset['description']);
            // check to make sure user actually owns the set they are trying to import
            if ($photoset['owner'] == $this->flickr_user) {
                $flickr = $this->flickr->photosets_getPhotos($this->params['id']);

                foreach ($flickr['photoset']['photo'] as $photo) {
                    $photosetPhotos[] = $photo['id'];
                }
            } else {
                $this->set('errorMessage', 'It appears you don\'t own this photoset. Please choose a photoset you created');
                $this->render('review');
            }
        }

        $stack = $_SESSION['stack'];

        // if quick import was used, use the quick import information, otherwise check the post array
        if (isset($this->params['form']['photos'])) {
            $photos = $this->params['form']['photos'];
        } else {
            $photos = $photosetPhotos;
        }

        // loop through posted photos and compare to the photos that were posted
        foreach ($photos as $photo) {
            // if not already in photos array, add to photos array and remove from stack
            if (!in_array($photo, $_SESSION['photos'])) {
                $_SESSION['photos'][] = $photo;
            }
            unset($stack[array_search($photo, $stack)]);
        }

        // loop through photos left in the stack, if exists in photo queue, remove from queue
        foreach ($stack as $photo) {
            $key = array_search($photo, $_SESSION['photos']);
            if ($key)
                unset($_SESSION['photos'][$key]);
        }

        $_SESSION['stack'] = array();  //reset stack


        if (isset($this->params['form']['page_number']) && is_numeric($this->params['form']['page_number'])) {
            $redirect = "/" . $GLOBALS['appPath'] . "/" . $_POST['type'] . "/page-" . $this->params['form']['page_number'];
        } else {
            $redirect = "/" . $GLOBALS['appPath'] . "/import/build";
        }

        $this->redirect($redirect);
        $this->layout = 'ajax';
        $this->viewPath = 'ajax';
        $this->render('ajax');
    }

}

?>