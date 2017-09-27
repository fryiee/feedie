<?php

class InstagramTest extends \PHPUnit_Framework_TestCase
{
    public function testFeedResponseAsFalseWithNoDetails()
    {
        $feed = new \Fryiee\Feedie\API\Instagram();

        $this->assertFalse($feed->getFeed());
    }

    public function testRequestParameters()
    {
        $container = [];
        $history = \GuzzleHttp\Middleware::history($container);

        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push($history);

        $feed = new \Fryiee\Feedie\API\Instagram();
        $feed->setClient(new \GuzzleHttp\Client(['base_uri' => $feed->getBaseUri(), 'handler' => $stack]));

        $feed->getFeed();

        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $container[0]['request'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals(
            'access_token='.getenv('FEEDIE_INSTAGRAM_TOKEN').'&count=5',
            $request->getUri()->getQuery()
        );
    }

    public function testResponseParameters()
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
           new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(__DIR__.'/../../mock/instagram.json'))
        ]);

        $stack = \GuzzleHttp\HandlerStack::create($mock);

        $feed = new \Fryiee\Feedie\API\Instagram();
        $client = new \GuzzleHttp\Client(['base_uri' => $feed->getBaseUri(), 'handler' => $stack]);

        $response = $client->get(
            'media/recent',
            [
                'query' => [
                    'access_token' => 'bla',
                    'count' => 5
                ]
            ]
        );

        $this->assertEquals(
            json_decode(file_get_contents(__DIR__.'/../../mock/instagram.json')),
            json_decode($response->getBody()->getContents())
        );
    }
}
