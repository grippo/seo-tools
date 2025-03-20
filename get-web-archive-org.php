#!/usr/local/bin/php
<?php
include "phpUri.php";
libxml_use_internal_errors(true);

if ($argc < 2) {
  fwrite(STDERR, "Usage:\nphp get-http-links.php {{ url }}\n");
  exit;
}
$url = $argv[1];
$debug = false;
$options = Array(
  'http' => Array(
    'follow_location' => false,
    'method'=>"GET",
    'header'=>"Accept-language: es\r\n" .
      "Cookie: ui=0001\r\n" .
      "User-Agent: Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)\r\n"
  )
);

$matches = array();
$subject = $url;
$web_archive_prefix = 'https://web.archive.org/web/';
$web_archive_prefix_re = 'https:\/\/web.archive.org\/web\/';
$pattern = '/^' . $web_archive_prefix_re . '(\d{14})\/((http|https):\/\/(.*))$/';
preg_match($pattern, $subject, $matches);
if ($debug) fwrite(STDERR, print_r($matches, true));
$web_archive_date = $matches[1];
$web_archive_url = $matches[2];
if ($debug) fwrite(STDERR, 'Date: ' . $web_archive_date . "\n");
if ($debug) fwrite(STDERR, 'Url: ' . $web_archive_url . "\n");

$web_archive_url_info = parse_url($url);
if ($debug) fwrite(STDERR, print_r($web_archive_url_info, true));
$web_archive_base = phpUri::parse($web_archive_url_info['host']);

$url_info = parse_url($web_archive_url);
if ($debug) fwrite(STDERR, print_r($url_info, true));
$base = phpUri::parse($url_info['host']);

$downloadh = Array();
$downloadi = Array();
$context = stream_context_create($options);
if ($content = file_get_contents($url, false, $context)) {
  if ($debug) fwrite(STDERR, $url . "'\n");

  
  $doc = new DOMDocument();
  $doc->loadHTML($content);
  unset($content);
  $nodes = $doc->getElementsByTagName('a');
  foreach ($nodes as $node) {
    $that_url = $node->getAttribute('href');
    if ($debug) echo "'" . $that_url . "':\n";
    $this_url = preg_replace('/' . $web_archive_prefix_re . $web_archive_date . '\//i', '', $that_url);
    $this_url = preg_replace('/' . '\/web\/' . $web_archive_date . '\//i', '', $this_url);
    $this_url_info = parse_url($this_url);
    if (array_key_exists('host', parse_url($this_url))) {
      if (parse_url($this_url)['host'] === $url_info['host']) {
        if ($debug) echo "\t" . $this_url . "\n";
        $node->setAttribute('href', $this_url);
        $downloadh[$this_url] = $that_url;
      } else {
    //    echo "\t" . $this_url_info['host'] . " != " . $url_info['host'] . "\n";
      }
    } else {
    //  echo "\thost == null\n";
    }
  }
  $nodes = $doc->getElementsByTagName('img');
  foreach ($nodes as $node) {
    $that_url = $node->getAttribute('src');
    if ($debug) echo "'" . $that_url . "':\n";
    $this_url = preg_replace('/' . $web_archive_prefix_re . $web_archive_date . '\//i', '', $that_url);
    $this_url = preg_replace('/' . '\/web\/' . $web_archive_date . '\//i', '', $this_url);
    $this_url = preg_replace('/' . '\/web\/' . $web_archive_date . 'im_\//i', '', $this_url);
    $this_url_info = parse_url($this_url);
    if (array_key_exists('host', parse_url($this_url))) {
      if (parse_url($this_url)['host'] === $url_info['host']) {
        if ($debug) echo "\t" . $this_url . "\n";
        $node->setAttribute('src', $this_url);
        $downloadi[$this_url] = $that_url;
      } else {
      //  echo "\t" . $this_url_info['host'] . " != " . $url_info['host'] . "\n";
      }
    } else {
    //  echo "\thost == null\n";
    }
  }
  $html = $doc->saveHTML();
  $html = preg_replace('/^(.*)<!-- END WAYBACK TOOLBAR INSERT -->/s', '', $html);
  $html = preg_replace('/<\/html>(.*)$/s', '</html>', $html);
  echo $html;
  fwrite(STDERR,  "\n\n======== download ========\n");
  foreach ($downloadh as $k => $v) {
    $this_v = phpUri::parse('https://web.archive.org/')->join($v);
    $this_v = preg_replace('/\/http:\//', '/http://', $this_v);
    $this_v = preg_replace('/\/https:\//', '/https://', $this_v);
    fwrite(STDERR,  'php get-web-archive-org,php ' . $this_v . ' -o ' . getcwd() . parse_url($k)['path'] . "\n");
  }
  foreach ($downloadi as $k => $v) {
    $this_v = phpUri::parse('https://web.archive.org/')->join($v);
    $this_v = preg_replace('/\/http:\//', '/http://', $this_v);
    $this_v = preg_replace('/\/https:\//', '/https://', $this_v);
    fwrite(STDERR,  'curl ' . $this_v . ' -o ' . getcwd() . parse_url($k)['path'] . "\n");
  }
} else {
  fwrite(STDERR, "Can't get '" . $url . "'\n");
  exit;
}
exit;

?>
