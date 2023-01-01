<?php

class Shop{
    // Стартовая функция распределения действий в магазине
    public static function start(){
        if(isset($_GET['pcodes'])){
            self::add_shop(Other::anticrack($_GET['pcodes']));
        }elseif(isset($_GET['order'])){
            self::find_shop((int)($_GET['order']));
        }

        
    }

    // Новая корзина с продуктами
    static function add_shop($pcodes){
        $pcodes_list = explode('_',$pcodes);
        foreach ($pcodes_list as $key => $value) {
            $pcodes_list[$key] = explode('qty',$pcodes_list[$key]);
        }
        // Сохраним заказ и получим его номер
        $shop = self::save($pcodes);

        // Сохраним список товаров для заказа
        self::save_list_pcodes($shop['id'], $pcodes_list);

        // Получим информацию о заказе
        self::find_shop($shop['id']);
    }

    // Найдем всю информацию о заказе
    static function find_shop($shop_id){

        // Соберём всю информацию о заказе.
        $shop           = self::find_id($shop_id);
        if ($shop) {
            $pcodes_list    = self::find_list_pcodes($shop_id);
            $weight         = self::weight_list_pcodes($pcodes_list);
            $kol_item       = self::kol_item_list_pcodes($pcodes_list);
            $summa_price    = self::summa_price_list_pcodes($pcodes_list);

            // Отправим список заказа
            Json::replace(['shop_id'=>$shop_id, 'weight'=>$weight, 'all_item'=>$kol_item, 'summa_price'=>$summa_price, 'list'=>$pcodes_list], 'shop');
        }else{
            Json::replace('ERROR: no shop id = '.$shop_id, 'ERROR');
        }
    }

    // Сохраним новый заказ
    // Вернём id заказа
    static function save($pcodes){
        SQL::Query("INSERT INTO `shop` (`pcodes`) VALUES ('{$pcodes}');");
        $shop = self::find_pcodes($pcodes);
        return $shop;
    }

    // Найдем номер заказа по pcodes , вернём самый свежий
    static function find_id($id){
        $result = SQL::Query("SELECT * FROM `shop` WHERE `id` = {$id} ;");
        foreach ($result as $row) {
            return $row;
        }

        return NULL;
    }

    // Найдем номер заказа по pcodes , вернём самый свежий
    static function find_pcodes($pcodes){
        $result = SQL::Query("SELECT * FROM `shop` WHERE `pcodes` LIKE '{$pcodes}' ORDER BY `shop`.`id` DESC LIMIT 0,1;");
        foreach ($result as $row) {
            return $row;
        }
    }

    // Получим общий вес по заказу.
    static function weight_list_pcodes($list_product){
        $weight = 0;
        foreach ($list_product as $key => $value) {
            $weight += $value['weight'] * $value['qty'];
        }
        return round($weight,2);
    }

    // Получим количество товаров по заказу.
    static function kol_item_list_pcodes($list_product){
        $kol = 0;
        foreach ($list_product as $key => $value) {
            $kol += $value['qty'];
        }
        return $kol;
    }

    // Получим сумму заказа товаров .
    static function summa_price_list_pcodes($list_product){
        $summa = 0;
        foreach ($list_product as $key => $value) {
            if ($value['price']) {
                $summa += $value['price']['price'] * $value['qty'];
            }
        }
        return round($summa,2);
    }

    // Получим список товаров по заказу.
    static function find_list_pcodes($shop_id){
        $list_product = [];
        $result = SQL::Query("SELECT * FROM `shop_pcodes` WHERE `shop_id` = {$shop_id};");
        foreach ($result as $row) {
            $card = [];
            $product = Product::sku($row['code']);

            if ($product) {
                $card['iherb_id']   = $product['iherb_id'];
                $card['weight']     = $product['weight'];
                $card['name']       = Product::Name($product['id'], '', false);
                $card['price']      = Price::last_to_product(Settings::$currency, Settings::$country, $product['id']);
            }else{
                $card['iherb_id']   = NULL;
                $card['weight']     = 0;
                $card['name']       = '';
                $card['price']      = NULL;
            }

            $card['sku']        = $row['code'];
            $card['qty']        = $row['qty'];
            $card['confirm']    = $row['confirm'];

            $list_product[]     = $card;
        }
        return $list_product;
    }



    // Сохраним список товаров по заказу.
    public static function save_list_pcodes($shop_id, $pcodes_list){
        foreach ($pcodes_list as $key => $value) {
            SQL::Query("INSERT INTO `shop_pcodes` (`shop_id`, `code`, `qty`) VALUES ({$shop_id}, '{$value[0]}', {$value[1]});");
        }
    }

}