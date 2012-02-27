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

class JobShell extends Shell {

    var $jobId;
    var $user;
    var $uses = array('Job', 'Photo', 'User');
    var $album_id;
    var $photos_completed;
    var $job_size;
    var $maxAlbumSize = 200;
    var $tmp_folder_base;
    var $tmp_folder_path;
    var $access_token;
    var $import;

    function main() {

        // Create a Facebook client API object and require login
        $this->facebook = new Facebook(array(
                    'appId' => FACEBOOK_APP_ID,
                    'secret' => FACEBOOK_SECRET,
                    'fileUpload' => true
                ));

        // Create a flickr api object
        $this->flickr = new phpflickr($GLOBALS['flickrApiKey'], $GLOBALS['flickrSecret']);

        $this->jobId = $this->params['jobid']; //set job id
        // set folder paths
        $this->tmp_folder_base = APP_PATH . "tmp" . DS . "jobs";
        $this->tmp_folder_path = $this->tmp_folder_base . DS . $this->jobId;

        // store job information and check to see if its valid
        $database = $this->Job->find('all', array('conditions' => array('Job.id' => $this->jobId)));
        if (count($database) == 0) {
            $this->out('Invalid job id');
            exit();
        }

        $this->flickr->setToken($database[0]['User']['flickr_auth']); // set the flickr auth


        $this->Job->updateAll(array('Job.pid' => getmypid(), 'Job.status' => 4), array('Job.id' => $this->jobId)); // set pid and "started" status
        $this->import = $database[0];

        if (count($database[0]['Photo']) == 0)
            $this->out('No photos to import');

        $this->checkTempFolder(); // see if temp folder is created for images

        foreach ($database[0]['Photo'] as $key => $photo) {
            //stop job in case user cancelled it or something went wrong to cancel job
            $status = $this->Job->find('all', array('conditions' => array('Job.id' => $this->jobId)));
            if ($status[0]['Job']['status'] != 1 && $status[0]['Job']['status'] != 4 && $status[0]['Job']['status'] != 6) {
                $this->out('Job status does not allow job to run. Job Status: ' . $status[0]['Job']['status']);
                exit();
            }

            // check to see if photo was imported already, if so skip it, in case a user is resuming a job
            if ($photo['status'] != 2) {
                $photo_sizes = $this->flickr->photos_getSizes($photo['flickr_id']); // get the avaliable photo sizes
                $this->log("Photo sizes found");
                if ($this->flickr->getErrorCode() == 1) {  // check to see if flickr said that it is missing the photo, if so, skip the photo
                    $this->Photo->updateAll(array('Photo.status' => 3), array('Photo.id' => $photo['id']));
                } else {
                    $photo_desc = $photo['description']; // set photo description

                    $sizes = array(); // reset sizes array right before we write to it to prevent duplicates

                    foreach ($photo_sizes as $size) {
                        $sizes[$size['label']] = $size['source']; // create an array with the URLs and sizes as keys
                    }

                    // test each image size to see which one exists
                    if (isset($database[0]['User']['use_originals']) && $database[0]['User']['use_originals'] == 1) {
                        if (isset($sizes['Original']))
                            $photo_url = $sizes['Original'];
                        elseif (isset($sizes['Large']))
                            $photo_url = $sizes['Large'];
                        else
                            $photo_url = $sizes['Medium'];
                    }
                    else {
                        if (isset($sizes['Large']))
                            $photo_url = $sizes['Large'];
                        else
                            $photo_url = $sizes['Medium'];
                    }
                    $this->log("Size to be uploaded figured out");

                    // see if the album id is set, if not get it set...
                    if (!isset($this->album_id)) {
                        $this->log("Album ID not set locally");
                        if ($database[0]['Job']['album_id'] && $this->checkAlbum($database[0]['Job']['album_id'])) {
                            $this->album_id = $database[0]['Job']['album_id'];
                            $this->log("Album ID found in database, using");
                        } else {
                            $this->log("Working to create album on Facebook");
                            $this->createAlbum($database[0]['Job']['title'], $database[0]['Job']['location'], $database[0]['Job']['description'], null, $database[0]['Job']['album_privacy']);
                            $this->log("Created album on Facebook since no album ID was found");
                        }
                    }

                    // try the import
                    try {
                        $this->log("Attempting upload to Facebook...");
                        $photo_details = array('message' => stripslashes($photo_desc), 'access_token' => $this->import['Job']['access_token']);
                        $photo_details['image'] = '@' . $this->getImage($photo_url);
                        $facebook_photo = $this->facebook->api('/' . $this->album_id . '/photos', 'post', $photo_details);
                        $this->log($facebook_photo);
                        $this->log("Done");
                    } catch (Exception $e) {
                        $this->log("caught problem: " . $e);
                    } catch (FacebookRestClientException $e) {

                        // if album is full
                        if ($e->getCode() == 321) {
                            $this->createAlbum($database[0]['Job']['title'], $database[0]['Job']['location'], $database[0]['Job']['description'], true, $database[0]['Job']['album_privacy']);
                            //$this->facebook->api_client->photos_upload($this->getImage($photo_url), $this->album_id, stripslashes($photo_desc), $this->user); // upload photo from flickr to facebook
                        }
                        // for whatever reason the album id is invalid, upload into the application album
                        elseif ($e->getCode() == 120) {
                            //$this->facebook->api_client->photos_upload($this->getImage($photo_url), null, stripslashes($photo_desc), $this->user);
                        }
                        // unknown error... try it one more time than error out
                        elseif ($e->getCode() == 1) {
                            try {
                                usleep(2000000);
                                //$facebook_photo = $this->facebook->api_client->photos_upload($this->getImage($photo_url), $this->album_id, stripslashes($photo_desc), $this->user);
                            } catch (FacebookRestClientException $e) {
                                $this->Job->updateAll(array('Job.error' => "'" . $e->getCode() . ": " . $e->getMessage() . "'", 'Job.status' => 5), array('Job.id' => $this->jobId));
                                exit();
                            }
                        }
                        // who knows what happened....
                        else {
                            $this->Job->updateAll(array('Job.error' => "'" . $e->getCode() . ": " . $e->getMessage() . "'", 'Job.status' => 5), array('Job.id' => $this->jobId));
                            exit();
                        }
                    }

                    $this->Photo->updateAll(
                            array('Photo.status' => 2,
                        'Photo.flickr_url' => '"' . $photo_url . '"',
                        'Photo.completed' => 'NOW()',
                        'Photo.aid' => '"' . $this->album_id . '"',
                        'Photo.pid' => '"' . $facebook_photo['id'] . '"'
                            ), array('Photo.id' => $photo['id']));
                }
                usleep(500000); // introduce delay to prevent API limits and server stress
            }
        }

        $results = $this->Job->find('all', array('conditions' => array('Job.id' => $this->jobId)));

        $importError = false;
        foreach ($results[0]['Photo'] as $photo) {
            if ($photo['status'] == 3)
                $importError = true;
        }

        if ($importError) { // check to see if any photos that we imported in this job were invalid, if so notify the user
            $this->Job->updateAll(array('Job.status' => 6, 'Job.completed' => 'NOW()', 'Job.error' => '"Some of the photos you imported are not accessible on Flickr."'), array('Job.id' => $this->jobId));
        } else {
            $this->Job->updateAll(array('Job.status' => 3, 'Job.completed' => 'NOW()'), array('Job.id' => $this->jobId));
        }

        // remove temp directory
        foreach (scandir($this->tmp_folder_path) as $item) {
            if ($item == '.' || $item == '..')
                continue;
            unlink($this->tmp_folder_path . DS . $item);
        }
        rmdir($this->tmp_folder_path);
    }

