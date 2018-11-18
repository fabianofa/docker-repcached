<?php


$cache1Server = new Memcached();
$cache1Server->addServers([
	['127.0.0.1', '11211']
]);
$cache1Status = false;


$cache2Server = new Memcached();
$cache2Server->addServers([
	['127.0.0.1', '11212']
]);
$cache2Status = false;


while (true) {
	$date = new \DateTime;
	system('clear');
	echo "cache1 Status: ";
	if (!$cache1Server->getStats()) {
		echo "\e[0;31moffline\e[0m";
		$cache1Status = false;
	} else {
		echo "\e[1;32monline\e[0m";
		$cache1Status = true;
	}
	echo PHP_EOL;
	echo "cache2 Status: ";
	if (!$cache2Server->getStats()) {
		echo "\e[0;31moffline\e[0m";
		$cache2Status = false;
	} else {
		echo "\e[1;32monline\e[0m";
		$cache2Status = true;
	}
	echo PHP_EOL;
	echo PHP_EOL;


	echo "Attempting to read keys from cache1" . PHP_EOL;
	$cache1Keys = $cache1Server->getAllKeys() ? $cache1Server->getAllKeys() : [];
	echo "Keys available at cache1 are: " . implode(", ", $cache1Keys) . PHP_EOL;
	if (!$cache1Status) {
		echo "Looks like cache1 is down" . PHP_EOL . PHP_EOL;
	}

	echo PHP_EOL;

	echo "Attempting to read keys from cache2" . PHP_EOL;
	$cache2Keys = $cache2Server->getAllKeys() ? $cache2Server->getAllKeys() : [];
	echo "Keys available at cache2 are: " . implode(", ", $cache2Keys) . PHP_EOL;
	if (!$cache2Status) {
		echo "Looks like cache2 is down too" . PHP_EOL . PHP_EOL;
	}

	echo PHP_EOL;
	echo PHP_EOL;

	echo "Last update: " . $date->format("Y-m-d H:i:s");
	sleep(1);
}
// var_dump($cache1Server->getStats());
// var_dump($cache2Server->getStats());


// $memcached = new Memcached();
// $memcached->addServers([
// 	['127.0.0.1', '11211', 20],
// 	// ['127.0.0.1', '11212', 10]
// ]);


// var_dump($memcached->get('hello'));
