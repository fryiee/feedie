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
     * Social constructor.
     * @param int $count
     */
    public function __construct($count = 10)
    {
        $this->count = $count;
    }

    public function generateCombinedFeed()
    {
        // do social media loop
        $twitter = new Twitter($this->count / 2);
        $instagram = new Instagram($this->count / 2);

        $normalisedTwitter = $this->normaliseFeed('twitter', $twitter->getFeed());
        $normalisedInstagram = $this->normaliseFeed('instagram', $instagram->getFeed());

        $combinedFeed = array_merge($normalisedTwitter, $normalisedInstagram);

        usort($combinedFeed, function ($item1, $item2) {
            if ($item1['date'] == $item2['date']) {
                return 0;
            }
            return $item1['date'] > $item2['date'] ? -1 : 1;
        });

        return $combinedFeed;
    }

    public function normaliseFeed($type, $feed)
    {
        $normalisedFeed = [];

        if (count($feed) > 0) {
            foreach ($feed as $post) {
                $normalisedPost = [
                    'id' => $post->id,
                    'type' => $type
                ];

                if (isset($post->created_at)) {
                    $normalisedPost['date'] = strtotime($post->created_at);
                } elseif (isset($post->created_time)) {
                    $normalisedPost['date'] = intval($post->created_time);
                }

                if ($type == 'twitter') {
                    $normalisedPost['link'] = 'https://twitter.com/'.$post->user->screen_name.'/status/'.$post->id;
                    $normalisedPost['text'] = $this->linkifyTweet($post);
                } else {
                    $normalisedPost['link'] = $post->link;
                    $normalisedPost['image'] = $post->images->standard_resolution->url;
                }

                $normalisedFeed[] = $normalisedPost;
            }
        }

        return $normalisedFeed;
    }

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