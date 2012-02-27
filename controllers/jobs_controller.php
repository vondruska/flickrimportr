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

App::import('Helper', 'Time');

class JobsController extends AppController {

    var $time;
    var $View;

    function beforeFilter() {
        parent::beforeFilter();
        $this->time = new TimeHelper();

        $viewClass = $this->view;

        if ($viewClass != 'View') {
            if (strpos($viewClass, '.') !== false) {
                list($plugin, $viewClass) = explode('.', $viewClass);
            }
            $viewClass = $viewClass . 'View';
            App::import('View', $this->view);
        }
        $this->View = new $viewClass($this->Controller, false);

        if (!isset($_SESSION['time_offset'])) {
            //$response = $this->facebook->api_client->users_getInfo($this->user, 'timezone');
//				$_SESSION['time_offset'] = ($response[0]['timezone']) * 60 * 60;
            $_SESSION['time_offset'] = 0;
        }
    }

    function index() {
        $completed = array();
        $uncompleted = array();
        $ajax_jobs = array();

        $jobs = $this->Job->find('all', array('conditions' => array('User.id' => $this->user['id'], 'Job.status >' => 0), 'order' => array('Job.completed DESC')));
        foreach ($jobs as $job) {
            if (is_numeric($job['Job']['import_object_id'])) {
                $data = $this->facebook->api("/" . $job['Job']['import_object_id']);
                $job['Job']['import_object_name'] = $data['name'];
            } else {
                $job['Job']['import_object_name'] = "Your Profile";
            }

            if ($job['Job']['status'] == 3 || $job['Job']['status'] == 6) {
                $completed[] = $job;
            } else {
                $uncompleted[] = $job;

                if ($job['Job']['status'] == 4 || $job['Job']['status'] == 1)
                    $ajax_jobs[] = $job['Job']['id'];
            }
        }
        $this->set('completed', $completed);
        $this->set('ajax_jobs', $ajax_jobs);
        $this->set('uncompleted', $uncompleted);
    }

    function getStatus() {
        $jobs = $_POST['jobs'];
        $jobs_array = explode(",", $jobs);
        $i = 0;
        $return['active_jobs'] = array();
        foreach ($jobs_array as $job) {
            $job_result = $this->Job->find('all', array('conditions' => array('Job.id' => $job, 'Job.status >' => 0)));

            // simple security check
            // prevent end user from requesting status on jobs they don't own
            if ($this->user['id'] != $job_result[0]['Job']['user_id'])
                continue;

            if (is_numeric($job_result[0]['Job']['import_object_id'])) {
                $data = $this->facebook->api("/" . $job_result[0]['Job']['import_object_id']);
                $job_result[0]['Job']['import_object_name'] = $data['name'];
            } else {
                $job_result[0]['Job']['import_object_name'] = "Your Profile";
            }
            $return['jobs'][$i]['html'] = str_replace("&", "&amp;", $this->View->element('jobs/status', array('job' => $job_result[0], 'time' => $this->time)));
            $return['jobs'][$i]['md5'] = md5($job_result[0]['Job']['id']);
            $return['jobs'][$i]['id'] = $job_result[0]['Job']['id'];
            $return['jobs'][$i]['status'] = $job_result[0]['Job']['status'];
            $return['jobs'][$i]['percentage'] = $job_result[0]['Job']['percentage'];
            if ($job_result[0]['Job']['status'] == 1 || $job_result[0]['Job']['status'] == 4)
                $return['active_jobs'][] = $job_result[0]['Job']['id'];
            $i++;
        }
        $this->set('output', json_encode($return));
        $this->layout = 'ajax';
        $this->viewPath = 'ajax';
        $this->render('ajax');
    }

    function stop() {
        $jobid = $this->params['form']['jobid'];

        $job_result = $this->Job->find('all', array('conditions' => array('Job.id' => $job)));
        // simple security check
        // prevent end user from requesting status on jobs they don't own
        if ($this->user['id'] == $job_result[0]['Job']['user_id']) {
            $this->Process->stop($jobid);
            $return['html'] = str_replace("&", "&amp;", $this->View->element('jobs/status', array('job' => $job_result[0], 'time' => $this->time)));
            $return['md5'] = md5($job_result[0]['Job']['id']);

            $this->set('output', json_encode($return));
            $this->layout = 'ajax';
            $this->viewPath = 'ajax';
            $this->render('ajax');
        }
    }

    function start() {
        $jobid = $this->params['form']['jobid'];


        $job_result = $this->Job->find('all', array('conditions' => array('Job.id' => $job)));

        if ($this->user['id'] == $job_result[0]['Job']['user_id']) {
            $this->Process->start($jobid);

            $return['html'] = str_replace("&", "&amp;", $this->View->element('jobs/status', array('job' => $job_result[0], 'time' => $this->time)));
            $return['md5'] = md5($job_result[0]['Job']['id']);

            $this->set('output', json_encode($return));
            $this->layout = 'ajax';
            $this->viewPath = 'ajax';
            $this->render('ajax');
        }
    }

    function restart() {
        $jobid = $this->params['form']['jobid'];

        $job_result = $this->Job->find('all', array('conditions' => array('Job.id' => $jobid)));

        if ($this->user['id'] == $job_result[0]['Job']['user_id']) {

            $this->Photo->updateAll(array('Photo.status' => 1), array('Job.id' => $jobid));
            $this->Job->updateAll(array('Job.album_id' => 0), array('Job.id' => $jobid));
            $this->Process->start($jobid);


            $return['html'] = str_replace("&", "&amp;", $this->View->element('jobs/status', array('job' => $job_result[0], 'time' => $this->time)));
            $return['md5'] = md5($job_result[0]['Job']['id']);

            $this->set('output', json_encode($return));
            $this->layout = 'ajax';
            $this->viewPath = 'ajax';
            $this->render('ajax');
        }
    }

    function issue() {
        $jobid = $this->params['form']['jobid'];

        $job_result = $this->Job->find('all', array('conditions' => array('Job.id' => $jobid)));
        $this->createIssue('Job Issue - ' . $jobid, 'Job ID:' . $jobid . '

Additional Information: ' . $_POST['info'] . '

Submitting User ID:' . $this->user . '

Job: ' . print_r($job_result, true) . '

Session: ' . print_r($_SESSION, true));
        $this->Job->updateAll(array('Job.reported' => 1), array('Job.id' => $jobid));
        $this->set('output', json_encode(array('status' => 'completed')));
        $this->layout = 'ajax';
        $this->viewPath = 'ajax';
        $this->render('ajax');
    }

    function delete() {
        $jobid = $this->params['form']['jobid'];
        $job_result = $this->Job->find('all', array('conditions' => array('Job.id' => $jobid)));

        if ($this->user['id'] == $job_result[0]['Job']['user_id']) {
            $this->Job->updateAll(array('Job.status' => 0), array('Job.id' => $jobid));
            $this->set('output', json_encode(array('status' => 'completed', 'md5' => md5($jobid))));
            $this->layout = 'ajax';
            $this->viewPath = 'ajax';
            $this->render('ajax');
        }
    }

}

?>
