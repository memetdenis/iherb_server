<?php
	date_default_timezone_set('Europe/Moscow');
    
    include_once 'connect.php';

	spl_autoload_register(function ($class_name) {
		include 'class/' . $class_name . '.php';
	});

?>