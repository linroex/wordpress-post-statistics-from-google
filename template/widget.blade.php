<div class="feature-post-widget">
    
    <h1>熱門文章</h1>
    @foreach($posts as $post)
        <?php
            $data = get_post($post[0]);
        ?>
        <div class="feature-post">
            <a href="{{$data->guid}}">
                <h2 class="post-title">
                    {{$data->post_title}}
                </h2>
            </a>
            <span class="post-date">
                {{date('Y/m/d', strtotime($data->post_date))}}
            </span>
            <span class="post-author">
                {{the_author_meta('display_name', $data->post_author)}}
            </span>
        </div>
    @endforeach
</div>
<div class="clearfix" style="clear:both;"></div>