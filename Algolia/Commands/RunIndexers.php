<?php

namespace PixelKey\Algolia\Commands;

use PixelKey\Algolia\Indexers\IndexerAbstract;
use PixelKey\Algolia\Setup;

class RunIndexers implements CommandInterface {
    public const INDEXERS = [];

    public static function run($indexers = false) {
        if(!is_plugin_active(Setup::PLUGIN_NAME)) return;

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

            $instance::index();
        }
    }

    public static function getIndexers() {
        $indexers = self::INDEXERS;
        $collectIndexers = [];
        $collectIndexers = apply_filters( 'pixelkey_algolia_add_custom_indexer', $collectIndexers);

        $indexers = array_merge($indexers, $collectIndexers);

        return $indexers;
    }
}
