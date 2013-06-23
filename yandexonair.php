<?php

# file with result
$file = './result';
$perf = 'yandexonair';

$k_count = $perf.'_count';
$k_key	 = $perf.'_key_';
$cache = new Memcached();
$cache->addServer('127.0.0.1', 11211);

/*
 * # delete keys from Memcached by perfix
 * foreach(explode("\n", `memdump --server 127.0.0.1`) as $key) {
 *	if(preg_match('%yandexonair%', $key))
 *		$cache->delete($key);
 * }
 * exit;
 * # or just restart memcached for remove all key
*/

$cache->set($k_count, $cache->get($k_count) ? $cache->get($k_count) : 0);


$numquery = 1;

if(file_exists($file)) {
	$list = array_map('trim', file($file));
	foreach($list as $key) {
		$cache->set($k_key.md5($key), true);
	}
}
$handle = fopen('./result', 'a+');

while(true) {
	print "[+] query: {$numquery}\n";
	$curl = `curl -b -e 'Referer: http://swf.static.yandex.net/queries/live-broadcast.swf?v=10.34.5&data-url=http://livequeries-front.corba.yandex.net/queries/' --user-agent "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0" 'http://livequeries-front.corba.yandex.net/queries/?ll1=-1.7356459922329044,-9.520975999999978&ll2=84.54750547846963,216.88527400000004&limit=50000&nc=0.027750199660658836' 2>&1`;
	preg_match_all('#query text="([^\"]+)"#isU', $curl, $matches);

	print count($matches[1]) ? "[+] Matches count: ".count($matches[1])."\n" : "[-] Matches not found\n";

	foreach($matches[1] as $key) 
	{
		$md5 = $k_key.md5($key);
		if(!$cache->get($md5)) {
			$cache->set($k_key.md5($key), true);
			$cache->set($k_count, $cache->get($k_count)+1);
			fwrite($handle, $key."\n");
		}
	}
	print "[~] Uniq: ".$cache->get($k_count)."\n~~~~~~~~~~~~~~~~~~~~~~\n";
	$numquery++;
}
fclose($handle);
