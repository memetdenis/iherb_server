<?php

class CSV{

    // Стартовая функция
    public static function start($id = 0, $only_price = false){
        $date = date('Y-m-d_H:i:s',time());

        header("Content-type: text/csv"); 
        header("Content-Disposition: attachment; filename={$id}_{$date}.csv"); 
        header("Pragma: no-cache");
        header("Expires: 0"); 
         
        $buffer = fopen('php://output', 'w'); 
        fputs($buffer, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        $prods = Catalog::list_product($id, $only_price);
        //print_r($prods);
        foreach($prods as $val) { 
            fputcsv($buffer, $val, ';'); 
        } 
        fclose($buffer); 
        exit();
    }
}

?>