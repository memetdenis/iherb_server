<?php
    class Product
    {
        public static function start(){
            // Найдем нужную форму для продукта
            $form       = 'full';
            if(isset($_GET['form'])){$form = Other::anticrack($_GET['form']);}

            // Найдем коды продукта
            $id_iherb   = 0;
            $sku        = 0;
            if(isset($_GET['id_iherb'])){   $id_iherb   = (int)($_GET['id_iherb']);         }
            if(isset($_GET['sku'])){        $sku        = Other::anticrack($_GET['sku']);   }

            // Найдем сам продукт
            $product = NULL;
            if($id_iherb > 0){
                $product = self::id_iherb($id_iherb);
            }elseif($sku > 0){
                $product = self::sku($sku);
            }

            // Выберем форму для работы.
            if($form=='full'){ 
                Json::replace(self::full($product['id']), 'result');
            }elseif($form=='mini'){
                Json::replace(self::mini($product['id']), 'result');
            }
            //Json::replace($id_iherb, 'log');
        }

        // Полная информация о продукте.
        public static function full($id){
            $product = [];
            $result = self::id($id);
            if ($result) {
                $product['iherb_id']    = $result['iherb_id'];
                $product['sku']         = $result['sku'];
                $product['weight']      = $result['weight'];
                $product['url']         = $result['url'];
                $product['name']        = self::Name($result['id']);
                $product['img']         = self::img_list($result['iherb_id']);
                $product['specs_list']  = self::specs_list($result['iherb_id']);
                $product['price']       = Price::last_to_product(Settings::$currency, Settings::$country, $result['id']);             
            }
            return $product;
        }

        // Список спецификаций
        static function specs_list($iherb_id, $lang = ''){
            // Если язык не выбран, то назначаем его по умолчанию
            if($lang==''){$lang = Settings::$lang;}

            $specs_list = [];

            // Попытка найти на нужном языке
            $result = SQL::Query("SELECT * FROM `specs_list` WHERE `product_id` = {$iherb_id} AND `lang` LIKE '{$lang}';");
            foreach ($result as $row) {
                $specs_list[$row['param']] = ['name' => $row['name'], 'value' => $row['value']];
            }
            if(count($specs_list)>0){
                return $specs_list;
            }

            // Попытка найти на любом языке
            $result = SQL::Query("SELECT * FROM `specs_list` WHERE `product_id` = {$iherb_id} GROUP BY `param`;");
            foreach ($result as $row) {
                $specs_list[$row['param']] = ['name' => $row['name'], 'value' => $row['value']];
            }
            if(count($specs_list)>0){
                return $specs_list;
            }
            
            return $specs_list;
        }

        // Мини форма для продукта.
        public static function mini($id){
            $product = [];
            $result = self::id($id);
            if ($result) {
                $product['iherb_id']    = $result['iherb_id'];
                $product['sku']         = $result['sku'];
                $product['weight']      = $result['weight'];
                $product['url']         = $result['url'];
                $product['name']        = self::Name($result['id']);
                $product['img']         = self::img_list($result['iherb_id']);
                $product['price']       = Price::last_to_product(Settings::$currency, Settings::$country, $result['id']);
            }
            return $product;
        }

        // Найдем продукт по id
        static function id($id){
            $result = SQL::Query("SELECT * FROM `product` WHERE `id` = {$id}; ");
            foreach ($result as $row) {
                return $row;
            }
            return NULL;
        }

        // Найдем продукт по sku
        static function sku($id){
            $result = SQL::Query("SELECT * FROM `product` WHERE `sku` LIKE '{$id}'; ");
            foreach ($result as $row) {
                return $row;
            }
            return NULL;
        }

        // Найдем продукт по id_iherb
        static function id_iherb($id){
            $result = SQL::Query("SELECT * FROM `product` WHERE `iherb_id` = {$id}; ");
            foreach ($result as $row) {
                return $row;
            }
            return NULL;
        }

        // Найдем все имена продукта
        static function Name($id, $lang = '', $all = true){
            // Если язык не выбран, то назначаем его по умолчанию
            if($lang==''){$lang = Settings::$lang;}

            // Вернём все варианты названий
            if ($all) {
                $name = [];
                $result = SQL::Query("SELECT * FROM `product_name` WHERE `product_id` = {$id};");
                foreach ($result as $row) {
                    $name[$row['lang']] = $row['name'];
                }
                return $name;
            }

            // Вернём только на нужном языке
            $result = SQL::Query("SELECT * FROM `product_name` WHERE `product_id` = {$id} AND `lang` LIKE '$lang';");
            foreach ($result as $row) {
                return $row['name'];
            }

            // Вернём на любом языке, первое найденное
            $result = SQL::Query("SELECT * FROM `product_name` WHERE `product_id` = {$id};");
            foreach ($result as $row) {
                return $row['name'];
            }

        }

        // Найдем все картинки на продукт
        static function img_list($id){
            $img_list = [];
            $result = SQL::Query("SELECT * FROM `product_img` WHERE `product_id` = {$id};");
            foreach ($result as $row) {
                $img_list[] = "https://iherb.memet.ru/images/{$row['brand']}/{$row['sku']}/l/{$row['img']}";
            }
            return $img_list;
        }
    }
?>