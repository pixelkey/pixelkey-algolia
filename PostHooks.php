<?php

namespace PixelKey\Algolia;

use PixelKey\Algolia\Commands\RunIndexers;

class PostHooks {
    public static function init() {
        add_action('save_post', [static::class, 'onPostSaveAndUpdate']);
        add_action('transition_post_status', [static::class, 'onPostStatusTransition'], 10, 3);
    }

    public static function onPostSaveAndUpdate($postId) {
        $indexers = RunIndexers::getIndexers();
        $post = get_post($postId);

        foreach($indexers as $indexer) {
            if($indexer::POST_TYPE === $post->post_type && $post->post_status === 'publish') {
                $indexer::index([$postId]);
            }
        }
    }

    public static function onPostStatusTransition($newStatus, $oldStatus, $post) {
        if($newStatus == 'publish' || $newStatus == $oldStatus) return;

        $indexers = RunIndexers::getIndexers();
        $post = get_post($post->ID);

        foreach($indexers as $indexer) {
            if($indexer::POST_TYPE === $post->post_type) {
                $indexer::remove([$post->ID]);
            }
        }
    }
}
