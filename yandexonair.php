<?php

$n = 1;
$h = fopen('./result', 'w');
while(true) {
	print "[+] query: {$n}\n";
	$curl = `curl -b -e 'Referer: http://swf.static.yandex.net/queries/live-broadcast.swf?v=10.34.5&data-url=http://livequeries-front.corba.yandex.net/queries/' --user-agent "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0" 'http://livequeries-front.corba.yandex.net/queries/?ll1=-1.7356459922329044,-9.520975999999978&ll2=84.54750547846963,216.88527400000004&limit=50000&nc=0.027750199660658836' 2>&1`;
	preg_match_all('#query text="([^\"]+)"#isU', $curl, $matches);

	print count($matches[1]) ? "[+] Matches count: ".count($matches[1])."\n" : "[-] Matches not found\n";

	foreach($matches[1] as $query) {
		if(!isset($array[md5($query)])) {
			$array[md5($query)] = true;
			fwrite($h, $query."\n");
		}
	}
	print "[~] Uniq: ".count($array)."\n~~~~~~~~~~~~~~~~~~~~~~\n";
	$n++;
	time_nanosleep(0, 500000000);
}
fclose($h);