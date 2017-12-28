<?php

/**
 * Benchmark Altorouter
 *
 * Usage: php ./tests/benchmark.php <iterations>
 *
 * Options:
 *
 * <iterations>:
 * The number of routes to map & match. Defaults to 1000.
 */

require __DIR__ . '/functions.php';
require dirname(__DIR__) . '/tests/boot.php';

global $argv;
$n = isset($argv[1]) ? (int)$argv[1] : 1000;

echo "There are generate $n routes. and dynamic route with 50% chance\n\n";


// prepare benchmark data
$requests = [];
for ($i = 0; $i < $n; $i++) {
    $requests[] = [
        'method' => random_request_method(),
        'url' => random_request_url(),
    ];
}

$startMem = memory_get_usage();
$router = new \Inhere\Route\CachedRouter([
    'cacheFile' => __DIR__ . '/cached/bench-routes-cache.php',
    'cacheEnable' => 0,
    'cacheOnMatching' => 0,
    // 'tmpCacheNumber' => 100,
    // 'notAllowedAsNotFound' => 1,
]);

/**
 * collect routes
 */
$start = microtime(true);
foreach ($requests as $r) {
    $router->map($r['method'], $r['url'], 'handler_func');
}
$end = microtime(true);
$buildTime = $end - $start;
echo "Build time ($n routes): ",
pretty_echo(number_format($buildTime, 3), 'cyan'),
" ms, For collect and parse routes.\n\n";

// dump caches
$router->dumpCache();

/**
 * match first route
 */

$r = $requests[0];
$uri = str_replace(['{', '}'], '', $r['url']);

$start = microtime(true);
$ret = $router->match($uri, $r['method']);
$end = microtime(true);
$matchTimeF = $end - $start;
echo 'Match time (first route):  ',
pretty_echo(number_format($matchTimeF, 6)),
" s.\n - URI: {$uri}, will match: {$r['url']}\n";
// echo "Match result: \n" . pretty_match_result($ret) . "\n\n";

/**
 * match random route
 */

// pick random route to match
$r = $requests[random_int(0, $n)];
$uri = str_replace(['{', '}'], '', $r['url']);

$start = microtime(true);
$ret = $router->match($uri, $r['method']);
$end = microtime(true);
$matchTimeR = $end - $start;
echo 'Match time (random route): ',
pretty_echo(number_format($matchTimeR, 6)),
" s.\n - URI: {$uri}, will match: {$r['url']}\n";
// echo "Match result: \n" . pretty_match_result($ret) . "\n\n";

/**
 * match last route
 */
$r = $requests[$n - 1];
$uri = str_replace(['{', '}'], '', $r['url']);

$start = microtime(true);
$ret = $router->match($uri, $r['method']);
$end = microtime(true);
$matchTimeE = $end - $start;
echo 'Match time (last route):   ',
pretty_echo(number_format($matchTimeE, 6)),
" s.\n - URI: {$uri}, will match: {$r['url']}\n";
// echo "Match result: \n" . pretty_match_result($ret) . "\n\n";

/**
 * match unknown route
 */
$start = microtime(true);
$ret = $router->match('/55-foo-bar', 'GET');
$end = microtime(true);
$matchTimeU = $end - $start;
echo 'Match time (unknown route): ', pretty_echo(number_format($matchTimeU, 6)), " s\n";
// echo "Match result: \n" . pretty_match_result($ret) . "\n\n";

// print totals
$totalTime = number_format($buildTime + $matchTimeF + $matchTimeR + $matchTimeU, 5);
echo PHP_EOL . 'Total time: ' . $totalTime . ' s' . PHP_EOL;
echo 'Memory usage: ' . round((memory_get_usage() - $startMem) / 1024) . ' KB' . PHP_EOL;
echo 'Peak memory usage: ' . round(memory_get_peak_usage(true) / 1024) . ' KB' . PHP_EOL;

/*
// 2017.12.3
$ php examples/benchmark.php
There are generate 1000 routes. and dynamic route with 10% chance

Build time (1000 routes): 0.011926 s
Match time (first route): 0.000072 s(URI: /rlpkswupqzo/g)
Match time (random route): 0.000015 s(URI: /muq/vs)
Match time (last route): 0.000013 s(URI: /fneek/aedpctey/v/aaxzpf)
Match time (unknown route): 0.000014 s
Total time: 0.011953 s
Memory usage: 1814 KB
Peak memory usage: 2048 KB

// 2017.12.26
$ php examples/benchmark.php
There are generate 1000 routes. and dynamic route with 50% chance

Build time (1000 routes): 0.017 s, For collect and parse routes.

Match time (first route): 0.000126 s(URI: /frlpz/y/yv/hzmjycn/fyuus/name)
Match time (random route): 0.000012 s(URI: /rt/tbivsuspclfyra/mrys)
Match time (last route): 0.000008 s(URI: /ltinm/mxrtqcbjb)
Match time (unknown route): 0.000015 s

Total time: 0.017024 s
Memory usage: 1078 KB
Peak memory usage: 4096 KB

// 2017.12.26
$ php examples/benchmark.php
There are generate 1000 routes. and no dynamic route

Build time (1000 routes): 0.012 s, For collect and parse routes.

Match time (first route): 0.000221 s(URI: /ltnwon/epwnihhylz/qmd)
Match time (random route): 0.000014 s(URI: /okluuvfaz/bolsgvnjp)
Match time (last route): 0.000009 s(URI: /rwako/vg/x)
Match time (unknown route): 0.000019 s

Total time: 0.012515 s
Memory usage: 1014 KB
Peak memory usage: 2048 KB

 */

