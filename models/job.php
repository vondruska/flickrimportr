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

class Job extends AppModel {

    var $hasMany = 'Photo';
    var $belongsTo = 'User';
    var $status = array(0 => 'Deleted', 1 => 'Starting Import', 2 => 'Stopped', 3 => 'Completed', 4 => 'Importing...', 5 => 'Error', 6 => 'Completed With Errors');

    function afterFind($results) {
        foreach ($results as $key => $val) {
            foreach ($val as $inner_key => $inner_val) {
                if ($inner_key == "Photo") {
                    $percentage = 0;
                    $photos_completed = 0;
                    foreach ($inner_val as $photo) {
                        if ($photo['status'] == 2)
                            $photos_completed++;
                    }

                    $results[$key]['Job']['total'] = count($results[$key]['Photo']);
                    $results[$key]['Job']['photos_completed'] = $photos_completed;
                    $results[$key]['Job']['percentage'] = round($photos_completed / count($results[$key]['Photo']) * 100, 2);
                }
                if ($inner_key == "Job") {
                    $results[$key]['Job']['nice_status'] = (isset($results[$key]['Job']['status'])) ? $this->status[$results[$key]['Job']['status']] : "";
                }
            }
        }
        return $results;
    }

}

?>