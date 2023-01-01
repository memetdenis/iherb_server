<?php

class Other{

    //**************************************************************************************************
    public static function DelNulls($tmp) {

        $lasttmp = $tmp[strlen($tmp)-1]; // последний символ в строке
            
        while ($lasttmp == '0') { // цикл определения нуля в конце строки
            $tmp = substr($tmp,0,strlen($tmp)-1); // укорачиваем строку на 1 символ, т.е. убираем последний ноль
            $lasttmp = $tmp[strlen($tmp)-1]; // определяем последний символ в новой строке
        }

        if (($lasttmp =='.') || ($lasttmp == ',')){
            $tmp = substr($tmp,0,strlen($tmp)-1);
        } // убираем точку или запятую
        
        return $tmp;
    }

    //**************************************************************************************************
    //Функция антихака по текстовой переменной
    public static function anticrack ($var){
        $var=str_replace("'","",$var);
        $var=str_replace('"','',$var);
        $var=str_replace('`','',$var);
        $var=trim(htmlspecialchars($var));
        return ($var);
    }
}

?>