<?php
use Windwalker\Renderer\BladeRenderer;

class HotPostsWidget extends WP_Widget {
    private $blade;
    function __construct() {
        parent::__construct( 'hot_posts_widget', '熱門文章1',
            ['description'=>'這是一個會顯示熱門文章的小工具，資料來源是 Google Analytics']
        );

        $this->blade = new BladeRenderer(__DIR__ . '/template/', ['cache_path'=>__DIR__ . '/cache']);
    }

    public function widget($args, $instance) {
        global $wpdb;
        $table_name = $wpdb->prefix . "statistics";

        $data = $wpdb->get_results("SELECT `post_id` ,sum(`count`) FROM `$table_name` where `date` > NOW() - INTERVAL 2 week group by `post_id` order by sum(`count`) desc limit 0,30", ARRAY_N);
        
        echo $this->blade->render('widget', ['posts'=>$data]);

    }

    public function form($instance) {
        
    }

    public function update($new, $old) {

    }
}

add_action('widgets_init', function() {
    register_widget('HotPostsWidget');
});