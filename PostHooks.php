<?php

namespace PixelKey\Algolia;

use PixelKey\Algolia\Commands\RunIndexers;

class PostHooks {
    public static function init() {
        add_action('save_post', [static::class, 'onPostSaveAndUpdate']);
    }

    public static function onPostSaveAndUpdate($postId) {
        $indexers = RunIndexers::getIndexers();
        $post = get_post($postId);

        foreach($indexers as $indexer) {
            if($indexer::POST_TYPE === $post->post_type && $post->post_status === 'publish') {
                $indexer::index($postId);
            }
        }
    }
}
