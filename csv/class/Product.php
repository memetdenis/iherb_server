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
        // 'Product code','Language','Category','Product name'
        public static function full($id, $catalog_id, $only_price){
            $product = [];
            $result = self::id($id);
            if ($result) {
                if ($result['sku'] != '') {

                    $product['sku']         = $result['sku'];


                    $price = Price::last_to_product(Settings::$currency, Settings::$country, $result['id']);
                    if ($price) {
                        $product['Price']       = $price['price'];
                        $product['List price']  = $price['standard_price'];
                    }else{
                        $product['Price']       = 0;
                        $product['List price']  = 0;
                    }

                    if (!$only_price) {
                        $product['lang']        = mb_strtolower(Settings::$lang);
                        $product['Category']    = 'iHerb'.self::all_category($result['id'], $catalog_id);

                        $name                   = htmlentities(self::Name($result['id'], Settings::$lang, false), null, 'utf-8');
                        $name                   = str_replace("&nbsp;", " ", $name);
                        $name                   = html_entity_decode($name);
                        $product['name']        = $name;

                        $product['Store']       = 'iHerb';


                        $product['short description'] = '';

                        $description                = htmlspecialchars_decode(self::content_id($result['iherb_id'], Settings::$lang, false));
                        $description                = htmlentities($description, null, 'utf-8');
                        $description                = str_replace("&nbsp;", " ", $description);
                        $description                = str_replace("`", "'", $description);
                        $description                = html_entity_decode($description);
                        $product['description']     = $description;

                        $product['Weight']          = $result['weight'];

                        $product['img']             = '';
                        //$product['img']             = self::img_first($result['iherb_id']);

                        $product['Images']          = self::img_all_string($result['iherb_id']);
                        //$product['Images']          = '';

                        $product['Quantity']        = 1000;
                        //$product['Min quantity']    = 1;
                        //$product['Max quantity']    = 3;
                        //$product['Quantity step']   = 1;

                        //$product['Features']        = self::specs_text($result['iherb_id'], Settings::$lang);
                        $product['before date']     = self::before_date($result['iherb_id'], Settings::$lang);
                    }

                }
            }
            return $product;
        }

        // Список всех категорий для выгрузки
        static function before_date($product_id, $lang){

            $specs = '';
            $specs_list = SQL::Query("SELECT * FROM `specs_list` WHERE `product_id` = {$product_id} AND `lang` LIKE '{$lang}' ;");

            foreach ($specs_list as $row) {
                if ($row['param']==0) {
                    return "{$row['value']}";
                }
            }

            return $specs;
        }

        // Список всех категорий для выгрузки
        static function specs_text($product_id, $lang){

            $specs = '';
            $specs_list = SQL::Query("SELECT * FROM `specs_list` WHERE `product_id` = {$product_id} AND `lang` LIKE '{$lang}' ;");

            //$name = self::list_catalog_on_product2($catalog_id, $all_catalog_product);
            //$name .= self::list_catalog_on_product($catalog_id, $all_catalog_product);
            foreach ($specs_list as $row) {
                if($row['param']==0){
                    $specs .= "{$row['name']}: T[{$row['value']}];";
                }

                if($row['param']==2){
                    $specs .= "{$row['name']}: T[{$row['value']}];";
                }

                if($row['param']==5){
                    $specs .= "{$row['name']}: T[{$row['value']}];";
                }
            }
            /*
            if(count($specs_list)>=6)
            {

            }*/
            return $specs;
        }

        // Список всех категорий для выгрузки
        static function all_category($product_id, $catalog_id){
            $all_catalog_product = SQL::Query("SELECT * FROM `product_catalog` WHERE `product_id` = {$product_id} ORDER BY `product_catalog`.`catalog_id` ASC;");
            $name = self::list_catalog_on_product2($catalog_id, $all_catalog_product);
            $name .= self::list_catalog_on_product($catalog_id, $all_catalog_product);
            return $name;
        }

        static function list_catalog_on_product2($catalog_id, $all_catalog_product){

            $catalog = Catalog::id($catalog_id);
            if ($catalog) {
                foreach ($all_catalog_product as $row2) {
                    if ($row2['catalog_id'] == $catalog['parent']) {
                        return self::list_catalog_on_product2($catalog['parent'], $all_catalog_product).'///'.Catalog::name($catalog['parent']);
                    }
                }
            }
/*
            $result = SQL::Query("SELECT * FROM `catalog` WHERE `parent` = {$catalog_id} ORDER BY `parent` ASC; ");
            foreach ($result as $row) {
                foreach ($all_catalog_product as $row2) {
                    if($row2['catalog_id'] == $row['id']){
                        $name.= self::list_catalog_on_product($row2['catalog_id'], $all_catalog_product);
                    }
                }
            }

            return $name;
            */
        }

        static function list_catalog_on_product($catalog_id, $all_catalog_product){

            $name = '///'.Catalog::name($catalog_id);

            $result = SQL::Query("SELECT * FROM `catalog` WHERE `parent` = {$catalog_id} ORDER BY `parent` ASC; ");
            foreach ($result as $row) {
                foreach ($all_catalog_product as $row2) {
                    if($row2['catalog_id'] == $row['id']){
                        $name.= self::list_catalog_on_product($row2['catalog_id'], $all_catalog_product);
                    }
                }
            }

            return $name;
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

        // Найдем контент продукта
        static function content_id($id, $lang = '', $all = true){
            // Если язык не выбран, то назначаем его по умолчанию
            if($lang==''){$lang = Settings::$lang;}

            // Вернём все варианты названий
            if ($all) {
                $name = [];
                $result = SQL::Query("SELECT * FROM `content` WHERE `product_id` = {$id};");
                foreach ($result as $row) {
                    $name[$row['lang']] = $row['html'];
                }
                return $name;
            }

            // Вернём только на нужном языке
            $result = SQL::Query("SELECT * FROM `content` WHERE `product_id` = {$id} AND `lang` LIKE '$lang';");
            foreach ($result as $row) {
                return $row['html'];
            }

            // Вернём на любом языке, первое найденное
            $result = SQL::Query("SELECT * FROM `content` WHERE `product_id` = {$id};");
            foreach ($result as $row) {
                return $row['html'];
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

        // Найдем все картинки на продукт
        static function img_all_string($id){
            $img_string = '';
            $count_img = 0;
            $result = SQL::Query("SELECT * FROM `product_img` WHERE `product_id` = {$id};");
            foreach ($result as $row) {
                if($count_img>0){ $img_string.='///';}
                $img_string.= "https://iherb.memet.ru/images/{$row['brand']}/{$row['sku']}/l/{$row['img']}";
                $count_img++;
            }
            return $img_string;
        }

        // Найдем все картинки на продукт
        static function img_first($id){
            $result = SQL::Query("SELECT * FROM `product_img` WHERE `product_id` = {$id};");
            foreach ($result as $row) {
                return "https://iherb.memet.ru/images/{$row['brand']}/{$row['sku']}/l/{$row['img']}";
            }
        }
    }
?>