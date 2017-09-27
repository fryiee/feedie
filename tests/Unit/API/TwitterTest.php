<?php

class TwitterTest extends \PHPUnit_Framework_TestCase
{
    public function testFeedResponseAsFalseWithNoDetails()
    {
        $feed = new \Fryiee\Feedie\API\Twitter();

        $this->assertFalse($feed->getFeed());
    }

    public function testRequestParameters()
    {
        $container = [];
        $history = \GuzzleHttp\Middleware::history($container);

        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push($history);

        $feed = new \Fryiee\Feedie\API\Twitter();
        $feed->setClient(new \GuzzleHttp\Client(['base_uri' => $feed->getBaseUri(), 'handler' => $stack]));

        $feed->getFeed();

        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $container[0]['request'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals(
            'count=5&exclude_replies=1',
            $request->getUri()->getQuery()
        );
    }

    public function testResponseParameters()
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(__DIR__.'/../../mock/twitter.json'))
        ]);

        $stack = \GuzzleHttp\HandlerStack::create($mock);

        $feed = new \Fryiee\Feedie\API\Twitter();
        $client = new \GuzzleHttp\Client(['base_uri' => $feed->getBaseUri(), 'handler' => $stack]);

        $response = $client->get(
            'statuses/user_timeline',
            [
                'query' => [
                    'count' => 2,
                    'exclude_replies' => false
                ]
            ]
        );

        $this->assertEquals(
            json_decode(file_get_contents(__DIR__.'/../../mock/twitter.json')),
            json_decode($response->getBody()->getContents())
        );
    }
}
