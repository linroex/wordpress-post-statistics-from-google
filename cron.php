<?php

function cronLoadData() {
    global $wpdb;

    $table_name = $wpdb->prefix . "statistics"; 

    foreach (getData(date('Y-m-d'), date('Y-m-d')) as $row) {
        $post_id = url_to_postid($row[0]);
        $date = $row[1];

        if($post_id == '0') {
            continue;
        }

        $data = [
            'post_id'=>$post_id,
            'date'=>$date,
            'count'=>$row[2]
        ];

        // 避免重複插入資料
        $exists_count = $wpdb->get_var("select count(id) from $table_name where post_id = $post_id and date = '$date'");
        if($exists_count > 0) {
            $wpdb->update($table_name, $data, ['date'=>$date, 'post_id'=>$post_id]);
        }else {
            $wpdb->insert($table_name, $data);
        }


        
    }
}

add_action('hourly_load_google_analytics', 'cronLoadData');