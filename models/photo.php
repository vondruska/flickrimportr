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

class Photo extends AppModel {

    var $belongsTo = 'Job';

}

function recentimports() {
    return $this->query("
			(
				SELECT users.flickr_user, photos.flickr_url
				FROM photos
				LEFT JOIN jobs ON jobs.id = photos.job_id
				LEFT JOIN users ON users.id = jobs.user_id
				WHERE users.display_photos = 1
			)
			UNION ALL 
			(
				SELECT users.flickr_user, imports.flickr_url
				FROM imports
				LEFT JOIN users ON users.id = imports.user_id
				WHERE users.display_photos = 1
			)
			ORDER BY RAND( ) 
			LIMIT 50
		");
}

?>