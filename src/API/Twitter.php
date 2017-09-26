<?php namespace Fryiee\Feedie\API;

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class Twitter
 * @package Theme\Marquee
 */
class Twitter
{
    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $consumerSecret;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $accessTokenSecret;

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
     * @param bool $excludeReplies
     */
    public function __construct($count = 20, $excludeReplies = true)
    {
        $this->count = intval($count);
        $this->consumerKey = env('TWITTER_CONSUMER_KEY');
        $this->consumerSecret = env('TWITTER_CONSUMER_SECRET');
        $this->accessToken = env('TWITTER_ACCESS_TOKEN');
        $this->accessTokenSecret = env('TWITTER_ACCESS_TOKEN_SECRET');
        $this->excludeReplies = boolval($excludeReplies);
    }

    /**
     * @return array|object
     */
    public function getFeed()
    {
        $connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->accessToken, $this->accessTokenSecret);
        $statuses = $connection->get('statuses/user_timeline', ['count' => $this->count, 'exclude_replies' => $this->excludeReplies]);

        return $statuses;
    }
}