<?php

namespace Akhela\WebsiteCarbonCalculator\Traits;

trait HelpersTraits {

    private static function asyncRequests(array $requests): array
    {
        // Guzzle with Async and promises
        // https://medium.com/@ardanirohman/how-to-handle-async-request-concurrency-with-promise-in-guzzle-6-cac10d76220e

        // Generators - yield
        // https://www.php.net/manual/en/language.generators.syntax.php

        // Guzzle with generators
        // http://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests

        // Placeholders for the data we are handling
        $responses = [];

        // Setup a guzzle client
        $client = new \GuzzleHttp\Client([
            'timeout' => 300,
            'verify' => false // Some Fibre clients need this. Morpheus gym design, unicef forms
        ]);

        // We need to pass in $fulfilled and $rejected by reference so we can
        // push data in to it and return everything when we are done.
        // https://github.com/guzzle/guzzle/issues/1155#issuecomment-117836887
        $pool = new \GuzzleHttp\Pool($client, self::generateRequests($requests), [
            'concurrency' => 100,
            'fulfilled' => function ($response, $index) use (&$responses) {
                $responses[$index] = json_decode($response->getBody());
            },
            'rejected' => function ($reason, $index) use (&$responses) {
                $responses[$index] = $reason;
            },
        ]);
        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $response = $promise->wait();

        return $responses;
    }

    private static function generateRequests(array $requests)
    {
        foreach($requests as $key => $request){

            $method = !empty($request['type']) ? $request['type'] : 'GET';
            $url = $request['url'];
            $timeout = !empty($request['timeout']) ? $request['timeout'] : 10;
            $headers = !empty($request['headers']) ? $request['headers'] : [];

            $headers['timeout'] = $timeout;
            // https://stackoverflow.com/questions/34577278/guzzle-not-sending-psr-7-post-body-correctly#answer-34577980
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';

            $body = !empty($request['data']) ? http_build_query($request['data']) : '';

            yield $key => new \GuzzleHttp\Psr7\Request($method, $url, $headers, $body);
        }
    }

	private static function calculateTransferedBytes(array $items): int {
        return array_reduce($items, function($carry, $item) {
            if(property_exists($item, 'transferSize')){
                $carry = $carry + $item->transferSize;
            }

            return $carry;
        }, 0);
    }
}
