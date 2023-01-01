<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'class.php';
include_once 'load.php';

SQL::connect();

//require_once 'phpQuery/phpQuery.php';

$json_data = [];
$data = [];
$data['type'] = 0;    
$data['html'] = anticrack($_POST['html']);

if (isset($_GET['type'])){
    $data['type'] = (int)($_GET['type']);
}

//HTML::save($data);

if($data['type']==2){
    if($data['html']!=''){
        $json_data = Job_html($data);
    }
}elseif($data['type']==3){
    // Загружаем карточку одного товара
    if($data['html']!=''){
        $data['catalog']    = anticrack($_POST['catalog']);
        $json_data = Job_html2($data);
    }
}elseif($data['type']==4){
    if($data['html']!=''){
        $data['catalog'] = anticrack($_POST['catalog']);
        $json_data = Job_html3($data);
    }
}elseif($data['type']==5){
    // Сохраним информацию о товаре
    if($data['html']!=''){
        $product = search_sku($_POST['sku'], $_POST['language']);
        $html = trim(htmlspecialchars($data['html']));
        save_content($html, $_POST['language'], $product['iherb_id']);
    }
}elseif($data['type']==6){
    // Сохраним корзину.
    if($data['html']!=''){
        $json_data = Job_html4($data);
    }
}elseif($data['type']==7){
    // Загружаем Отзывы пользователей 
    if($data['html']!=''){
        $json_data = HTML::review_list($data);
    }
}

function anticrack ($var){
    $var=str_replace("'","`",$var);
    $var=str_replace('"','`',$var);
    //$var=trim(htmlspecialchars($var));
    $var=trim($var);
    return ($var);
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
    $product_load['category']   = HTML::search_category($value['catalog'], $product_load);

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

function Job_html4($html){

    $product = [];
    $product['symbol']     = search_symbol($_POST['currency']);
    $product['language']   = $_POST['language'];
    $product['currency']   = $_POST['currency'];
    $product['country']    = $_POST['country'];
    //$product_load = load_HTML($value['html'], $value['type']);
    $html = trim($html['html']);

    // Разобьём на div блоки и удалим пустые части
    $html_load = explode('<div class=',$html);
    foreach ($html_load as $key => $value) {
        if($value==''){
            unset($html_load[$key]);
        }else{
            if(strpos($value, '</div>') === false){
                unset($html_load[$key]);
            }else{
                $html_load[$key] = '<div class='.$value;
            }
        }
    }

    // Восстановим нумерацию массива для работы
    $html_load = array_values(array_unique($html_load));

    if(strpos($html_load[0], 'href=`') !== false){
        $product['url'] = search_HTML_atrib('href=`', $html_load[0]);
    }

    $url = explode('/',$product['url']);
    $product['product_id'] = (int)$url[count($url)-1];

    // Найдем название товара
    $product['name'] = search_HTML_atrib('aria-label=`', $html_load[0]);
    $product['brand'] = trim(substr($product['name'], 0, strpos($product['name'], ',')));

    $product['img_new'] = img_structure(search_HTML_atrib('src=`', $html_load[0]), $product['product_id']);

    $product['sku'] = explode(':',strip_tags($html_load[4]));
    $product['sku'] = trim($product['sku'][1]);

    $product['weight'] = explode(':',strip_tags($html_load[5]));
    $product['weight'] = weight_convert(trim($product['weight'][1]));

    $product['qty'] = (int)trim(strip_tags($html_load[7]));

    // Найдем цены
    $price = str_replace('</div><div>',':', $html_load[9]);
    $price = explode(':',$price);
    foreach ($price as $key => $value) {
        $price[$key] = trim(str_replace($product['symbol'],'',trim(strip_tags($value))));
    }

    if(count($price)>1){
        $product['standard_price'] = $price[0] / $product['qty'];
        $product['price']          = $price[1] / $product['qty'];
    }else{
        $product['standard_price'] = $product['price'] = $price[0] / $product['qty'];
    }

    // Сохраним
    save_product3($product);

    $product['product_load']    = $html_load;
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
    $product_load['category2']   = HTML::search_category_list($value['catalog'], $product_load);

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
    Json::replace('POST', $_POST);
    Json::replace('GET', $_GET);
}

if (isset($_GET['id'])){
    Json::replace((int)($_GET['id']), 'id');
}

Json::public();
//echo json_encode($json);
//print_r($_POST);
//print_r($_GET);
?>