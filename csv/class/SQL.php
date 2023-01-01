<?php
class SQL{
    static $conn;

    public static function connect(){
        //Подключимся к базе данных
        try {
            SQL::$conn = new PDO('mysql:host=localhost;dbname=iherb', "root", "");
            SQL::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            exit;
        }
    }

    public static function Query($query){
        try{
            // Выполним запрос
            $Q = SQL::$conn->prepare($query);
            $Q->execute();
            return $Q->fetchAll();
        } catch(PDOException $e) {
            echo ('ERROR: ' . $e->getMessage());
            exit;
        }
    }
}

?>