<?php

include_once 'class.php';

SQL::connect();

if(isset($_GET['catalog'])){ 
    $id = Other::anticrack($_GET['catalog']);

    if(isset($_GET['only_price'])){ 
        $only_price = TRUE;
    }else{
        $only_price = FALSE;
    }

    CSV::start($id, $only_price);
}

?>