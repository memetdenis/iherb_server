<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'class.php';
include_once 'load.php';

$json = [];
$data = [];
$data['type'] = 0;
if (isset($_GET['type'])){
    $data['type'] = (int)($_GET['type']);
}

if($data['type']==2){
    $data['html'] = anticrack($_POST['html']);
    if($data['html']!=''){
        //save_HTML($data);
        $json = Job_html($data);
    }
}elseif($data['type']==3){
    $data['html'] = anticrack($_POST['html']);
    if($data['html']!=''){
        //save_HTML($data);
        $json = Job_html2($data);
    }
}elseif($data['type']==4){
    $data['html']       = anticrack($_POST['html']);
    $data['catalog']    = anticrack($_POST['catalog']);
    if($data['html']!=''){
        //save_HTML($data);
        $json = Job_html3($data);
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

    $product = [];
    $product_load = load_HTML($value['html'], $value['type']);

    $product_load['language']    = $_POST['language'];
    $product_load['currency']    = $_POST['currency'];
    $product_load['country']     = $_POST['country'];
    $product_load['symbol']      = search_symbol($_POST['currency']);    
    save_product2($product_load);

    $product                    = search_iherb_id($product_load['product_id'], $_POST['language']);
    
    $product['product_load']    = $product_load;

    $product['price']       = Price::product_id($product['id'], $_POST['currency'], $_POST['country']);
    $product['language']    = $_POST['language'];
    $product['currency']    = $_POST['currency'];
    $product['symbol']      = search_symbol($_POST['currency']);
    $product['rating']      = Rating::view_changes($product['id']);
    return $product;

}


function Job_html3($value){

    $product = [];
    $product_load = load_HTML($value['html'], $value['type']);

    $product_load['language']   = $_POST['language'];
    $product_load['currency']   = $_POST['currency'];
    $product_load['country']    = $_POST['country'];
    $product_load['symbol']     = search_symbol($_POST['currency']);  
    $product_load['category']   = search_category_HTML($value['catalog'], $product_load);
    save_product2($product_load);

    $product                    = search_iherb_id($product_load['product_id'], $_POST['language']);
    
    $product['product_load']    = $product_load;
    $product_load['id']         = $product['id'];
    save_catalog($product_load);

    $product['price']       = Price::product_id($product['id'], $_POST['currency'], $_POST['country']);
    $product['language']    = $_POST['language'];
    $product['currency']    = $_POST['currency'];
    $product['symbol']      = search_symbol($_POST['currency']);
    $product['rating']      = Rating::view_changes($product['id']);
    return $product;

}

function Job_html2($value){

    $product = [];
    $product_load = load_HTML2($value['html'], $value['type']);

    // Проверка на заполнение
    if(!isset($product_load['sku'])){ return $product_load;}
    if($product_load['sku']==''){ return $product_load;}

    $product_load['language']    = $_POST['language'];
    $product_load['currency']    = $_POST['currency'];
    $product_load['country']     = $_POST['country'];
    $product_load['symbol']      = search_symbol($_POST['currency']);
    $product_load['price']       = clear_symbol($product_load['price'],$product_load['symbol']);
    save_product2($product_load);

    $product                = search_iherb_id($product_load['product_id'], $_POST['language']);
    $product['product_load']= $product_load;
    $product['price']       = Price::product_id($product['id'], $_POST['currency'], $_POST['country']);
    $product['language']    = $_POST['language'];
    $product['currency']    = $_POST['currency'];
    $product['symbol']      = search_symbol($_POST['currency']);
    $product['rating']      = Rating::view_changes($product['id']);
    
    return $product;

}



if($data['type']==0){
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