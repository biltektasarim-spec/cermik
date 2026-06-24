<?php
$html = file_get_contents('eo_html.txt');
preg_match_all('/<option value="(\d+)">(.*?)<\/option>/i', $html, $matches);
foreach ($matches[1] as $index => $value) {
    echo $value . " => " . trim($matches[2][$index]) . "\n";
}
?>
