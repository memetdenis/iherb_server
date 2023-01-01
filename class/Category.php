<?php

class Category{
    
    //********************************************************************* */
    public static function find_or_create($category){

        $S = SQLQ("SELECT * FROM `catalog` WHERE `url` LIKE '{$category[0]}';");
        if(mysqli_num_rows($S)>0){
            while($Q = mysqli_fetch_array($S)){
                return self::find_or_create_name($Q,$category);
            }
        }else{
            SQLQ("INSERT INTO `catalog` (`parent`, `url`) VALUES ('{$category[3]}', '{$category[0]}');");
            return self::find_or_create($category);
        }
    }

    //*********************************************************************
    static function find_or_create_name($cat, $category){

        $S = SQLQ("SELECT * FROM `catalog_name` WHERE `catalog_id` = {$cat['id']} AND `lang` LIKE '{$category[2]}';");
        if(mysqli_num_rows($S)>0){
            while($Q = mysqli_fetch_array($S)){
                $cat['name'] = $Q['name'];
                return $cat;
            }
        }else{
            SQLQ("INSERT INTO `catalog_name` (`catalog_id`, `lang`, `name`) VALUES ('{$cat['id']}', '{$category[2]}', '{$category[1]}');");
            return self::find_or_create_name($cat, $category);
        }
    }


}
?>