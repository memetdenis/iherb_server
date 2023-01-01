<?php

class Catalog{

    public static function start(){
        
        if(isset($_GET['id'])){ 
            $id = Other::anticrack($_GET['id']);
            return self::group($id);
        }else{
            return self::all();
        }
    }

    static function all(){
        $catalog = [];
        $catalog = self::add_parent(0);
        // Выгрузим все строки полученные в запросе
        return $catalog;
    }

    static function add_parent($id){
        $catalog = [];
        $result = SQL::Query("SELECT * FROM `catalog` WHERE `parent` = {$id}; ");
        foreach ($result as $row) {
            $catalog[$row['id']] = [];
            $catalog[$row['id']]['name']    = self::name($row['id']);
            $catalog[$row['id']]['count']   = self::count_item($row['id']);
            $catalog[$row['id']]['entry']   = self::add_parent($row['id']);
        }
        return $catalog;
    }

    // Количество товаров в каталоге
    static function count_item($id){
        $result = SQL::Query("SELECT * FROM `product_catalog` WHERE `catalog_id` = {$id}; ");
        return count($result);
    }

    static function group($id){

        $catalog = [];
        $result = SQL::Query("SELECT * FROM `catalog` WHERE `id` = {$id}; ");
        foreach ($result as $row) {
            $catalog[$row['id']] = [];
            $catalog[$row['id']]['name']    = self::name($row['id']);
            $catalog[$row['id']]['count']   = self::count_item($row['id']);
            $catalog[$row['id']]['item']    = self::list_product($id);
        }

        return $catalog;
    }

    public static function count(){
        $result = SQL::Query("SELECT * FROM `product_catalog` GROUP BY `product_id`;");
        return count($result);
    }

    static function list_product($id, $only_price = false){

        if (!$only_price) {
            $item = [
                [
                'Product code'
                ,'Price'
                ,'List price'
                ,'Language'
                ,'Category'
                ,'Product name'
                ,'Store'
                ,'short description'
                ,'description'
                ,'Weight'
                ,'Detailed image'
                ,'Images'
                ,'Quantity'
                //,'Features'
                ,'before date'
                //,'Min quantity'
                //,'Max quantity'
                //,'Quantity step'
                ]
            ];
        }else{
            $item = [
                [
                'Product code'
                ,'Price'
                ,'List price'
                ]
            ];
        }

        $result = SQL::Query("SELECT * FROM `product_catalog` WHERE `catalog_id` = {$id}; ");
        foreach ($result as $row) {
            $item[] = Product::full($row['product_id'], $id, $only_price);
        }
        return $item;
    }

    public static function name($id, $lang = ''){

        // Если язык не выбран, то назначаем его по умолчанию
        if($lang==''){$lang = Settings::$lang;}

        // Попробуем найти нужное название каталога по языку.
        $result = SQL::Query("SELECT * FROM `catalog_name` WHERE `catalog_id` = {$id} AND `lang` LIKE '{$lang}'; ");
        foreach ($result as $row) {
            return $row['name'];
        }

        // Если не получилось найти по нужному языку, то найдем любой
        $result = SQL::Query("SELECT * FROM `catalog_name` WHERE `catalog_id` = {$id} LIMIT 0,1; ");
        foreach ($result as $row) {
            return $row['name'];
        }
    }

    public static function id($id){
        $result = SQL::Query("SELECT * FROM `catalog` WHERE `id` = {$id}; ");
        foreach ($result as $row) {
            return $row;
        }
    }

}

?>