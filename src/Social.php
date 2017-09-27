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
    private $cacheAmount;

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
        $this->setCount($count);
        $this->setUseCache(!is_null(getenv('FEEDIE_CACHE')) ? boolval(getenv('FEEDIE_CACHE')) : true);
        $this->setCacheAmount(intval(getenv('FEEDIE_CACHE_AMOUNT')) ?: 20);
        $this->setCacheTime(intval(getenv('FEEDIE_CACHE_TIME')) ?: 60);

        $cacheDir = getenv('FEEDIE_CACHE_DIR');
        if ($cacheDir && file_exists($cacheDir)) {
            $this->setCache($cacheDir . '/feedie.json');
        } else {
            $this->setCache((ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) .'/feedie.json');
        }
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
    public function useCache()
    {
        return $this->useCache;
    }

    /**
     * @param $useCache
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
    }

    /**
     * @return string
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return int
     */
    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * @param $cacheTime
     */
    public function setCacheTime($cacheTime)
    {
        $this->cacheTime = $cacheTime;
    }

    /**
     * @return int
     */
    public function getCacheAmount()
    {
        return $this->cacheAmount;
    }

    /**
     * @param $cacheAmount
     */
    public function setCacheAmount($cacheAmount)
    {
        $this->cacheAmount = $cacheAmount;
    }

    /**
     * The main method for retrieving a combined feed.
     *
     * @return array
     */
    public function getFeed()
    {
        return ($this->useCache() ? $this->getCachedFeed() : $this->makeFeed());
    }

    /**
     * This returns a cached feed if it exists and is not invalid.
     *
     * @return array
     */
    private function getCachedFeed()
    {
        try {
            $cache = file_get_contents($this->getCache());
        } catch (\Exception $e) {
            $cache = false;
        }

        return $cache ? json_decode($cache, true) : $this->makeFeed();
    }

    /**
     * Make the combined feed and cache if necessary.
     *
     * @return array
     */
    private function makeFeed()
    {
        $twitter = (new Twitter($this->getCount() / 2))->getFeed();
        $instagram = (new Instagram($this->getCount() / 2))->getFeed();

        $feed = $this->sortFeed(array_merge($twitter, $instagram));

        if ($this->useCache()) {
            if (!file_exists($this->getCache()) || filemtime($this->getCache()) + $this->getCacheTime() < time()) {
                file_put_contents(
                    $this->getCache(),
                    json_encode(array_slice($feed, 0, $this->getCacheAmount()))
                );
            }
        }

        return $feed;
    }

    /**
     * basic sort a feed by timestamp.
     *
     * @param $feed
     * @return array
     */
    private function sortFeed($feed)
    {
        usort($feed, function ($item1, $item2) {
            if ($item1['date'] == $item2['date']) {
                return 0;
            }

            return $item1['date'] > $item2['date'] ? -1 : 1;
        });

        return $feed;
    }
}
