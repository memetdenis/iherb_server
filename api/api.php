<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Замерим скорость работы
$time_start = microtime(true);

// Пустой массив для данных
$json_data = array();

include_once 'class.php';

SQL::connect();

Route::GET();

Json::replace(round(microtime(true) - $time_start,4),'time_execution'); // Время выполнения скрипта
Json::replace(time(),'time'); // Текущее время сервера
Json::public();

?>