    private function createAlbum($name, $location, $description, $multiple = false, $visible = "EVERYONE") {
        $this->log("Creating album function");
        try {
            $album = $this->facebook->api('/' . $this->import['Job']['import_object_id'] . '/albums', 'post', array(
                        'access_token' => $this->import['Job']['access_token'],
                        'name' => $this->import['Job']['title'],
                        'message' => $this->import['Job']['description']
                            )
            );
        } catch (FacebookRestClientException $e) { // couldn't create an album, and facebook doesn't return enough information to recover from this issue, die
            $this->Job->updateAll(array('Job.error' => "'" . $e->getCode() . ": " . $e->getMessage() . "'", 'Job.status' => 5), array('Job.id' => $this->jobId));
        } catch (Exception $e) {
            $this->log($e);
        }

        $album = $this->facebook->api("/" . $album['id'], array('access_token' => $this->import['Job']['access_token']));

        $this->Job->updateAll(array('Job.album_id' => $album['id'], 'Job.album_link' => "\"" . $album['link'] . "\""), array('Job.id' => $this->jobId));
        $this->album_id = $album['id'];
    }

    // download image from path, and save it to temp directory, and return path
    private function getImage($url) {
        $ext = substr($url, strrpos($url, '.') + 1);

        $filename = md5($url) . "." . $ext;
        $folder = $this->tmp_folder_path;
        $fullpath = $folder . DS . $filename;

        if (!file_exists($fullpath)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $rawdata = curl_exec($ch);
            curl_close($ch);

            $fp = fopen($fullpath, 'x');
            fwrite($fp, $rawdata);
            fclose($fp);
            if (!chmod($fullpath, 0777))
                $this->log("Unable to set permissions");
        }

        return $fullpath;
    }

    private function checkTempFolder() {
        if (!is_dir($this->tmp_folder_path)) {
            mkdir($this->tmp_folder_path);
        }

        return true;
    }

    private function checkAlbum($aid) {
        return true;
        // TODO: ensure album in actually created. unreliable results from graph API
    }

}

?>
