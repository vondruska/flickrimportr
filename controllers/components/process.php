<?php
	class ProcessComponent extends Object {
		public $cakeConsole = null;
		public $controller = null;
		
		function initialize(&$controller, $settings = array()) {
			// saving the controller reference for later use
			$this->controller =& $controller;
                        $this->cakeConsole = $GLOBALS['cakeConsole'];
		}

		function start($jobid) {
			$this->controller->Job->updateAll(array('Job.status' => 1), array('Job.id' => $jobid));
			echo $this->run('job', array('bg' => true, 'params'=>array('jobid' => $jobid)));
			return true;
		}
		
		function stop($jobid) {
			$this->controller->Job->updateAll(array('Job.status' => 2), array('Job.id' => $jobid));
			return true;
		}
		
		function check($jobid) {
			exec("ps $pid", $ProcessState);
			return(count($ProcessState) >= 2);
		}
        
		/**
		* Shell Component
		*
		* Run cakephp shell scripts
		*
		* Depending on your operating system check the background process append and prepend variables.
		* This script works fine on Ubuntu 8.x - it won't work on Windows unless changed to do so.
		*
		* @version       0.1 beta
		* @author        Darren Moore, zeeneo@gmail.com
		* @link          http://www.zeen.co.uk
		 * Run shell command
		 *
		 * Example:
		 * $this->Shell->run('myshell',array('task'=>'crawler','bg'=>true,'params'=>array('feedId'=>123,'batchId'=>345)));
		 *
		 * Options
		 * - 'task' - Task to run
		 * - 'params' - Params to pass to shell
		 * - 'bg' - If script should run in background
		 *
		 * @param string $shell Shell command
		 * @param array $options Options
		 * @access public
		 * @return string
		 */
		private function run($shell,$options = array())
        {
            //Build command
            $cmd = array($shell);
            $append = '';
            $prepend = '';
			$appDir = ROOT . DS . APP_DIR;
            
            //Task
            if(isset($options['task']) && !empty($options['task'])) { $cmd[] = $options['task']; }
        
            //Params
            if(isset($options['params']) && !empty($options['params']))
            {
                foreach($options['params'] as $key => $val)
                {
                    $cmd[] = '-'.$key.' '.$val;
                }
            }

            //Background
            if(isset($options['bg']) && $options['bg'] == true)
            {
                $prepend = 'nohup ';
                $append = ' > /dev/null &';
            }
            
            
            $cmd = $prepend.$GLOBALS['phpPath'].' '.$this->cakeConsole.' -working '.$appDir.' '.implode(' ',$cmd).$append;
$this->log($cmd);
            return exec($cmd);
        }
	}
?>
