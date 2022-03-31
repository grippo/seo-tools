#!/usr/local/bin/php
<?php
libxml_use_internal_errors(true);

if ($argc < 2) {
  fwrite(STDERR, "Usage:\nphp check-hreflang.php {{ url }}\n");
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
  $todo = Array();
  $alternates = Array();
  fwrite(STDERR, $url . "'\n");
  $doc = new DOMDocument();
  $doc->loadHTML($content);
  unset($content);
  $xpath = new DOMXPath($doc);
  $nodes = $xpath->query('/html/head/link');
  foreach ($nodes as $node) {
    if ($node->getAttribute('hreflang') ) {
      if ($node->getAttribute('rel') == 'alternate' ) {
        echo "\t";
        echo $node->getAttribute('hreflang') . " - ";
        echo $node->getAttribute('href');
        echo "\n";
        $alternates[$node->getAttribute('hreflang')] = $node->getAttribute('href');
        if ($node->getAttribute('hreflang') == 'x-default') {
        } elseif ($node->getAttribute('href') == $url) {
        } else {
          $todo[] = $node->getAttribute('href');
        }
      }
    }
  }

  foreach ($todo AS $href) {
    if ($content = file_get_contents($href, false, $context)) {
      $blternates = Array();
      $btodo = Array();
      fwrite(STDERR, $href . "'\n");
      $doc = new DOMDocument();
      $doc->loadHTML($content);
      unset($content);
      $xpath = new DOMXPath($doc);
      $nodes = $xpath->query('/html/head/link');
      foreach ($nodes as $node) {
        if ($node->getAttribute('hreflang') ) {
          if ($node->getAttribute('rel') == 'alternate' ) {
            echo "\t";
            echo $node->getAttribute('hreflang') . " - ";
            echo $node->getAttribute('href');
            echo "\n";
            $blternates[$node->getAttribute('hreflang')] = $node->getAttribute('href');
            if ($node->getAttribute('hreflang') == 'x-default') {
            } elseif ($node->getAttribute('href') == $href) {
            } else {
              $btodo[] = $node->getAttribute('href');
            }
          }
        }
      }
      if ($alternates == $blternates) {
        echo "Alternates OK\n";
      } else {
        fwrite(STDERR, "Alternates are not the same\n");
      }
    }
  }

} else {
  fwrite(STDERR, "Can't get '" . $url . "'\n");
  exit;
}
exit;

?>
