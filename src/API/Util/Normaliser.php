<?php namespace Fryiee\Feedie\API\Util;

/**
 * Class Normaliser
 * @package Fryiee\Feedie\API\Util
 */
class Normaliser
{
    /**
     * @param $type
     * @param $feed
     * @return array
     */
    public static function normalise($type, $feed)
    {
        $normalisedFeed = [];

        if (count($feed) > 0) {
            foreach ($feed as $post) {
                $normalisedFeed[] = self::{$type}($post);
            }
        }

        return $normalisedFeed;
    }

    /**
     * Normalised post array for twitter posts.
     *
     * @param $post
     * @return array
     */
    private static function twitter($post)
    {
        return [
            'id' => $post->id,
            'type' => 'twitter',
            'date' => strtotime($post->created_at),
            'link' => 'https://twitter.com/'.$post->user->screen_name.'/status/'.$post->id,
            'text' => Linkify::tweet($post),
            'raw_text' => $post->text,
            'image' => null
        ];
    }

    /**
     * Normalised post array for instagram posts.
     *
     * @param $post
     * @return array
     */
    private static function instagram($post)
    {
        return [
            'id' => $post->id,
            'type' => 'instagram',
            'date' => strtotime($post->timestamp),
            'link' => $post->permalink,
            'text' => $post->caption,
            'raw_text' => $post->caption,
            'image' => $post->media_url
        ];
    }
}
