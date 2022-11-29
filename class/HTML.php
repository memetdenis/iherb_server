<?php

class HTML{

    //*********************************************************************
    // Поиск Бренда в коде HTML
    public static function brand_search($html, $name){
        if(strpos($html, 'data-ga-brand-name=`') !== false){
            return search_HTML_atrib('data-ga-brand-name=`', $html);
        }
    
        if($name != ''){
            return trim(substr($name, 0, strpos($name, ',')));
        }
    
        return '';
    }

    
    //*********************************************************************
    // Поиск рейтинга в коде HTML
    public static function rating_search($html){
        $rating = ['ratingValue'=>0, 'reviewCount'=>0];

        // Попробуем найти вариант №1
        $text = "'<meta itemprop=`ratingValue` content=`'";
        if(strpos($html, $text) !== false){
            $rating['ratingValue'] = search_HTML_atrib($text, $html);
        }

        if(strpos($html, '<meta itemprop=`reviewCount` content=`') !== false){
            $rating['reviewCount'] = search_HTML_atrib('<meta itemprop=`reviewCount` content=`', $html);
        }

        if($rating['reviewCount']==0){
            if (strpos($html, '<div class=`rating`>') !== false) {
                $text = cut_html_code('<div class=`rating`>', $html, true);
                $text = search_HTML_atrib('title=`', $text);
                $text = trim($text);
                $rating['ratingValue'] = trim(substr($text, 0, strpos($text, 'of')));
                $code = 'on';
                $rating['reviewCount'] = trim(substr($text, strpos($text, $code)+strlen($code)));
            }
        }

        return $rating;
    }
}

?>