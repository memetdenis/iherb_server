<?php
class Json{
    /**
     * добавим в json, в обёртку $node, объект $obj
     */
    public static function add($obj, $node){
        global $json_data;

        if(isset($json_data[$node])){
            $json_data[$node][] = $obj;
        }else{
            $json_data[$node] = [$obj];
        }
    }

    /**
     * Перезапишем в json, обёртку $node, на объект $obj
     */
    public static function replace($obj, $node){
        global $json_data;
        $json_data[$node] = $obj;
    }

    /**
     * Вернуть json
     */
    public static function public(){
        global $json_data;
        echo json_encode($json_data);
    }
}

?>