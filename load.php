<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'connect.php';

//**************************************************************************************************************************************** */
// Найдем артикул
function sku($contents)
{
    $contents = trim($contents);
    $contents = substr($contents, strpos($contents, '<span itemprop=`sku`>')+21);
    return substr($contents, 0, strpos($contents, '</span>'));
}


//**************************************************************************************************************************************** */
// Найдем вес
function weight($contents)
{

    $atrib = 'product-weight`>';
    $contents = trim($contents);
    $contents = trim(str_replace('\n','',trim(substr($contents, strpos($contents, $atrib)+strlen($atrib)))));
    $contents = trim(substr($contents, 0, strpos($contents, '</span>')));

    $ar = explode(' ',$contents);
    switch(trim($ar[1])){
        case 'кг':
        case 'kg':
            return round($ar[0]*1,2);
        case 'фунтов':
        case 'lb':
            return round($ar[0]*0.453592,2);
    }

    return 0;

}

//**************************************************************************************************************************************** */
// Найдем ранги категорий
function rank($contents)
{
    $rank = [];

    $atrib = 'column fluid product-description-ranking`>';
    $contents = trim($contents);
    $contents = trim(str_replace(' ','',str_replace('\n','',trim(substr($contents, strpos($contents, $atrib)+strlen($atrib))))));
    $contents = trim(substr($contents, 0, strpos($contents, '</section>')));

    $substr_count = substr_count($contents, 'product_ranking');
    for($i = 0; $i < $substr_count; ++$i){
        $atrib = '`product_ranking`>';
        //$rank[] = substr($contents, strpos($contents, $atrib)+strlen($atrib));
        $contents = trim(substr(trim(substr($contents, strpos($contents, $atrib)+strlen($atrib))), 8));
        $rank[] = trim(substr($contents, 0, strpos($contents, '</strong>')));
    }

    return $rank;
}

function save_product($product)
{
    // Найдем товар
    $S = SQLQ("SELECT * FROM `product` WHERE `sku` LIKE '{$product['sku']}' ");
    if(!mysqli_num_rows($S)){
        SQLQ("INSERT INTO `product` (`sku`, `weight`, `product_id`, `img`) VALUES ('{$product['sku']}', '{$product['weight']}', '{$product['meta']['og:product_id']}', '{$product['img']}');");
    }

    // Найдем название товара на нужном языке
    $S = SQLQ("SELECT * FROM `product_name` WHERE `sku` LIKE '{$product['sku']}' AND `lang` LIKE '{$product['language']}'");
    if(!mysqli_num_rows($S)){
        SQLQ("INSERT INTO `product_name` (`sku`, `lang`, `name`, `brand`) VALUES ('{$product['sku']}', '{$product['language']}', '{$product['name']}', '{$product['meta']['og:brand']}');");
    }

    // Найдем название категорий
    foreach ($product['rank'] as $key => $value) {
        $S = SQLQ("SELECT * FROM `product_rank` WHERE `sku` LIKE '{$product['sku']}' AND `lang` LIKE '{$product['language']}' AND `name` LIKE '{$value}'");
        if(!mysqli_num_rows($S)){
            SQLQ("INSERT INTO `product_rank` (`sku`, `lang`, `name`) VALUES ('{$product['sku']}', '{$product['language']}', '{$value}');");
        }
    }

    // Проверим цены
    $S = SQLQ("SELECT * FROM `price` WHERE `sku` LIKE '{$product['sku']}' ORDER BY `price`.`date_create` DESC LIMIT 0,1;");
    if(mysqli_num_rows($S)>0){
        while($Q = mysqli_fetch_array($S)){
            if($Q['standard_price']!=$product['meta']['og:standard_price'] || $Q['price']!=$product['meta']['price']){
                SQLQ("INSERT INTO `price` (`sku`, `date_create`, `currency`, `standard_price`, `price`) VALUES ('{$product['sku']}', now(), '{$product['meta']['og:price:currency']}', '{$product['meta']['og:standard_price']}', '{$product['meta']['price']}');");
            }
        }
    }else{
        SQLQ("INSERT INTO `price` (`sku`, `date_create`, `currency`, `standard_price`, `price`) VALUES ('{$product['sku']}', now(), '{$product['meta']['og:price:currency']}', '{$product['meta']['og:standard_price']}', '{$product['meta']['price']}');");
    }
}

