<?php
/* SVN FILE: $Id: database.php.default 8004 2009-01-16 20:15:21Z gwoo $ */
class DATABASE_CONFIG {

    var $development = array(
        'driver' => 'mysqli',
		'persistent' => false,
		'host' => 'localhost',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => '',
    );
    var $production = array(
        'driver' => 'mysqli',
		'persistent' => false,
		'host' => 'localhost',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => '',
    );
    var $default = array();

    function __construct()
    {
        $this->default = ($GLOBALS['development'] == true) ?
            $this->development : $this->production;
    }
    function DATABASE_CONFIG()
    {
        $this->__construct();
    }
}
?>
