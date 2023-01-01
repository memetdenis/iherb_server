<?php

class HTML{

    static function Element($element, $html)
    {
        $dom = new DomDocument();

        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8'), LIBXML_NOERROR);

        $xpath = new DOMXpath($dom);
        return $xpath->query($element);

    }

    static function Element_to_array($elements){

        $result = [];
        if (!is_null($elements)) {
            $id = 0;
            foreach ($elements as $element) {
                //$result[$element->nodeName.'_'.$id] = [];
        
                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
                    //$result[$element->nodeName.'_'.$id][] = $node->nodeValue;
                    $result[] = $node->nodeValue;
                }
                //$id++;
            }
        }  
        return $result;    
    }

    static function Element_to_text($elements){
        $result = '';
        if (!is_null($elements)) {
            foreach ($elements as $element) {
        
                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
                    $result .= $node->nodeValue;
                }
            }
        }  
        return $result; 
    }


    // Парсим отзыв
    public static function review_list($html){

        $review = [];
        $review['language']     = $_POST['language'];
        $review['sku']          = $_POST['sku'];

        $product                = Product::sku($review['sku']);
        $review['iherb_id']     = $product['iherb_id'];
        $review['country']      = $_POST['country'];

        $html = str_replace('`','"',$html['html']);

        $review['images']       = self::Element_to_array(self::Element(".//ugc-review-content-images/*/*/img/@src", $html));
        $review['ellipsis']     = self::Element_to_text(self::Element(".//ugc-ellipsis-text/div/p", $html));
        $review['medical']      = self::Element_to_text(self::Element(".//ugc-medical-discaimer/div/span", $html));

        //ugc-review-title
        $review['review_url']   = self::Element_to_text(self::Element(".//ugc-review-title/a/@href", $html));
        $id_array = explode('/',$review['review_url']);
        $review['review_id']    = $id_array[count($id_array)-2];
        $review['title']        = self::Element_to_text(self::Element(".//ugc-review-title/a", $html));

        $review['postdate']     = self::Element_to_text(self::Element(".//ugc-review-postdate/span", $html));

        Review::save($review);

        return $review;
    }

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
        global $message;

        $rating = ['ratingValue'=>0, 'reviewCount'=>0];

        // Попробуем найти вариант №1
        $text = '<meta itemprop=`ratingValue` content=`';
        if(strpos($html, $text) !== false){
            $rating['ratingValue'] = search_HTML_atrib($text, $html);
        }

        $text = '<meta itemprop=`reviewCount` content=`';
        if(strpos($html, $text) !== false){
            $rating['reviewCount'] = search_HTML_atrib($text, $html);
        }

        // Попробуем найти вариант №2
        if($rating['reviewCount']==0){
            if (strpos($html, '<div class=`rating`>') !== false) {
                $text = cut_html_code('<div class=`rating`>', $html, true);
                $text = search_HTML_atrib('title=`', $text);
                $text = trim($text);
                //$rating['log'] = $text;
                $rating['ratingValue'] = trim(substr($text, 0, strpos($text, 'of')));
                $code = 'on';
                $rating['reviewCount'] = trim(substr($text, strpos($text, $code)+strlen($code)));
            }
        }

        return $rating;
    }

    
    //********************************************************************* */
    public static function search_category_list($catalog, $product_load)
    {
        $product = Product::sku($product_load['sku']);
        $product_load['id'] = $product['id'];
        $category = explode('<br>', $catalog);
        foreach ($category as $key => $value) {
            $category[$key] = trim(str_replace('\n','',trim($value)));
            if($category[$key] == ''){
                unset($category[$key]);
            }else{
                $category[$key] = self::search_category($category[$key], $product_load);
                $product_load['category'] = $category[$key];
                save_catalog($product_load);
            }
        }
        //$category[] = $product;
        return $category;
    }
    
    //********************************************************************* */
    public static function search_category($catalog, $product)
    {
        $category = explode('</a>', $catalog);
        foreach ($category as $key => $value) {
            $category[$key] = trim(str_replace('\n','',trim($value))); 
            if (strpos($category[$key], '<a href=`') === false) {
                // Удалим элемент если в нём нет ссылки
                unset($category[$key]);
            }else{
                // Разделим ссылку и название
                $category[$key] = explode('>',$category[$key]);

                //Получим только ссылку, без тегов HTML
                $category[$key][0] = search_HTML_atrib('href=`', $category[$key][0]);

                // Очистим ссылку от доменов
                $code = 'iherb.com';
                if (strpos($category[$key][0], $code) !== false) {
                    // Оставим ссылку без домена
                    $category[$key][0] = substr($category[$key][0], strpos($category[$key][0], $code)+strlen($code));
                }else{
                    // Если в ссылки нет .iherb.com , то нужно удалить элемент
                    //unset($category[$key]);
                }
            }
        }

        return $category;
    }

    public static function stock_status($html){
        // Найдем статус продажи.
        $text = 'product-stock-status';
        if(strpos($html, $text) !== false){
            return 0;
        }

        $text = 'notify-me-link';
        if(strpos($html, $text) !== false){
            return 0;
        }
        
        return 1;
    }

    //*********************************************************************
    public static function product_specs_list($html){
        $specs_list = [];
        
        // Очистим от всего лишнего
        $html = str_replace('&nbsp;','',$html);
        $html = cut_html_code('<ul id=`product-specs-list`>',$html);
        $html = cut_html_to_code('</ul>',$html);
        $html = explode('</li>', $html);

        // Посмотрим каждый элемент списка LI
        foreach ($html as $key => $value) {

            $value = cut_html_code('<li>', $value);
            $code = '<cms';
            if (strpos($value, $code) !== false) {
                $value = cut_html_to_code($code, $value);
            }
            if ($value!='') {
                $value = explode(':',$value);
                foreach ($value as $key2 => $value2) {
                    $value[$key2] = trim(strip_tags($value2));

                    // 2  строка с весом, надо её немного обрезать
                    if($key==2){
                        $size_ar = explode('  ',$value[$key2]);
                        $value[$key2] = trim($size_ar[0]);
                    }

                    // 6  строка с размерами, надо её немного обрезать
                    if($key==6){
                        $size_ar = explode(',',$value[$key2]);
                        $value[$key2] = trim($size_ar[0]);
                    }
                }
                $html[$key] = $value;

                

                // 7 строка, это сертификаты, нам они не нужны
                if($key==7){
                    unset($html[$key]);
                }
            }else{
                unset($html[$key]);
            }
        }

        //$specs_list['log'] = $html;
        return $html;
    }

    //********************************************************************* */
    public static function save($value){
        SQLQ("INSERT INTO `html_load` (`html`, `type`) VALUES ('{$value['html']}', '{$value['type']}');");
    }
}

?>