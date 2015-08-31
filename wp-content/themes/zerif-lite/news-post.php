<?php
/**
 * Created by PhpStorm.
 * User: MRX
 * Date: 17.08.15
 * Time: 18:52
 */

/*
Template Name: nevs-content

*/

get_header(); ?>

<div class="clear"></div>

</header> <!— / END HOME SECTION —>

<div id="content" class="site-content">

<div class="container">
<div class="buttons">
<form class="newsform" method="get" name="fnews" action="">
<button type="submit" name="new" value="9,13" >Усі</button>
<button type="submit" name="new" value="9" >Статті</button>
<button type="submit" name="new" value="13" >Новини</button>
</form>
</div>
<div style="clear: both; width: 100%" ></div>
<?php the_widget('WP_Widget_Our_focus', '');

$page = (get_query_var('paged')) ? get_query_var('paged') : 1;

query_posts('cat='.$_GET['new'].'&paged='.$page.'&posts_per_page=4');?>
<div class = "forqveri" name="<?echo $_GET['new'];?>"></div>
<h2 style="float: left; padding: 0">
<?php if($_GET['new'] ||$_GET['new']=='9,13'){
echo get_cat_name($_GET['new']);
}else{echo 'Усі';}
while( have_posts() ) : the_post();
    ?>
</h2>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <?php if ( ! is_search() ) : ?>

        <?php if ( has_post_thumbnail()) : ?>

        <div class="post-img-wrap">



                <?php the_post_thumbnail("post-thumbnail"); ?>

            </a>

        </div>

        <div class="listpost-content-wrap">

            <?php else: ?>

            <div class="listpost-content-wrap-full">

                <?php endif; ?>

                <?php else:  ?>

                <div class="listpost-content-wrap-full">

                    <?php endif; ?>

                    <div class="list-post-top">

                        <header class="entry-header">

                            <h3 class="new-post-title"><?php the_title(); ?></h3>



                            <?php if ( 'post' == get_post_type() ) : ?>

                                <div class="entry-meta">

                                    <?php zerif_posted_on(); ?>

                                </div><!-- .entry-meta -->

                            <?php endif; ?>

                        </header><!-- .entry-header -->



                        <?php if ( is_search() ) : // Only display Excerpts for Search ?>

                        <div class="entry-summary">

                            <?php the_excerpt(); ?>



                            <?php else : ?>

                            <div class="entry-content">

                                <?php
                                if(in_category(9)){
                                the_excerpt();
                                    $posturl = get_permalink();
                                    ?>
                                    <div class="forbooot">
                                <a class="buuut" href="<? echo $posturl;?>">Дізнтись більше
                                    <img class="hrimg" src="http://www.prosvitcenter.org/wp-content/uploads/2015/08/strilka.fw_.png" alt="" />
                                </a></div>
                                <?}
                                if(in_category(13)){
                                    $posturl = null;
                                    the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'zerif-lite' ) );

                                    ?><div class="forbooot  onvidh" >
                                <a class="buuut"    >Дізнтись більше
                                    <img class="hrimg" src="http://www.prosvitcenter.org/wp-content/uploads/2015/08/strilka.fw_.png" alt="" />
                                </a></div>
                               <? }

                                ?>



                               <?php endif; ?>






                            </div><!-- .entry-content --><!-- .entry-summary -->

                        </div><!-- .list-post-top -->


                    </div><!-- .listpost-content-wrap -->

    </article><!-- #post-## -->

<?php
endwhile;?>
<div style="clear: both; width: 100%" ></div>
<?php if (function_exists('wp_corenavi')) wp_corenavi(); ?>

</div><!— .container —>
<!— <div class="beforethtbestfooteronthesite"><?//php echo do_shortcode("[mapsmarker marker='1']"); ?></div>
<?php get_footer(); ?>