# Pixelkey-algolia v1.0.4

This wordpress plugin enables the integration of custom Algolia indexing within your applications. It includes several useful hooks, scheduled indexing cron tasks, and an administrative interface to monitor and initiate your custom indexers. As this module is intended to facilitate the creation of a tailored indexing experience, it is expected that you possess a solid understanding of Algolia indexing principles. Please note that this module does not perform post indexing on your behalf, and no default indexing configurations are provided. Thus, you will need to implement the following filters in your theme:

### Initialize Algolia ``` App_ID ``` and ``` API_Key ```

```
add_filter('initialize_algolia_key', function () {
    $algolia_key = [
        'appId' => "xxxxxx",
        'apiKey' => "xxxxxxxxxxxxxxxxxxxxxxxxx",
    ];
    return $algolia_key;
}, 10, 1);

```
### Initialize `Algolia index_name`

```
add_filter('algolia_index_name', function ($post_type = 'post') {
    return 'post_index_name';
}, 10, 1);

```
This adds it to the list of indexers the module is aware of, and it will now automatically trigger this indexer during the scheduled tasks, manual triggers from the admin, or any update/save/edit hook on the particular post type it's assigned to.

### Filter query Arguments `[Optional]`

```
// Example: Filter the post query arguments to remove posts with 'teaser' category slug.
add_filter('post_query_args', function ($args) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => 'teaser',
            'operator' => 'NOT IN'
        ]
    ];
    return $args;
});

```

### Serialize the `post object` to an `array` before sending it to Algolia.

```
/**
 * This filter is used by the Pixelkey-Algolia plugin.
 * @param \WP_Post $post The post object to serialize.
 * @return array The serialized post object.
 */
function algolia_post_to_record(WP_Post $post) {
    $tags = array_map(function (WP_Term $term) {
        return $term->name;
    }, wp_get_post_terms($post->ID, 'post_tag'));

    return [
        'objectID' => implode('#', [$post->post_type, $post->ID]),
        'title' => $post->post_title,
        'author' => [
            'id' => $post->post_author,
            'name' => get_user_by('ID', $post->post_author)->display_name,
        ],
        'excerpt' => $post->post_excerpt,
        'content' => strip_tags($post->post_content),
        'tags' => $tags,
        'url' => get_post_permalink($post->ID),
        'custom_field' => get_post_meta($post->id, 'custom_field_name'),
    ];
}
add_filter('post_to_record', 'algolia_post_to_record');

```

### Additional Action hook Available
1. Trigger Specific actions before the cron runs

     ``` add_action('pixelkey_algolia:after_index_cron', 'your_callback_function'); ```

2. Trigger Specific actions after the cron runs


    ``` add_action('pixelkey_algolia:after_index_cron', 'your_callback_function'); ```

3. Trigger after post status transition or post is saved or updated

    ``` add_action('pixelkey_algolia:update_success', 'your_callback_function'); ```