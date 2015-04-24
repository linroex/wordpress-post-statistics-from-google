<?php
/**
 * Plugin Name: Post Statistics
 * Plugin URI: https://github.com/linroex/wordpress-post-statistics-from-google
 * Description: Import Google Analytics data in WordPress.
 * Version: 0.0.1
 * Author: linroex
 * Author URI: http://me.coder.tw
 * License: MIT
 */

session_start();

include(__DIR__ . '/vendor/autoload.php');
include(__DIR__ . '/Google_Analytics.php');
include(__DIR__ . '/HotPostsWidget.php');
include(__DIR__ . '/cron.php');

function install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 
    $charset = $wpdb->get_charset_collate();

    $sql = "
        CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            date DATE NOT NULL,
            count bigint(20) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}

function getData($start_date, $end_date) {
    $applicationName = 'newcongress';
    $account = '433729975312-ar2hp401av11nsgadevcqk8fhuodn8uh@developer.gserviceaccount.com';
    $key = __DIR__ . '/newcongress-tw-be61ca6250aa.p12';

    $analytics = new Google_Analytics($applicationName, $account, $key);

    $analytics->setAccountId('New Congress')->setWebpropertieId('New Congress')->setProfileId(1);

    $data = $analytics->getResults(
                $start_date, 
                $end_date, 
                'ga:pageviews', [
                    'dimensions'=>'ga:pagePath,ga:date', 
                    'sort'=>'-ga:pageviews'
                ]
            );

    return $data->getRows();
}

function installData() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 

    foreach (getData('2015-04-24', '2015-04-25') as $row) {
        if(url_to_postid($row[0]) == '0'){
            continue;
        }
        $data = [
            'post_id'=>url_to_postid($row[0]),
            'date'=>$row[1],
            'count'=>$row[2]
        ];
        $wpdb->insert($table_name, $data);
    }

}

function uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

register_activation_hook(__FILE__, 'install');
register_activation_hook(__FILE__, 'installData');
register_activation_hook(__FILE__, function(){
    wp_schedule_event(current_time('timestamp'), 'hourly', 'hourly_load_google_analytics');
});
register_deactivation_hook(__FILE__, 'uninstall');
register_deactivation_hook(__FILE__, function(){
    wp_clear_scheduled_hook('hourly_load_google_analytics');
});