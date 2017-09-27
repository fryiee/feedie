<?php namespace Fryiee\Feedie\API\Util;

/**
 * Class Linkify
 * @package Fryiee\Feedie\API\Util
 */
class Linkify
{
    /**
     * Return a linkified version of a tweet.
     *
     * @param $post
     * @return mixed
     */
    public static function tweet($post)
    {
        $text = $post->text;

        $linkified = array();
        foreach ($post->entities->hashtags as $hashtag) {
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
        foreach ($post->entities->user_mentions as $userMention) {
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
        foreach ($post->entities->urls as $url) {
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
