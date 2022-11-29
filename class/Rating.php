<?php
class Rating{

    // Запись рейтинга
    public static function save($product_id, $product){
        if ($product['rating']['reviewCount']>0 && $product['rating']['ratingValue']>0) {
            $S = SQLQ("SELECT * FROM `rating` WHERE `product_id` = {$product_id} ORDER BY `rating`.`date_create` DESC LIMIT 0,1;");
            if(mysqli_num_rows($S)>0){
                while ($Q = mysqli_fetch_array($S)) {
                    if($Q['reviewCount']!=$product['rating']['reviewCount']){
                        SQLQ("INSERT INTO `rating` (`product_id`, `date_create`, `ratingValue`, `reviewCount`) VALUES ({$product_id}, now(), '{$product['rating']['ratingValue']}', '{$product['rating']['reviewCount']}') ON DUPLICATE KEY UPDATE `ratingValue` = '{$product['rating']['ratingValue']}', `reviewCount` = '{$product['rating']['reviewCount']}'; ");
                    }
                }
            }
        }
    }

    // Получить изменения за месяц
    public static function view_changes($product_id){
        $old_date = date('Y-m-d',  strtotime('-1 month'));
        $old_rating = 0;

        $current_rating = self::current($product_id);

        $S = SQLQ("SELECT * FROM `rating` WHERE `product_id` = {$product_id} AND `date_create` <= '{$old_date}' ORDER BY `rating`.`date_create` DESC LIMIT 0,1;");
        if(mysqli_num_rows($S)>0){
            while ($Q = mysqli_fetch_array($S)) {
                $old_rating = $Q['reviewCount'];
            }
        }else{
            $S = SQLQ("SELECT * FROM `rating` WHERE `product_id` = {$product_id} ORDER BY `rating`.`date_create` ASC LIMIT 0,1;");
            if (mysqli_num_rows($S)>0) {
                while ($Q = mysqli_fetch_array($S)) {
                    $old_rating = $Q['reviewCount'];
                }
            }
        }

        return $current_rating['reviewCount'] - $old_rating;

    }

    // Текущий рейтинг
    static function current($product_id){
        $S = SQLQ("SELECT * FROM `rating` WHERE `product_id` = {$product_id} ORDER BY `rating`.`date_create` DESC LIMIT 0,1;");
        if (mysqli_num_rows($S)>0) {
            while ($Q = mysqli_fetch_array($S)) {
                return $Q;
            }
        }
    }
}

?>