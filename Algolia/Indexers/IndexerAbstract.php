<?php

namespace PixelKey\Algolia\Indexers;

abstract class IndexerAbstract {
    const DISPLAY_NAME = '';
    const REMOTE_NAME = '';
    const POST_TYPE = '';

    abstract public static function index($ids = false);

}
