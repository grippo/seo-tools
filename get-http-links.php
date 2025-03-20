#!/usr/local/bin/php
<?php
libxml_use_internal_errors(true);

if ($argc < 2) {
  fwrite(STDERR, "Usage:\nphp get-http-links.php {{ url }}\n");
  exit;
}
$url = $argv[1];
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
if ($content = file_get_contents($url, false, $context)) {
  fwrite(STDERR, $url . "'\n");
  $doc = new DOMDocument();
  $doc->loadHTML($content);
  unset($content);
  $xpath = new DOMXPath($doc);
  $nodes = $xpath->query('//a/@href');
//  $nodes = $xpath->query("//div[(contains(@class,'info-item-producto') and not(contains(@class,'sin-stock')))]/a/@data-crosss-id-catalog");
  foreach ($nodes as $node) {
    echo "\t" . $node->nodeValue . "\n";
  }
} else {
  fwrite(STDERR, "Can't get '" . $url . "'\n");
  exit;
}
exit;

?>
