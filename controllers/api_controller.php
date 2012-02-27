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
 */

Configure::write('debug', 0);
class ApiController extends AppController {
		var $uses = array();
		var $secret = "";
		function beforeFilter() {
			$this->RequestHandler->setContent('json', 'text/x-json');
		}
		
		function post() {
			if(!isset($_POST['microtime']) && !isset($_POST['hash']) && md5($this->secret . $_POST['microtime']) != $_POST['hash']) {
				header("HTTP/1.0 500 Server Error");
				$this->set('return', array('status' => 'error', 'message' => 'Invalid key'));
			}
			elseif(!method_exists($this, $_POST['method'])) {
				$this->set('return', array('status' => 'error', 'message' => 'Invalid method'));
			}
			else {
				$this->set('return', $this->$_POST['method']());
			}
		}
		
		function numberofimports() {
			return array('status' => 'ok', 'number' => $this->Photo->find('count') + 54540);
		}
		
		function recentimports() {
			$photos = $this->Photo->query("(
				SELECT DISTINCT photos.flickr_id, users.flickr_name, photos.flickr_url
				FROM photos
				LEFT JOIN jobs ON jobs.id = photos.job_id
				LEFT JOIN users ON users.id = jobs.user_id
				WHERE users.display_photos = 1 AND photos.flickr_url != ''
			)
			ORDER BY RAND( )
			LIMIT 50");
			return array('status' => 'ok', 'photos' => $photos);
		}
	}
?>