<?php namespace Fryiee\Feedie\API\Contract;

/**
 * Interface FeedInterface
 * @package Fryiee\Feedie\API\Contract
 */
interface FeedInterface
{
    public function getClient();

    public function setClient($client);

    public function getBaseUri();

    public function setBaseUri($baseUri);

    public function getCount();

    public function setCount($count);

    public function getFeed();
}