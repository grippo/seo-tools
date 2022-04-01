#!/usr/local/bin/php
<?php
libxml_use_internal_errors(true);

if ($argc < 2) {
  fwrite(STDERR, "Usage:\nphp get-basic-tags.php {{ url }} {{ print-header: 0/1 }}\n");
  exit;
}
$url = $argv[1];
$print_header = false;
if (isset($argv[2])) {
    $print_header = ($argv[2] == 1);
}
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
  if ($print_header) echo "URL\tROBOTS AS IS\tROBOTS TO BE\n";
  $robots = '';

  fwrite(STDERR, $url . "'\n");
  $doc = new DOMDocument();
  $doc->loadHTML($content);
  unset($content);
  $xpath = new DOMXPath($doc);


  $query = '/html/head/meta[@name="robots"]/@content';
  fwrite(STDERR, "\t" . $query . "\n");
  $nodes = $xpath->query($query);
  foreach ($nodes as $node) {
    $robots .= $node->nodeValue;
  }


  echo $url . "\t" . $robots . "\t\n";

} else {
  fwrite(STDERR, "Can't get '" . $url . "'\n");
  exit;
}
exit;

?>
