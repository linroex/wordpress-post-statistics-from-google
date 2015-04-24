<?php

function cronLoadData() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 

    foreach (getData(date('Y-m-d'), date('Y-m-d')) as $row) {
        $data = [
            'post_id'=>url_to_postid($row[0]),
            'date'=>$row[1],
            'count'=>$row[2]
        ];
        $wpdb->insert($table_name, $data);
    }
}

add_action('hourly_load_google_analytics', 'cronLoadData');