<?php

namespace PixelKey\Algolia\Indexers;

abstract class IndexerAbstract {
    const DISPLAY_NAME = '';
    const REMOTE_NAME = '';

    abstract public static function index();

}
