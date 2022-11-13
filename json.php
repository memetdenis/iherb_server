<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'connect.php';
include_once 'load.php';

$json = [];
$data = [];
$data['type'] = 0;
if (isset($_GET['type'])){
    $data['type'] = (int)($_GET['type']);
}

if($data['type']==1){
    $data['name']           = anticrack($_POST['name']);
    $data['specifications'] = anticrack($_POST['specifications']);
    $data['rank']           = anticrack($_POST['rank']);
    $data['image']          = anticrack($_POST['image']);
    $data['pricing']        = anticrack($_POST['pricing']);
    $data['content']        = anticrack($_POST['content']);
    $data['meta']           = anticrack($_POST['meta']);
    $data['language']       = anticrack($_POST['language']);
    
    if($data['name']!=''){
        saveValue($data);
    }

    load();
}elseif($data['type']==2){
    $data['html'] = anticrack($_POST['html']);
    if($data['html']!=''){
        save_HTML($data);
        $json = Job_html($data);
    }
}elseif($data['type']==3){
    $data['html'] = anticrack($_POST['html']);
    if($data['html']!=''){
        save_HTML($data);
        $json = Job_html2($data);
    }
}

function anticrack ($var){
    $var=str_replace("'","`",$var);
    $var=str_replace('"','`',$var);
    //$var=trim(htmlspecialchars($var));
    $var=trim($var);
    return ($var);
}

function saveValue($value){
    SQLQ("INSERT INTO `load` (`name`, `pricing`, `image`, `specifications`, `rank`, `content`, `meta`, `language`, `type`) VALUES ('{$value['name']}', '{$value['pricing']}', '{$value['image']}', '{$value['specifications']}', '{$value['rank']}', '{$value['content']}', '{$value['meta']}', '{$value['language']}', '{$value['type']}');");
}

function save_HTML($value){
    SQLQ("INSERT INTO `html_load` (`html`, `type`) VALUES ('{$value['html']}', '{$value['type']}');");
}

function Job_html($value){

    $product = load_HTML($value['html'], $value['type']);

    // Проверка на заполнение
    if(!isset($product['sku'])){ return $product;}
    if($product['sku']==''){ return $product;}
    //print_r($product);
    $product['language']    = $_POST['language'];
    $product['currency']    = $_POST['currency'];
    $product['symbol']      = search_symbol($product['currency']);
    save_product2($product);

    $product['product']     = search_product_id($product['product_id'], $product['language']);
    //$product['price']       = search_price($product['product']['sku'], $product['currency']);
    return $product;

}

function Job_html2($value){

    $product = load_HTML2($value['html'], $value['type']);

    // Проверка на заполнение
    if(!isset($product['sku'])){ return $product;}
    if($product['sku']==''){ return $product;}

    $product['language']    = $_POST['language'];
    $product['currency']    = $_POST['currency'];
    $product['symbol']      = search_symbol($_POST['currency']);
    $product['price']       = clear_symbol($product['price'],$product['symbol']);
    save_product2($product);

    $product                = search_product_id($product['product_id'], $_POST['language']);
    $product['price']       = search_price($product['sku'], $_POST['currency']);
    
    return $product;

}


if($data['type']==1){
    if (isset($_POST['product_id'])){
        $json['product_id'] = $_POST['product_id'];
        $json['currency']   = $_POST['currency'];
        $json['symbol']     = '$';
        $json['language']   = $_POST['language'];
        $json['product']    = search_product_id($json['product_id'], $json['language']);
        $json['price']      = search_price($json['product']['sku'], $json['currency']);
    }
}elseif($data['type']==2){
    //$data['html']           = anticrack($_POST['html']);
}elseif($data['type']==3){
    //$data['html']           = anticrack($_POST['html']);
}else{
    $json['POST'] = $_POST;
    $json['GET'] = $_GET;
}

if (isset($_GET['id'])){
    $json['id'] = (int)($_GET['id']);
}

echo json_encode($json);
//print_r($_POST);
//print_r($_GET);
?>