<?php

use Algolia\AlgoliaSearch\SearchClient as SearchClient;
use WP_Query as WP_Query;
use WP_Post as WP_Post;

class AlgoliaIndexer
{
    // Add a batch size property
    private $batch_size = 200; // Number of posts to process per batch
    private $daishChainCronEvent = 'pixelkey_algolia_run_daisychain_indexers';
    private $pageIndex = 'last_processed_page_number';
    /**
     * Creates an Algolia search client using the provided credentials.
     *
     * @return \Algolia\AlgoliaSearch\SearchClient|false The Algolia search client instance if successful, false otherwise.
     */
    public function createAlgoliaSearchClient()
    {
        // Retrieve the appId and apiKey from the 'initialize_algolia_key' filter.
        // If the filter does not return an array, the default values of null are used.
        ['appId' => $appId, 'apiKey' => $apiKey] = (array) apply_filters('initialize_algolia_key', []) + ['appId' => null, 'apiKey' => null];

        try {
            if (!$appId || !$apiKey) {
                new \Exception('Algolia credentials not found');
            }

            $algolia = SearchClient::create($appId, $apiKey);
            return $algolia;
        } catch (\Exception $e) {
            PixelkeyAlgolia()->helpers->pixelkey_algolia_log_event('Algolia credentials not found');
            return false;
        }
    }

    /**
     * Process a batch of posts for reindexing.
     */
    public function reindex_post_batch($assoc_args)
    {
        $algolia = $this->createAlgoliaSearchClient();

        $type = isset($assoc_args['type']) ? $assoc_args['type'] : 'post';

        $last_processed_page_number = get_option($this->pageIndex, 1);

        $queryArgs = [
            'post_type' => $type,
            'posts_per_page' => $this->batch_size,
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC',
            'paged' => $last_processed_page_number,
        ];

        if (has_filter('post_query_args')) {
            $queryArgs = apply_filters('post_query_args', $queryArgs);
        }

        $query = new WP_Query($queryArgs);
        $posts = $query->posts;

        if (empty($posts)) {
            // No more posts to process, reset the option
            delete_option($this->pageIndex);
            // also delete the cron job
            if (wp_next_scheduled($this->daishChainCronEvent)) {
                wp_clear_scheduled_hook($this->daishChainCronEvent);
            }
            return;
        }

        $iterator = new Algolia_Post_Iterator($type, $posts);
        
        $index = $algolia->initIndex(
            apply_filters('algolia_index_name', $type)
        );
        $index->saveObjects($iterator);

        // Update the last processed page number
        update_option($this->pageIndex, $last_processed_page_number + 1);
        // Schedule the next batch
        if (!wp_next_scheduled($this->daishChainCronEvent)) {
            wp_schedule_event(time() + 1 * MINUTE_IN_SECONDS, '1min', $this->daishChainCronEvent);
        }
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
    private $type;
    private $posts;
    private $key;

    /**
     * Algolia_Post_Iterator constructor.
     *
     * @param string $type  The type of the posts.
     * @param array  $posts Array of WP_Post objects to be indexed.
     */
    public function __construct($type, array $posts)
    {
        $this->type = $type;
        $this->posts = $posts;
        $this->key = 0;
    }

    /**
     * Return the current element.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->serialize($this->posts[$this->key]);
    }

    /**
     * Move forward to the next element.
     */
    public function next(): void
    {
        ++$this->key;
    }

    /**
     * Return the identifying key of the current element.
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    /**
     * Checks if the current position is valid.
     */
    public function valid(): bool
    {
        return isset($this->posts[$this->key]);
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * Serializes a WP_Post object for Algolia's indexing.
     *
     * @param WP_Post $post The post to serialize.
     *
     * @return array The serialized post. Uses a filter based on the post type.
     */
    private function serialize(WP_Post $post)
    {
        $record = (array) apply_filters($this->type . '_to_record', $post);

        if (!isset($record['objectID'])) {
            $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
        }

        return $record;
    }
}
