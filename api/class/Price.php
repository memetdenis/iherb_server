<?php

class Price{

    public static function all_to_product($id){
        $price_list = [];
        $result = SQL::Query("SELECT * FROM `price` WHERE `product_id` = {$id} GROUP BY `country`; ");
        foreach ($result as $row) {
            $price_list[$row['country']] = self::country_to_product($row['country'], $id);
        }
        return $price_list;
    }

    public static function country_to_product($country, $id){
        $price_list = [];
        $result = SQL::Query("SELECT * FROM `price` WHERE `product_id` = {$id} AND `country` LIKE '{$country}' GROUP BY `currency`;");
        foreach ($result as $row) {
            $price_list[$row['currency']] = self::currency_to_product($row['currency'], $country, $id);
        }
        return $price_list;
    }

    public static function currency_to_product($currency, $country, $id){
        $price_list = [];
        $result = SQL::Query("SELECT * FROM `price` WHERE `product_id` = {$id} AND `currency` LIKE '{$currency}' AND `country` LIKE '{$country}' ORDER BY `price`.`date_create` DESC; ");
        foreach ($result as $row) {
            $price_list[$row['date_create']] = ['country' => $row['country'], 'currency' => $row['currency'], 'standard_price' => $row['standard_price'], 'price' => $row['price']];
        }
        return $price_list;
    }

    public static function last_to_product($currency, $country, $id){
        $price_list = [];
        $result = SQL::Query("SELECT * FROM `price` WHERE `product_id` = {$id} AND `currency` LIKE '{$currency}' AND `country` LIKE '{$country}' ORDER BY `price`.`date_create` DESC LIMIT 0,1; ");
        foreach ($result as $row) {
            return ['country' => $row['country'], 'currency' => $row['currency'], 'standard_price' => $row['standard_price'], 'price' => $row['price'], 'last_modified' => $row['last_modified']];
        }
        return $price_list;
    }
}
?>