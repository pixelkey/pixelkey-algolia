<?php

namespace PixelKey\Algolia;

// use PixelKey\Algolia\Indexers\IndexerAbstract;

class RunIndexers implements CommandInterface {
    public static function run($indexers = false) {
        if(!$indexers) {
            $indexers = self::getIndexers();
        }

        /** @var IndexerAbstract $instance */
        foreach ($indexers as $indexer) {
            if(!is_object($indexers)) {
                $instance = new $indexer();
            } else {
                $instance = $indexer;
            }

            if(!$instance instanceof IndexerAbstract) return;

            $instance->index();
        }
    }

    public static function getIndexers() {
        $collectIndexers = [];
        $collectIndexers = apply_filters( 'pixelkey_algolia_add_custom_indexer', $collectIndexers);

        return array_filter($collectIndexers, function($indexer) {
            if($indexer instanceof IndexerAbstract) {
                return true;
            } else {
                return false;
            }
        });
    }

    public static function runAsCron() {
        do_action('pixelkey_algolia:before_index_cron');

        self::run();

        do_action('pixelkey_algolia:after_index_cron');
    }

    public static function testing(){
        echo "Teesting";
        console_log("Testing");
    }
}
