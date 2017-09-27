<?php namespace Fryiee\Feedie;

use Fryiee\Feedie\API\Twitter;
use Fryiee\Feedie\API\Instagram;

/**
 * Class Social
 * @package Fryiee\Feedie
 */
class Social
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var string
     */
    private $cache;

    /**
     * @var bool
     */
    private $useCache;

    /**
     * @var int
     */
    private $cacheNumber;

    /**
     * @var int
     */
    private $cacheTime;

    /**
     * Social constructor.
     * @param int $count
     */
    public function __construct($count = 10)
    {
        $this->count = $count;
        $this->useCache = (!is_null(getenv('FEEDIE_CACHE')) ? boolval(getenv('FEEDIE_CACHE')) : true);
        $this->cacheNumber = intval(getenv('FEEDIE_CACHE_AMOUNT')) ?: 20;
        $this->cacheTime = intval(getenv('FEEDIE_CACHE_TIME')) ?: 60;

        $cacheDir = getenv('FEEDIE_CACHE_DIR');
        if ($cacheDir && file_exists($cacheDir)) {
            $this->cache = $cacheDir . '/feedie.json';
        } else {
            $this->cache = (ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) .'/feedie.json';
        }
    }

    /**
     * The main method for retrieving a combined feed.
     *
     * @return array
     */
    public function getFeed()
    {
        return ($this->useCache ? $this->getCachedFeed() : $this->makeFeed());
    }

    /**
     * This returns a cached feed if it exists and is not invalid.
     *
     * @return array
     */
    private function getCachedFeed()
    {
        $cache = json_decode(file_get_contents($this->cache), true);

        return $cache ?: $this->makeFeed();
    }

    /**
     * Make the combined feed and cache if necessary.
     *
     * @return array
     */
    private function makeFeed()
    {
        // do social media loop
        $twitter = (new Twitter($this->count / 2))->getFeed();
        $instagram = (new Instagram($this->count / 2))->getFeed();

        $combinedFeed = array_merge($twitter, $instagram);

        usort($combinedFeed, function ($item1, $item2) {
            if ($item1['date'] == $item2['date']) {
                return 0;
            }

            return $item1['date'] > $item2['date'] ? -1 : 1;
        });

        if ($this->useCache) {
            if (!file_exists($this->cache) || filemtime($this->cache) + $this->cacheTime < time()) {
                file_put_contents($this->cache, json_encode($combinedFeed));
            }
        }

        return $combinedFeed;
    }
}
