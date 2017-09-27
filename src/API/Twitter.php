<?php namespace Fryiee\Feedie\API;

use Fryiee\Feedie\API\Contract\FeedInterface;
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
     * @var int
     */
    private $count;

    /**
     * @var bool
     */
    private $excludeReplies;

    /**
     * @var Client
     */
    private $client;

    /**
     * Twitter constructor.
     * @param int $count
     * @param bool $excludeReplies
     */
    public function __construct($count = 5, $excludeReplies = true)
    {
        $this->count = intval($count);
        $this->excludeReplies = boolval($excludeReplies);

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
     * @return array|bool|object
     */
    public function getFeed()
    {

        try {
            $response = $this->getClient()->get(
                'statuses/user_timeline',
                [
                    'query' => [
                        'count' => $this->count,
                        'exclude_replies' => $this->excludeReplies
                    ]
                ]
            );

            $json = json_decode($response->getBody()->getContents());

            return (isset($json) ? $this->normaliseFeed($json) : false);
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
     * Normalise a twitter feed for Feedie\Social.
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
                    'type' => 'twitter',
                    'date' => strtotime($post->created_at),
                    'link' => 'https://twitter.com/'.$post->user->screen_name.'/status/'.$post->id,
                    'text' => $this->linkifyTweet($post),
                    'raw_text' => $post->text
                ];
            }
        }

        return $normalisedFeed;
    }

    /**
     * Return a linkified version of a tweet.
     *
     * @param $tweetObject
     * @return mixed
     */
    private function linkifyTweet($tweetObject)
    {
        $text = $tweetObject->text;

        $linkified = array();
        foreach ($tweetObject->entities->hashtags as $hashtag) {
            $hash = $hashtag->text;

            if (in_array($hash, $linkified)) {
                continue; // do not process same hash twice or more
            }
            $linkified[] = $hash;

            // replace single words only, so looking for #Google we wont linkify >#Google<Reader
            $text = preg_replace(
                '/#\b' . $hash . '\b/',
                sprintf(
                    '<a href="https://twitter.com/search?q=%%23%2$s&src=hash">#%1$s</a>',
                    $hash,
                    urlencode($hash)
                ),
                $text
            );
        }

        // user_mentions
        $linkified = array();
        foreach ($tweetObject->entities->user_mentions as $userMention) {
            $name = $userMention->name;
            $screenName = $userMention->screen_name;

            if (in_array($screenName, $linkified)) {
                continue; // do not process same user mention twice or more
            }
            $linkified[] = $screenName;

            // replace single words only, so looking for @John we wont linkify >@John<Snow
            $text = preg_replace(
                '/@\b' . $screenName . '\b/',
                sprintf(
                    '<a href="https://www.twitter.com/%1$s" title="%2$s">@%1$s</a>',
                    $screenName,
                    $name
                ),
                $text
            );
        }

        // urls
        $linkified = array();
        foreach ($tweetObject->entities->urls as $url) {
            $url = $url->url;

            if (in_array($url, $linkified)) {
                continue; // do not process same url twice or more
            }
            $linkified[] = $url;

            $text = str_replace($url, sprintf('<a href="%1$s">%1$s</a>', $url), $text);
        }

        return $text;
    }
}