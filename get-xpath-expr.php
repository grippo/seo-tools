#!/usr/local/bin/php
<?php
// cat bor1 | php get-xpath-expr.php > bor2

libxml_use_internal_errors(true);

$options = Array(
  'http' => Array(
    'follow_location' => false,
    'method'=>"GET",
    'header'=>"Accept-language: es\r\n" .
      "Cookie: ui=0001\r\n" .
      "User-Agent: Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)\r\n"
  )
);
$context = stream_context_create($options);

while($url = trim(fgets(STDIN))){
  if ($content = file_get_contents($url, false, $context)) {
    fwrite(STDERR, $url . "\n");
    $doc = new DOMDocument();
    $doc->loadHTML($content);
    unset($content);
    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query('//div[@itemprop="sku"]');
    //  $nodes = $xpath->query("//div[(contains(@class,'info-item-producto') and not(contains(@class,'sin-stock')))]/a/@data-crosss-id-catalog");
    $val = '';
    foreach ($nodes as $node) {
      $val .= $node->nodeValue;
    }
    echo $val . "\t" . $url . "\n";
  } else {
    fwrite(STDERR, "Can't get '" . $url . "'\n");
  }
}
exit;

?>
