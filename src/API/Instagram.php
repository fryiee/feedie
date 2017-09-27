<?php namespace Fryiee\Feedie\API;

use Fryiee\Feedie\API\Contract\FeedInterface;
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
     * @var
     */
    private $user;

    /**
     * @var
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
        $this->user = getenv('FEEDIE_INSTAGRAM_USER');
        $this->token = getenv('FEEDIE_INSTAGRAM_TOKEN');
        $this->count = intval($count);

        $this->setBaseUri('https://api.instagram.com/v1/users/');
        $this->setClient(new Client(['base_uri' => $this->getBaseUri()]));
    }

    /**
     * @return mixed
     */
    public function getFeed()
    {
        try {
            $response = $this->getClient()->get(
                'media/recent',
                [
                    'query' => [
                        'access_token' => $this->token,
                        'count' => $this->count
                    ]
                ]
            );

            $json = json_decode($response->getBody()->getContents());

            return (isset($json->data) ? $this->normaliseFeed($json->data) : false);
        } catch (\Exception $e) {
            return false;
        }
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
     * Normalise an instagram feed.
     *
     * @param $feed
     * @return array
     */
    private function normaliseFeed($feed)
    {
        $normalisedFeed = [];

        if (count($feed) > 0) {
            foreach ($feed as $post) {
                $normalisedFeed[] = [
                    'id' => $post->id,
                    'type' => 'instagram',
                    'date' => intval($post->created_time),
                    'link' => 'https://twitter.com/'.$post->user->screen_name.'/status/'.$post->id,
                    'image' => $post->images->standard_resolution->url
                ];
            }
        }

        return $normalisedFeed;
    }
}