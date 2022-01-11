# pixelkey-algolia

This module provides helper classes to allow you to integreate custom Algolia indexing into your Wordpress applications. It contains some useful hooks, scheduled indexing cron tasks, and an admin interface to see and trigger your custom indexers. Since this is designed to be used to create a custom indexing experience, it's expected you have a fairly good understanding of how Algolia indexing works in general. This module will not index your posts for you, and no default indexing is included.

### Custom Indexer
To create a custom indexer, you will need to create a new class that extends from the IndexerAbstract class. Lets create an indexer for a custom post type for Articles: `article`.

```
use PixelKey\Algolia\Indexers\IndexerAbstract;

class CustomArticle extends IndexerAbstract {
    const DISPLAY_NAME = 'Article'; //This is what the indexer is called in the admin area
    const REMOTE_NAME = 'article_index'; //This is the literal name of the target index inside the algolia dashboard
    const POST_TYPE = 'article'; //This is the custom post type we want to index

    public static function index($ids = []){
     /* This function is called by the cron, the admin area, and by post update/edit/save hooks.
      * This is where you would implementyour custom indexing logic. $ids is a list of post ids passed
      * in by the hooks. For a full re-index triggered by the cron or admin, no $ids will be present */
      $index = static::_getIndexObject();
      $articles = static::_getArticles(); //Needs to be implemented for your specific use case.
      $indexSuccesful = $index->saveObjects($articles); //You might consider batching these
      
      return $indexSuccesful;
    }

    public static function remove($ids = []){
     /* This is called by the post update/edit/save hooks, $ids will contain all post IDs that should be
      * removed from the index. */
      if(count($ids) < 1) return false;

      $index = static::_getIndexObject();

      foreach($ids as $id) {
          $index->deleteObject($id);
      }

      return true;

    }
    
    /** We need access to the index in order to perform operations on that. This module includes the 
     * Algolia PHP client as a dependency, so we can use that to get access to the index using your
     * Algolia application credentials */
    protected static function _getIndexObject() {
        $client = \Algolia\AlgoliaSearch\SearchClient::create(
        //Your credentials here
            'yourAppId',
            'yourAppKey'
        );

        return $client->initIndex(static::REMOTE_NAME);
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
