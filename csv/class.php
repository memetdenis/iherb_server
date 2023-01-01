<?php
	date_default_timezone_set('Europe/Moscow');
    
	spl_autoload_register(function ($class_name) {
		include 'class/' . $class_name . '.php';
	});

?>