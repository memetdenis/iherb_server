<?php
class Review{
    public static function save($review){
        $result = SQL::Query("SELECT * FROM `review` WHERE `iherb_id` = {$review['iherb_id']} AND `country` LIKE '{$review['country']}' AND `language` LIKE '{$review['language']}' AND `review_id` LIKE '{$review['review_id']}';");
        if(count($result)==0){
            SQL::Query(" INSERT INTO 
            `review` (
                `iherb_id`
                , `country`
                , `language`
                , `review_id`
                , `review_url`
                , `ellipsis`
                , `medical`
                , `title`
                , `postdate`
            ) 
            VALUES (
                {$review['iherb_id']}
                , '{$review['country']}'
                , '{$review['language']}'
                , '{$review['review_id']}'
                , '{$review['review_url']}'
                , '{$review['ellipsis']}'
                , '{$review['medical']}'
                , '{$review['title']}'
                , '{$review['postdate']}'
            );
            ");
            foreach ($review['images'] as $row) {
                self::save_img($row, $review['review_id'], $review['iherb_id']);
            }
        }
    }

    static function save_img($img, $review_id, $iherb_id){

        $img_id = explode('/',$img);
        $img_id = $img_id[count($img_id)-2];

        //return true;
        
        $result = SQL::Query("SELECT * FROM `review_img` WHERE `img_id` LIKE '{$img_id}' AND `review_id` LIKE '{$review_id}';");
        if(count($result)==0){
            if (self::load_img($img, $img_id, $review_id, $iherb_id)) {
                SQL::Query(" INSERT INTO 
                        `review_img` (
                            `review_id`
                            , `img_id`
                        ) 
                        VALUES (
                            '{$review_id}'
                            , '{$img_id}'
                        );
                        ");
            }
        }
    }

    static function load_img($image_url, $img_id, $review_id, $iherb_id){
        try{

            if(!is_dir("review/{$iherb_id}/")) {
                mkdir("review/{$iherb_id}/", 0777, true);
            }

            if(!is_dir("review/{$iherb_id}/{$review_id}/")) {
                mkdir("review/{$iherb_id}/{$review_id}/", 0777, true);
            }

            if(!is_dir("review/{$iherb_id}/{$review_id}/{$img_id}/")) {
                mkdir("review/{$iherb_id}/{$review_id}/{$img_id}/", 0777, true);
            }
            
            $img_extension = explode('/',$image_url);
            $img_extension = $img_extension[count($img_extension)-1];

            $img_extension = explode('.',$img_extension);
            $img_extension = $img_extension[count($img_extension)-1];


            $image_location = "review/{$iherb_id}/{$review_id}/{$img_id}/l.{$img_extension}";

            $image_url = explode('/',$image_url);
            $image_url[count($image_url)-1] = "l.{$img_extension}";
            $image_url = implode('/', $image_url);

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

            return true;

        }catch(\Exception $e){
            return false;
           //addErrorMessage($e->getMessage());
        }
    }
}
?>