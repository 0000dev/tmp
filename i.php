<?php
require 'vendor/autoload.php';

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use duzun\hQuery;

$client = new Client();

$list = file('./list');

$tries = 0;

$requests = function ($total) use ($list) {
    //$uri = 'https://httpbin.org/ip';
    for ($i = 0; $i < $total; $i++) {
        yield new Request('GET', $list[rand(0,count($list)-1)]);
    }
};

$pool = new Pool($client, $requests(1000), [
    'concurrency' => 5,
    'fulfilled' => function ($response, $index) use (&$tries) {
        // this is delivered each successful response
        $tries++;
		$body = $response->getBody();	
		$doc = hQuery::fromHTML($body);
		$line = $title = $doc->find('title').PHP_EOL;
		echo $tries.' '.$line;
		file_put_contents('./result', $line, FILE_APPEND);
		file_put_contents('./last_result'.rand(0,3).'.html', $body);

    },
    'rejected' => function ($reason, $index) use (&$tries){
        // this is delivered each failed request
        $tries++;
        $message = $reason->getMessage();
        if (preg_match('#resulted in a `.+?`#', $message, $ok))
	        $message = $ok[0];
	    echo $tries.' '.$message.PHP_EOL;
        file_put_contents('./result', 'ERROR '.$message.PHP_EOL  , FILE_APPEND);
    },
]);