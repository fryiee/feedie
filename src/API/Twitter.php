<?php namespace Fryiee\Feedie\API;

use Fryiee\Feedie\API\Contract\FeedInterface;
use Fryiee\Feedie\API\Util\Normaliser;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

/**
 * Class Twitter
 * @package Theme\Marquee
 */
class Twitter implements FeedInterface
{
    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $count;

    /**
     * @var bool
     */
    private $excludeReplies;


    /**
     * Twitter constructor.
     * @param int $count
     */
    public function __construct($count = 5)
    {
        $this->setCount($count);
        $this->setExcludeReplies(boolval(getenv('FEEDIE_TWITTER_EXCLUDE_REPLIES')) ?: true);

        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key'    => getenv('FEEDIE_TWITTER_CONSUMER_KEY'),
            'consumer_secret' => getenv('FEEDIE_TWITTER_CONSUMER_SECRET'),
            'token'           => getenv('FEEDIE_TWITTER_ACCESS_TOKEN'),
            'token_secret'    => getenv('FEEDIE_TWITTER_ACCESS_TOKEN_SECRET')
        ]);
        $stack->push($middleware);

        $this->setBaseUri('https://api.twitter.com/1.1/');
        $this->setClient(new Client([
            'base_uri' => $this->getBaseUri(),
            'handler' => $stack,
            'auth' => 'oauth',
        ]));
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param $baseUri
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @return mixed
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return bool
     */
    public function getExcludeReplies()
    {
        return $this->excludeReplies;
    }

    /**
     * @param bool $excludeReplies
     */
    public function setExcludeReplies($excludeReplies)
    {
        $this->excludeReplies = $excludeReplies;
    }

    /**
     * @return array|bool
     */
    public function getFeed()
    {
        $response = $this->getClient()->get(
            'statuses/user_timeline.json',
            [
                'query' => [
                    'count' => $this->getCount(),
                    'exclude_replies' => $this->getExcludeReplies()
                ]
            ]
        );

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $json = json_decode($response->getBody()->getContents());

        return Normaliser::normalise('twitter', $json);
    }
}
