<?php

use PixelKey\Algolia\Commands\RunIndexers;

add_action( 'admin_menu', function() {
    add_options_page( 'Algolia Indexing', 'Algolia Indexing', 'manage_options', 'algolia-indexing', function() {
        function getIndexerDisplayName($indexerName) {
            $indexerParts = explode('\\', $indexerName);
            return array_pop($indexerParts);
        }

        echo "
            <h3>Algolia Indexing Control</h3>
            <div style='padding: 20px; background:#FFF; border: 1px solid #AAA; border-radius: 3px;'>
        ";

        if(isset($_POST['action']) && $_POST['action'] === 'run_all') {
            try {
                echo '<div style="padding:10px; background: #9ece7f; border: 1px solid #888; margin-bottom: 20px;"><b>Running All Indexers</b>... ✓</div>' . PHP_EOL;
                RunIndexers::run();

            } catch(\Exception $exception) {
                echo $exception->getMessage();
            }
        }

        if(isset($_POST['action']) && $_POST['action'] === 'run_index') {
            try {
                $indexerName = str_replace('\\\\', '\\', $_POST['index']);

                $indexerClasses = [];

                foreach(RunIndexers::getIndexers() as $indexer) {
                    $indexerClasses[] = (is_object($indexer)) ? get_class($indexer): $indexer;
                }

                if(!in_array($indexerName, $indexerClasses)) {
                    throw new \Exception('Class does not exist as Indexer.');
                }

                $indexer = new $indexerName();
                echo '<div style="padding:10px; background: #9ece7f; border: 1px solid #888; margin-bottom: 20px;">Running the <b>' . $indexer::DISPLAY_NAME . '</b> indexer... ✓</div>';

            } catch(\Exception $exception) {
                echo $exception->getMessage();
            }
        }

        echo "
            <form action='?page=algolia-indexing' method='post'>
                <input type='hidden' name='page' value='algolia-indexing' />
                <button class='button button-primary' name='action' value='run_all'>Run All Indexers</button><br /><br/>
                " . wp_nonce_field() . "
            </form>
        ";

        foreach(RunIndexers::getIndexers() as $indexer) {
            $instance = ($indexer instanceof \PixelKey\Algolia\Indexers\IndexerAbstract) ? $indexer : new $indexer();
            $indexerName = get_class($instance);

            echo "<form action='?page=algolia-indexing' method='post'>
                <input type='hidden' name='page' value='algolia-indexing' />
                <input type='hidden' name='index' value='$indexerName'>
                
                <button class='button button-primary' name='action' value='run_index'>Run " . $instance::DISPLAY_NAME . " Indexer</button><br/><br />
                " . wp_nonce_field() . "
            </form>";
        }

        echo "</div>";

    });
});
