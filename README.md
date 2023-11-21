# Pixelkey-algolia v1.0.1

This module provides helper classes to allow you to integreate custom Algolia indexing into your Wordpress applications. It contains some useful hooks, scheduled indexing cron tasks, and an admin interface to see and trigger your custom indexers. Since this is designed to be used to create a custom indexing experience, it's expected you have a fairly good understanding of how Algolia indexing works in general. This module will not index your posts for you, and no default indexing is included.

### Initialize Algolia ``` App_ID ``` and ``` API_Key ```

```
/**
 * This function is a filter callback that initializes the Algolia key.
 *
 * @return array The Algolia key with 'app_id' and 'api_key' values.
 */
add_filter('initialize_algolia_key', function () {
    $algolia_key = [
        'appId' => "xxxxxx",
        'apiKey' => "xxxxxxxxxxxxxxxxxxxxxxxxx",
    ];
    return $algolia_key;
}, 10, 1);

```

### Custom Indexer
To create a custom indexer, you will need to create a new class that extends from the IndexerAbstract class. Lets create an indexer for a custom post type for Articles: `article`.

```
use PixelKey\Algolia\IndexerAbstract;

class CustomArticle extends IndexerAbstract {
    const DISPLAY_NAME = 'Article'; //This is what the indexer is called in the admin area
    const REMOTE_NAME = 'article_index'; //This is the literal name of the target index inside the algolia dashboard
    const POST_TYPE = 'article'; //This is the custom post type we want to index

    public static function index($ids = []){
     /* This function is called by the cron, the admin area, and by post update/edit/save hooks.
      * This is where you would implementyour custom indexing logic. $ids is a list of post ids passed
      * in by the hooks. For a full re-index triggered by the cron or admin, no $ids will be present */
      $articles = static::_getArticles($ids); //Needs to be implemented for your specific use case.
      $indexSuccesful = self::_savePosts($articles);; //You might consider batching these
      
      return $indexSuccesful;
    }

    /**
     * Retrieves articles based on the provided IDs.
     *
     * @param array $ids The IDs of the articles to retrieve.
     * @return array The retrieved articles.
     */
    protected static function _getArticles($ids)
    {
        $arguments = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ];

        if ($ids) {
            $arguments['include'] = $ids;
        }

        return get_posts($arguments);
    }
}
```

### Add the indexer to the site
Now that we have the indexer prepared, we need to add it to the list of indexers available to the module. We can do that using a filter:
```
add_filter( 'pixelkey_algolia_add_custom_indexer', function($collector) {
  $collector[] = new Indexers\CustomArticle();

  return $collector;
});
```

This adds it to the list of indexers the module is aware of, and it will now automatically trigger this indexer during the scheduled tasks, manual triggers from the admin, or any update/save/edit hook on the particular post type it's assigned to.

### Additional Action hook Available
1. Trigger Specific actions before the cron runs

     ``` add_action('pixelkey_algolia:after_index_cron', 'your_callback_function'); ```

2. Trigger Specific actions after the cron runs


    ``` add_action('pixelkey_algolia:after_index_cron', 'you_callback_function'); ```

3. Trigger after post status transition or post is saved or updated

    ``` add_action('pixelkey_algolia:update_success', 'you_callback_function'); ```