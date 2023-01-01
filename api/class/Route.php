<?php

class Route{
    public static function GET(){
        if(isset($_GET['act'])){ Settings::$act = Other::anticrack($_GET['act']); }
        Json::replace(Settings::$act, 'act');
        if (Settings::$act != '') {
            switch (Settings::$act){
                case 'catalog':
                    Catalog::start();
                    break;
                case 'product':
                    Product::start();
                    break;
                case 'shop':
                    Shop::start();
                    break;
            }
        }
    }
}

?>