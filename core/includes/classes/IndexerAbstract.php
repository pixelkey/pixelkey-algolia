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
     * Saves posts to the Algolia index.
     *
     * @param array $records The records to be saved.
     * @return bool Returns true if the save operation is successful, false otherwise.
     */
    public static function _savePosts($records = [])
    {
        try {
            $index = self::_getIndexObject();
            $index->saveObjects($records);
        } catch (\Exception $e) {
            PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event($e->getMessage());
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

        try {
            $index = $algolia->initIndex(static::REMOTE_NAME);
            return $index;
        } catch (\Exception $e) {
            PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event($e->getMessage());
            return false;
        }
    }

    /**
     * Creates an Algolia search client using the provided credentials.
     *
     * @return \Algolia\AlgoliaSearch\SearchClient|false The Algolia search client instance if successful, false otherwise.
     */
    public static function createAlgoliaSearchClient()
    {
        // Retrieve the appId and apiKey from the 'initialize_algolia_key' filter.
        // If the filter does not return an array, the default values of null are used.
        ['appId' => $appId, 'apiKey' => $apiKey] = (array) apply_filters('initialize_algolia_key', []) + ['appId' => null, 'apiKey' => null];

        try {
            if (!$appId || !$apiKey) new \Exception('Algolia credentials not found');

            $algolia = \Algolia\AlgoliaSearch\SearchClient::create($appId, $apiKey);
            return $algolia;
        } catch (\Exception $e) {
            PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event($e->getMessage());
            return false;
        }
    }
}
