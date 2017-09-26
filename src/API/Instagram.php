<?php namespace Fryiee\Feedie\API;

/**
 * Basic instagram feed loader.
 *
 * Class Instagram
 * @package Theme\Marquee
 */
class Instagram
{

    /**
     * @var string
     */
    private $endpointBase = 'https://api.instagram.com/v1/users/';

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
    public function __construct($count = 20)
    {
        $this->user = env('INSTAGRAM_USER_ID');
        $this->token = env('INSTAGRAM_TOKEN');
        $this->count = intval($count);
    }

    /**
     * @return mixed
     */
    public function getFeed()
    {
        $result = file_get_contents($this->endpointBase . $this->user . "/media/recent/?access_token=" . $this->token . '&count=' . $this->count);
        $json = json_decode($result);
        return (!$json ?: $json->data);
    }
}