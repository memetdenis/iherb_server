<?php

class Catalog{

    public static function start(){
        
        if(isset($_GET['id'])){ 
            $id = Other::anticrack($_GET['id']);
            self::group($id);
        }else{
            self::all();
        }
    }

    static function all(){
        $catalog = [];
        $catalog = self::add_parent(0);
        // Выгрузим все строки полученные в запросе
        Json::replace($catalog, 'result');
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

        Json::replace($catalog, 'result');
    }

    static function list_product($id){
        $item = [];
        $result = SQL::Query("SELECT * FROM `product_catalog` WHERE `catalog_id` = {$id}; ");
        foreach ($result as $row) {
            $item[] = Product::mini($row['product_id']);
        }
        return $item;
    }

    static function name($id, $lang = ''){

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

}

?>