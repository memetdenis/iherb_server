<?php

class Img
{
    public static function save_base($img){
        $S = SQLQ("SELECT * FROM `product_img` WHERE `product_id` = {$img['product_id']} AND `img` LIKE '{$img['name']}' ;");
        if(mysqli_num_rows($S)==0){
            self::load($img);
        }
    }

    public static function load($img){
        try{
            //if(file_exists("https://cloudinary.images-iherb.com/image/upload/f_auto,q_auto:eco/images/{$img['brand']}/{$img['sku']}/l/{$img['name']}")){

                if(!is_dir("images/{$img['brand']}/")) {
                    mkdir("images/{$img['brand']}/", 0777, true);
                }

                if(!is_dir("images/{$img['brand']}/{$img['sku']}/")) {
                    mkdir("images/{$img['brand']}/{$img['sku']}/", 0777, true);
                }

                if(!is_dir("images/{$img['brand']}/{$img['sku']}/l/")) {
                    mkdir("images/{$img['brand']}/{$img['sku']}/l/", 0777, true);
                }
                
                $image_url = "https://cloudinary.images-iherb.com/image/upload/f_auto,q_auto:eco/images/{$img['brand']}/{$img['sku']}/l/{$img['name']}";
                $image_location = "images/{$img['brand']}/{$img['sku']}/l/{$img['name']}";

                $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                curl_setopt($ch, CURLOPT_URL,$image_url);
                $result=curl_exec($ch);
            
            
                $open_image_in_binary = fopen($image_location, 'wb');
                // Close the connection and
                curl_close($ch);
                // Close the file pointer
                fwrite($open_image_in_binary,$result);
                fclose($open_image_in_binary);

                SQLQ("INSERT INTO `product_img` (`product_id`, `sku`, `brand`, `img`) VALUES ({$img['product_id']}, '{$img['sku']}', '{$img['brand']}', '{$img['name']}') ; ");

        }catch(\Exception $e){
           //addErrorMessage($e->getMessage());
        }
    }

}

?>