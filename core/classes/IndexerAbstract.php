<?php

namespace PixelKey\Algolia;

abstract class IndexerAbstract {
    const DISPLAY_NAME = '';
    const REMOTE_NAME = '';
    const POST_TYPE = '';

    abstract public static function index($ids = []);

    abstract public static function remove($ids = []);

}
