<?php

add_shortcode('categori','categori_news');
function categori_news(){
query_posts('cat=9&showposts=10&posts_per_page=3');

while (have_posts()) : the_post();
/*Dima insert*/the_excerpt(); ?>
<div class="content-box">
    <div class="bgr01"><div class="bgr02"><div class="bgr03">
                <div <?php post_class() ?> id="post-<?php the_ID(); ?>" style=" float:none; ">
                    <div class="title">
                        <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                        <div class="date_all">
                            <?php the_time('l, j ?F, Y') ?>
                        </div>
                        <div class="post">
                            Написал <?php the_author_link() ?> <?php the_time('g:i A') ?>
                        </div>
                    </div>
                    <div class="content_box">
                        <?php the_content('Читать всё'); ?>
                    </div>

                    <div class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?></div>

                    <div class="comments"><?php comments_popup_link('0 комментарии', 'комментарии', '% комментарии '); ?></div>
                    <div class="link-edit"><?php edit_post_link('Edit', ''); ?></div>
                </div>
            </div></div></div>
</div>
<?php endwhile;}
function wp_corenavi() {
    global $wp_query;
    $pages = '';
    $max = $wp_query->max_num_pages;
    if (!$current = get_query_var('paged')) $current = 1;
    $a['base'] = str_replace(999999999, '%#%', get_pagenum_link(999999999));
    $a['total'] = $max;
    $a['current'] = $current;

    $total = 1; //1 - выводить текст "Страница N из N", 0 - не выводить
    $a['mid_size'] = 3; //сколько ссылок показывать слева и справа от текущей
    $a['end_size'] = 1; //сколько ссылок показывать в начале и в конце
    $a['prev_text'] = '&laquo;'; //текст ссылки "Предыдущая страница"
    $a['next_text'] = '&raquo;'; //текст ссылки "Следующая страница"

    if ($max > 1) echo '<div class="navigation">';
    if ($total == 1 && $max > 1) $pages = '<span class="pages">Страница ' . $current . ' из ' . $max . '</span>'."\r\n";
    echo $pages . paginate_links($a);
    if ($max > 1) echo '</div>';
}
function set_clas(){
    if($_GET['new']){

    }
}
?>