function save_product2($product)
{
    
    // Найдем товар
    $S = SQLQ("SELECT * FROM `product` WHERE `sku` LIKE '{$product['sku']}' ");
    if(!mysqli_num_rows($S)){
        SQLQ("INSERT INTO `product` (`sku`, `weight`, `product_id`, `img`) VALUES ('{$product['sku']}', '{$product['weight']}', '{$product['product_id']}', '{$product['img']}');");
    }else{
        // Проверим записан ли у нас вес товара
        if($product['weight']>0){
            while ($Q = mysqli_fetch_array($S)) {
                if($Q['weight'] == 0){
                    SQLQ("UPDATE `product` SET `weight` = '{$product['weight']}' WHERE `product`.`sku` = '{$Q['sku']}';");
                }
            }
        }
    }

    // Найдем название товара на нужном языке
    $S = SQLQ("SELECT * FROM `product_name` WHERE `sku` LIKE '{$product['sku']}' AND `lang` LIKE '{$product['language']}'");
    if(!mysqli_num_rows($S)){
        SQLQ("INSERT INTO `product_name` (`sku`, `lang`, `name`, `brand`) VALUES ('{$product['sku']}', '{$product['language']}', '{$product['name']}', '{$product['brand']}');");
    }

    // Найдем название категорий
    if (isset($product['rank'])) {
        foreach ($product['rank'] as $key => $value) {
            $S = SQLQ("SELECT * FROM `product_rank` WHERE `sku` LIKE '{$product['sku']}' AND `lang` LIKE '{$product['language']}' AND `name` LIKE '{$value}'");
            if (!mysqli_num_rows($S)) {
                SQLQ("INSERT INTO `product_rank` (`sku`, `lang`, `name`) VALUES ('{$product['sku']}', '{$product['language']}', '{$value}');");
            }
        }
    }

    // Проверим цены
    $product['standard_price']  = trim(str_replace($product['symbol'],'',$product['price']['standard_price']));
    $product['price']           = trim(str_replace($product['symbol'],'',$product['price']['price']));

    if($product['standard_price']>0){
        $S = SQLQ("SELECT * FROM `price` WHERE `sku` LIKE '{$product['sku']}' ORDER BY `price`.`date_create` DESC LIMIT 0,1;");
        if(mysqli_num_rows($S)>0){
            while($Q = mysqli_fetch_array($S)){
                if($Q['standard_price']!=$product['standard_price'] || $Q['price']!=$product['price']){
                    SQLQ("INSERT INTO `price` (`sku`, `date_create`, `currency`, `standard_price`, `price`) VALUES ('{$product['sku']}', now(), '{$product['currency']}', '{$product['standard_price']}', '{$product['price']}') ON DUPLICATE KEY UPDATE `standard_price` = '{$product['standard_price']}', `price` = '{$product['price']}'; ");
                }
            }
        }else{
            SQLQ("INSERT INTO `price` (`sku`, `date_create`, `currency`, `standard_price`, `price`) VALUES ('{$product['sku']}', now(), '{$product['currency']}', '{$product['standard_price']}', '{$product['price']}');");
        }
    }
}

//****************************************************************************************************************************************
function load()
{
    $product = [];
    $S = SQLQ("SELECT * FROM `load` ORDER BY `id` DESC LIMIT 0,1;");
    if(mysqli_num_rows($S)>0){
        while($Q = mysqli_fetch_array($S)){
            SQLQ("UPDATE `load` SET `processed` = '1' WHERE `load`.`id` = {$Q['id']};");
            $product['sku']         = sku($Q['specifications']);
            $product['weight']      = weight($Q['specifications']); 
            $product['name']        = trim($Q['name']);
            $product['language']    = trim($Q['language']);
            $product['img']         = trim($Q['image']);

            $meta                   = explode(';',$Q['meta']);
            foreach ($meta as $key => $value) {
                $arr = explode('=',$value);
                if(is_array($arr)){
                    if(count($arr)>1){
                        $product['meta'][$arr[0]] = $arr[1];    
                    }
                }
            }

            $product['rank'] = rank($Q['rank']);

            save_product($product);

        }
    }
}

