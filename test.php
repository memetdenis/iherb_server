<?php
include 'simplehtmldom_1_9_1/simple_html_dom.php';

// create HTML DOM
$html = file_get_html(`<html><head><title>Cool HTML Parser</title></head><body><h2>PHP Simple HTML DOM Parser</h2><p>PHP Simple HTML DOM Parser is the best HTML DOM parser in any programming language.</p></body></html>`);

// remove all comment elements
foreach($html->find('h2') as $e){
    print_r($e);
}
?>