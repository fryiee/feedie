<?php namespace Fryiee\Feedie\API;

use Fryiee\Feedie\API\Contract\FeedInterface;
use Fryiee\Feedie\API\Util\Normaliser;
use GuzzleHttp\Client;

/**
 * Basic instagram feed loader.
 *
 * Class Instagram
 * @package Theme\Marquee
 */
class Instagram implements FeedInterface
{
    /**
     * @var
     */
    private $baseUri;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $count;

    /**
     * Instagram constructor.
     * @param int $count
     */
    public function __construct($count = 5)
    {
        $this->setCount($count);
        $this->setToken(strval(getenv('FEEDIE_INSTAGRAM_TOKEN')));

        $this->setBaseUri('https://api.instagram.com/v1/users/');
        $this->setClient(new Client(['base_uri' => $this->getBaseUri()]));
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
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getFeed()
    {
        $response = $this->getClient()->get(
            'self/media/recent',
            [
                'query' => [
                    'access_token' => $this->getToken(),
                    'count' => $this->getCount()
                ]
            ]
        );

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $json = json_decode($response->getBody()->getContents());

        return Normaliser::normalise('instagram', $json->data);
    }
}
