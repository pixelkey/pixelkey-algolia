<?php

namespace PixelKey\Algolia;

use PixelKey\Algolia\Commands\RunIndexers;

class Setup {

    const PLUGIN_NAME = 'Pixel Key Algolia';

    public static function init() {
        self::initializeCronJob();
    }

    public static function initializeCronJob() {
        add_action(
            'pixelkey_algolia/run_indexers',
            array(RunIndexers::class, 'runAsCron'),
            100
        );

        $eventName = 'pixelkey_algolia/run_indexers';
        $interval = 'twicedaily';

        $currentEvent = wp_get_scheduled_event($eventName);

        if(!$currentEvent) {
            wp_schedule_event(time(), $interval, $eventName);
        }
    }
}

function pixelkey_runindexers_cron() {
    if(is_plugin_active(Setup::PLUGIN_NAME)) {
        RunIndexers::run();
    }
};
