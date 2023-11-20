<?php

namespace PixelKey\Algolia;

abstract class IndexerAbstract
{
    const DISPLAY_NAME = '';
    const REMOTE_NAME = '';
    const POST_TYPE = '';

    abstract public static function index($ids = []);

    /**
     * Removes objects from the Algolia index.
     *
     * @param array $ids The array of object IDs to be removed.
     * @return bool Returns true if the objects are successfully removed, false otherwise.
     */
    public static function remove($ids = [])
    {
        if (count($ids) < 1) return false;

        $index = self::_getIndexObject();

        foreach ($ids as $id) {
            $index->deleteObject($id);
        }

        return true;
    }

    /**
     * Creates an Algolia search client using the provided credentials.
     *
     * @return \Algolia\AlgoliaSearch\SearchClient|false The Algolia search client instance if successful, false otherwise.
     */
    public static function createAlgoliaSearchClient()
    {
        $key = (array) apply_filters('initialize_algolia_key', []);
        $appId = key_exists('appId', $key) ? $key['appId'] : null;
        $apiKey = key_exists('apiKey', $key) ? $key['apiKey'] : null;

        try {
            if (!$appId || !$apiKey) {
                new \Exception('Algolia credentials not found');
            }

            $algolia = \Algolia\AlgoliaSearch\SearchClient::create($appId, $apiKey);
            return $algolia;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves the Algolia index object.
     *
     * @return \Algolia\Search\Index|false The Algolia index object if successful, false otherwise.
     */
    public static function _getIndexObject()
    {
        $algolia = self::createAlgoliaSearchClient();
        $indexName = static::REMOTE_NAME;

        try {
            $index = $algolia->initIndex($indexName);
            return $index;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Saves posts to the Algolia index.
     *
     * @param array $records The records to be saved.
     * @return bool Returns true if the save operation is successful, false otherwise.
     */
    public static function _savePosts($records = [])
    {
        try {
            $index = static::_getIndexObject();
            $index->saveObjects($records);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
