<?php

class Price{

    public static function product_id($product_id, $currency, $country){
        $price = [];
        $old_price = ['date_create'=>'', 'price'=>0, 'standard_price'=>0];
        
        $S = SQLQ("SELECT * FROM `price` WHERE `product_id` LIKE '{$product_id}' AND `currency` LIKE '{$currency}' AND `country` LIKE '{$country}' ORDER BY `date_create` DESC;");
        if(mysqli_num_rows($S)>0){
            while($Q = mysqli_fetch_array($S)){
                if($old_price['price']!=$Q['price']){
                    $price[] = ['date_create'=>$Q['date_create'], 'price'=>$Q['price'], 'standard_price'=>$Q['standard_price']];
                }
                $old_price = ['date_create'=>$Q['date_create'], 'price'=>$Q['price'], 'standard_price'=>$Q['standard_price']];
                
            }
        }
        return $price;
    }
}

?>