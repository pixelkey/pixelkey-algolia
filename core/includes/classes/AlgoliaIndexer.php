<?php

class AlgoliaIndexer
{
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
            PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event('Algolia credentials not found');
            return false;
        }
    }

    /**
     * Method reindex_post_atomic
     *
     * This method is responsible for reindexing a single post atomically. 
     * It ensures that the post is indexed in a way that doesn't interfere with other operations, 
     * providing a level of isolation between the indexing of individual posts.
     *
     * @param int $post_id The ID of the post to be reindexed.
     *
     * @return void
     *
     * @throws Exception If there is an error during the reindexing process.
     */
    public static function reindex_post_atomic($assoc_args)
    {
        $algolia = self::createAlgoliaSearchClient();

        $type = isset($assoc_args['type']) ? $assoc_args['type'] : 'post';

        $index = $algolia->initIndex(
            apply_filters('algolia_index_name', $type)
        );

        $queryArgs = [
            'posts_per_page' => 100,
            'post_status' => 'publish',
        ];

        if (has_filter('post_query_args')) {
            $queryArgs = apply_filters('post_query_args', $queryArgs);
        }

        $iterator = new Algolia_Post_Iterator($type, $queryArgs);

        $index->replaceAllObjects($iterator);

        PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event('Iterator Reindexing complete');
    }
}

/**
 * Class Algolia_Post_Iterator
 *
 * The Algolia_Post_Iterator class is used to iterate over a collection of posts.
 * It provides a way to access the elements of an aggregate object sequentially without exposing its underlying representation.
 * It allows for efficient looping over a large collection of posts without loading them all into memory at once.
 *
 * @package pixelkey-algolia
 */
class Algolia_Post_Iterator implements Iterator
{
    /**
     * @var array
     */
    private $queryArgs;

    private $key;

    private $paged;

    private $posts;
    private $type;

    public function __construct($type, array $queryArgs = [])
    {
        $this->type = $type;
        $this->queryArgs = ['post_type' => $type] + $queryArgs;
    }

    /**
     * Return the current element.
     */
    public function current()
    {
        return $this->serialize($this->posts[$this->key]);
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP.
     */
    public function next(): void
    {
        ++$this->key;
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP.
     */
    public function key()
    {
        $this->key;
    }

    /**
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        if (isset($this->posts[$this->key])) {
            return true;
        }

        $this->paged++;
        $query = new \WP_Query(['paged' => $this->paged] + $this->queryArgs);

        if (!$query->have_posts()) {
            return false;
        }

        $this->posts = $query->posts;
        $this->key = 0;

        return true;
    }

    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     */
    public function rewind(): void
    {
        $this->key = 0;
        $this->paged = 0;
        $this->posts = [];
    }

    private function serialize(\WP_Post $post)
    {
        $record = (array) apply_filters($this->type . '_to_record', $post);

        if (!isset($record['objectID'])) {
            $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
        }

        return $record;
    }
}
