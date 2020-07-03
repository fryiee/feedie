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
     * @var bool
     */
    private $alternate;

    /**
     * Social constructor.
     * @param int $count
     * @param bool $alternate
     */
    public function __construct($count = 10, $alternate = false)
    {
        $this->setAlternate($alternate);
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

    public function getAlternate()
    {
        return $this->alternate;
    }

    public function setAlternate($alternate)
    {
        $this->alternate = $alternate;
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
        if (file_exists($this->getCache())) {
            $cache = json_decode(file_get_contents($this->getCache()), true);
        }

        if (isset($cache) && count($cache) >= $this->getCount()) {
            if (count($cache) > $this->getCount()) {
                return array_slice($cache, 0, $this->getCount());
            } else {
                return $cache;
            }
        } else {
            return $this->makeFeed();
        }
    }

    /**
     * Make the combined feed and cache if necessary.
     *
     * @return array
     */
    private function makeFeed()
    {
        $half = round($this->getCount() / 2, PHP_ROUND_HALF_UP);
        $twitter = (new Twitter($half))->getFeed();
        $instagram = (new Instagram($half))->getFeed();

        $feed = $this->sortFeed($twitter, $instagram);

        if ($this->useCache()) {
            if (!file_exists($this->getCache()) || filemtime($this->getCache()) + $this->getCacheTime() < time()) {
                file_put_contents(
                    $this->getCache(),
                    json_encode(array_slice($feed, 0, $this->getCacheAmount()))
                );
            }
        }

        if (count($feed) > $this->getCount()) {
            $feed = array_slice($feed, 0, $this->getCount());
        }

        return $feed;
    }

    /**
     * @param $twitter
     * @param $instagram
     * @return array|mixed
     */
    private function sortFeed($twitter, $instagram)
    {
        if ($this->getAlternate() && count($twitter) == count($instagram)) {
            // we know that we can grab them one by one and they'll be the same size anyway
            $feed = [];
            $toggle = $this->getAlternate();
            $total = count($twitter) + count($instagram);
            for ($x = 0; $x < $total; $x++) {
                $feed[] = array_shift(${$toggle});
                $toggle = ($toggle == 'twitter' ? 'instagram' : 'twitter');
            }
        } else {
            $feed = array_merge($twitter, $instagram);
            usort($feed, function ($item1, $item2) {
                if ($item1['date'] == $item2['date']) {
                    return 0;
                }

                return $item1['date'] > $item2['date'] ? -1 : 1;
            });
        }

        return $feed;
    }
}