//********************************************************************* */
function search_product_id($id, $lang)
{
    $S = SQLQ("SELECT 
        prod.sku AS sku, 
        prod.weight AS weight,
        nam.name AS name,
        nam.brand AS brand
    FROM `product` prod 
    INNER JOIN `product_name` nam ON nam.sku = prod.sku 
    WHERE prod.product_id = {$id} AND nam.lang = '{$lang}'
    ");
    if(mysqli_num_rows($S)>0){
        while($Q = mysqli_fetch_array($S)){
            return ['sku'=>$Q['sku'], 'weight'=>$Q['weight'], 'name'=>$Q['name'], 'brand'=>$Q['brand']];
        }
    }
}

//********************************************************************* */
function search_price($sku, $currency)
{
    $price = [];
    $S = SQLQ("SELECT * FROM `price` WHERE `sku` LIKE '{$sku}' AND `currency` LIKE '{$currency}' ORDER BY `date_create` DESC;");
    if(mysqli_num_rows($S)>0){
        while($Q = mysqli_fetch_array($S)){
            $price[] = ['date_create'=>$Q['date_create'], 'price'=>$Q['price'], 'standard_price'=>$Q['standard_price']];
        }
    }
    return $price;
}

//********************************************************************* */
function search_HTML_atrib($atrib, $html)
{
    /*if(strpos($html, $atrib) === false){
        return '';
    }*/

    $html = trim($html);
    $html = substr($html, strpos($html, $atrib)+strlen($atrib));
    return substr($html, 0, strpos($html, '`'));
}

//********************************************************************* */
function sku_HTML($html)
{
    //data-part-number=`
    if(strpos($html, 'data-part-number=`') !== false){
        return search_HTML_atrib('data-part-number=`', $html);
    }

   
    if(strpos($html, 'data-ga-part-number=`') !== false){
        return search_HTML_atrib('data-ga-part-number=`', $html);
    }

    //data-ga-event-label=`
    if(strpos($html, 'data-ga-event-label=`') !== false){
        return search_HTML_atrib('data-ga-event-label=`', $html);
    }
    
    return '';
}

//********************************************************************* */
function product_id_HTML($html)
{
    if(strpos($html, 'data-ga-id=`') !== false){
        return search_HTML_atrib('data-ga-id=`', $html);
    }

    if(strpos($html, 'data-product-id=`') !== false){
        return search_HTML_atrib('data-product-id=`', $html);
    }

    //data-ds-id
    if(strpos($html, 'data-ds-id=`') !== false){
        return search_HTML_atrib('data-ds-id=`', $html);
    }

    echo $html;

    return '';
}

//********************************************************************* */
function img_HTML($html)
{
    if(strpos($html, '<img src=`') !== false){
        return search_HTML_atrib('<img src=`', $html);
    }

    if(strpos($html, 'data-image-retina-src=`') !== false){
        return search_HTML_atrib('data-image-retina-src=`', $html);
    }

    if(strpos($html, 'data-image=`') !== false){
        return search_HTML_atrib('data-image=`', $html);
    }

    if(strpos($html, 'src=`') !== false){
        return search_HTML_atrib('src=`', $html);
    }
    //src=`

    return '';
}

//********************************************************************* */
function name_HTML($html)
{
    if (strpos($html, '<div class=`product-summary-title`>') !== false) {
        $atrib = '<div class=`product-summary-title`>';
        $text = trim($html);
        $text = trim(str_replace('\n','',trim(substr($text, strpos($text, $atrib)+strlen($atrib)))));
        return substr($text, 0, strpos($text, '<'));
    }

    if(strpos($html, 'data-ga-name=`') !== false){
        return search_HTML_atrib('data-ga-name=`', $html);
    }

    if(strpos($html, 'title=`') !== false){
        return search_HTML_atrib('title=`', $html);
    }

    //data-ga-title=`
    if(strpos($html, 'data-ga-title=`') !== false){
        return search_HTML_atrib('data-ga-title=`', $html);
    }

    //data-ga-title=`
    return '';
}

//********************************************************************* */
function price_HTML($html)
{
    $price = ['standard_price'=>'0', 'price'=>'0'];

    $html = trim($html);

    //$html = substr($html, strpos($html, $atrib)+strlen($atrib));
    //return substr($html, 0, strpos($html, '`'));
    if(strpos($html, '<span class=`price` itemprop=`price` content=`') !== false){
        $price['standard_price']    = search_HTML_atrib('<span class=`price` itemprop=`price` content=`', $html);
        $price['price']             = $price['standard_price'];
    }elseif(strpos($html, '<span class=`price discount-red` itemprop=`price` content=`') !== false){
        $price['price']             = search_HTML_atrib('<span class=`price discount-red` itemprop=`price` content=`', $html);
        if(strpos($html, '<span class=`price-olp` itemprop=`price` content=`') !== false){
            $price['standard_price'] = search_HTML_atrib('<span class=`price-olp` itemprop=`price` content=`', $html);      
        }elseif(strpos($html, '<span class=`price-olp`>') !== false){
            $atrib = '<span class=`price-olp`>';
            $text = trim($html);
            $text = trim(str_replace(' ','',str_replace('\n','',trim(substr($text, strpos($text, $atrib)+strlen($atrib))))));
            
            $atrib = '<bdi>';
            $text = trim(substr($text, strpos($text, $atrib)+strlen($atrib)));
            $price['standard_price'] = substr($text, 0, strpos($text, '<'));
        }
    }elseif(strpos($html, '<span class=`price discount-red`>') !== false){
        $atrib = '<span class=`price discount-red`>';
        $text = trim($html);
        $text = trim(str_replace(' ','',str_replace('\n','',trim(substr($text, strpos($text, $atrib)+strlen($atrib))))));
        //echo $text;
        
        $atrib = '<bdi>';
        $text = trim(substr($text, strpos($text, $atrib)+strlen($atrib)));
        $price['price'] = substr($text, 0, strpos($text, '<'));
        //echo $price['price'];
        
        $atrib = '<span class=`price-olp`>';
        $text = trim($html);
        $text = trim(str_replace(' ','',str_replace('\n','',trim(substr($text, strpos($text, $atrib)+strlen($atrib))))));
        $atrib = '<bdi>';
        $text = trim(substr($text, strpos($text, $atrib)+strlen($atrib)));
        $price['standard_price'] = substr($text, 0, strpos($text, '<'));
    }elseif(strpos($html, '<div class=`price-original-list`>') !== false){
        $atrib = '<div class=`price-original-list`>';
        $text = trim($html);
        $text = trim(str_replace(' ','',str_replace('\n','',trim(substr($text, strpos($text, $atrib)+strlen($atrib))))));
        $price['standard_price'] = substr($text, 0, strpos($text, '<'));

        $price['price'] = search_HTML_atrib('<meta itemprop=`price` content=`', $html);
    }

    return $price;
}

//********************************************************************* */
function brand_HTML($html, $name){
    //data-ga-brand-name=`
    if(strpos($html, 'data-ga-brand-name=`') !== false){
        return search_HTML_atrib('data-ga-brand-name=`', $html);
    }

    if($name != ''){
        return trim(substr($name, 0, strpos($name, ',')));
    }

    return '';
}

//********************************************************************* */
function clear_symbol($data, $symbol){

    if(is_array($data)){
        foreach ($data as $key => $value) {
            $data[$key] = clear_symbol($value, $symbol);
        }
    }else{
        return trim(str_replace($symbol,'',$data));
    }

    return $data;
}

//********************************************************************* */
function load_HTML($html, $type)
{
    $html = trim($html);

    $product = [];
    $product['sku']         = sku_HTML($html);
    //echo $product['sku'];
    if($product['sku']!=''){
        $product['product_id']  = product_id_HTML($html);
        $product['img']         = img_HTML($html);
        $product['name']        = name_HTML($html);
        $product['price']       = price_HTML($html);
        $product['brand']       = brand_HTML($html, $product['name']);
        $product['weight']      = 0;

    }else{
        $product['html'] = $html;
    }
    return $product;
}

//********************************************************************* */
function load_HTML2($html, $type)
{
    $html = trim($html);

    $product = [];
    $product['sku']         = sku_HTML($html);

    if($product['sku']!=''){
        $product['product_id']  = product_id_HTML($html);
        $product['img']         = img_HTML($html);
        $product['name']        = name_HTML($html);
        $product['price']       = price_HTML($html);
        $product['brand']       = brand_HTML($html, $product['name']);
        $product['weight']      = weight($html);
        $product['rank']        = rank($html);
    }else{
        $product['html'] = $html;
    }

    return $product;
}

//********************************************************************* */
function search_symbol($currency)
{
    $html = '<div class="item gh-dropdown-menu-item" data-val="USD">
	<bdi><label>USD ($)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item selected" data-val="EUR">
	<bdi><label>EUR (€)</label></bdi>
</div>
<div class="dd-divider" data-val=""></div>
<div class="item gh-dropdown-menu-item" data-val="AED">
	<bdi><label>AED (AED)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="AMD">
	<bdi><label>AMD (֏)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="ARS">
	<bdi><label>ARS (ARS$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="AUD">
	<bdi><label>AUD (AU$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="AZN">
	<bdi><label>AZN (ман)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="BAM">
	<bdi><label>BAM (KM)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="BGN">
	<bdi><label>BGN (лв)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="BHD">
	<bdi><label>BHD (BD)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="BND">
	<bdi><label>BND (B$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="BRL">
	<bdi><label>BRL (R$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="BYN">
	<bdi><label>BYN (Br)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="CAD">
	<bdi><label>CAD (CA$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="CHF">
	<bdi><label>CHF (Fr)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="CLP">
	<bdi><label>CLP ($)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="CNY">
	<bdi><label>CNY (¥)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="COP">
	<bdi><label>COP (Col$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="CZK">
	<bdi><label>CZK (Kč)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="DJF">
	<bdi><label>DJF (Fdj)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="DKK">
	<bdi><label>DKK (kr.)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="DZD">
	<bdi><label>DZD (DA)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="EGP">
	<bdi><label>EGP (EGP)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="FJD">
	<bdi><label>FJD ($)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="FKP">
	<bdi><label>FKP (£)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="GBP">
	<bdi><label>GBP (£)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="GEL">
	<bdi><label>GEL (ლ)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="GHS">
	<bdi><label>GHS (GH₵)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="GIP">
	<bdi><label>GIP (£)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="GNF">
	<bdi><label>GNF (FG)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="HKD">
	<bdi><label>HKD (HK$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="HRK">
	<bdi><label>HRK (kn)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="HUF">
	<bdi><label>HUF (Ft)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="IDR">
	<bdi><label>IDR (Rp)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="ILS">
	<bdi><label>ILS (₪)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="INR">
	<bdi><label>INR (₹)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="ISK">
	<bdi><label>ISK (kr)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="JOD">
	<bdi><label>JOD (JOD)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="JPY">
	<bdi><label>JPY (¥)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="KGS">
	<bdi><label>KGS (лв)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="KHR">
	<bdi><label>KHR (៛)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="KRW">
	<bdi><label>KRW (₩)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="KWD">
	<bdi><label>KWD (KWD)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="KYD">
	<bdi><label>KYD (KYD$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="KZT">
	<bdi><label>KZT (₸)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="LVL">
	<bdi><label>LVL (Ls)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MAD">
	<bdi><label>MAD (DH)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MDL">
	<bdi><label>MDL (L)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MKD">
	<bdi><label>MKD (ден)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MOP">
	<bdi><label>MOP (MOP$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MVR">
	<bdi><label>MVR (ރ)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MWK">
	<bdi><label>MWK (K)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MXN">
	<bdi><label>MXN (Mex$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="MYR">
	<bdi><label>MYR (RM)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="NGN">
	<bdi><label>NGN (₦)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="NOK">
	<bdi><label>NOK (kr)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="NPR">
	<bdi><label>NPR (रू)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="NZD">
	<bdi><label>NZD (NZ$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="OMR">
	<bdi><label>OMR (OMR)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="PEN">
	<bdi><label>PEN (S/)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="PHP">
	<bdi><label>PHP (₱)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="PKR">
	<bdi><label>PKR (₨)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="PLN">
	<bdi><label>PLN (zł)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="QAR">
	<bdi><label>QAR (QAR)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="RON">
	<bdi><label>RON (lei)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="RSD">
	<bdi><label>RSD (РСД)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="RUB">
	<bdi><label>RUB (₽)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="RWF">
	<bdi><label>RWF (FRw)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="SAR">
	<bdi><label>SAR (SAR)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="SEK">
	<bdi><label>SEK (kr)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="SGD">
	<bdi><label>SGD (SG$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="SHP">
	<bdi><label>SHP (£)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="STN">
	<bdi><label>STN (Db)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="SZL">
	<bdi><label>SZL (E)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="THB">
	<bdi><label>THB (฿)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="TOP">
	<bdi><label>TOP (T$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="TRY">
	<bdi><label>TRY (₺)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="TWD">
	<bdi><label>TWD (NT$)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="UAH">
	<bdi><label>UAH (₴)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="UGX">
	<bdi><label>UGX (USh)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="UZS">
	<bdi><label>UZS (лв)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="VEF">
	<bdi><label>VEF (vef)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="VND">
	<bdi><label>VND (₫)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="XAF">
	<bdi><label>XAF (CFA)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="XCD">
	<bdi><label>XCD ($)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="YER">
	<bdi><label>YER (﷼)</label></bdi>
</div>
<div class="item gh-dropdown-menu-item" data-val="ZAR">
	<bdi><label>ZAR (R)</label></bdi>
</div>';

    $atrib = "<label>{$currency} (";
    $html = trim($html);
    $html = substr($html, strpos($html, $atrib)+strlen($atrib));
    return substr($html, 0, strpos($html, ')'));

}

?>