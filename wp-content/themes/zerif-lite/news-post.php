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
<div class="buttons"><form method="get" name="fnews" action="">
<button type="submit" name="666" value="9" ></button>
<button type="submit" name="666" value="6" ></button></form>
</div>
<?php the_widget('WP_Widget_Our_focus', '');

$page = (get_query_var('paged')) ? get_query_var('paged') : 1;

query_posts('cat='.$_GET['666'].'&paged='.$page.'&posts_per_page=4');?>

<?php echo $_GET['666'];
while( have_posts() ) : the_post();
    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <?php if ( ! is_search() ) : ?>

        <?php if ( has_post_thumbnail()) : ?>

        <div class="post-img-wrap">

            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >

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

                                the_excerpt()

                                //the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'zerif-lite' ) );

                                ?>

                                <?php

                                wp_link_pages( array(

                                    'before' => '<div class="page-links">' . __( 'Pages:', 'zerif-lite' ),

                                    'after'  => '</div>',

                                ) );

                                ?>


                                <?php endif; ?>



                                <footer class="entry-footer">

                                    <?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>

                                        <?php

                                        /* translators: used between list items, there is a space after the comma */

                                        $categories_list = get_the_category_list( __( ', ', 'zerif-lite' ) );

                                        if ( $categories_list && zerif_categorized_blog() ) :

                                            ?>

                                            <span class="cat-links">

				<?php printf( __( 'Posted in %1$s', 'zerif-lite' ), $categories_list ); ?>

			</span>

                                        <?php endif; // End if categories ?>



                                        <?php

                                        /* translators: used between list items, there is a space after the comma */

                                        $tags_list = get_the_tag_list( '', __( ', ', 'zerif-lite' ) );

                                        if ( $tags_list ) :

                                            ?>

                                            <span class="tags-links">

				<?php printf( __( 'Tagged %1$s', 'zerif-lite' ), $tags_list ); ?>

			</span>

                                        <?php endif; // End if $tags_list ?>

                                    <?php endif; // End if 'post' == get_post_type() ?>



                                    <?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>

                                        <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'zerif-lite' ), __( '1 Comment', 'zerif-lite' ), __( '% Comments', 'zerif-lite' ) ); ?></span>

                                    <?php endif; ?>



                                    <?php edit_post_link( __( 'Edit', 'zerif-lite' ), '<span class="edit-link">', '</span>' ); ?>

                                </footer><!-- .entry-footer -->


                            </div><!-- .entry-content --><!-- .entry-summary -->

                        </div><!-- .list-post-top -->


                    </div><!-- .listpost-content-wrap -->

    </article><!-- #post-## -->

<?php
endwhile;?>
<?php if (function_exists('wp_corenavi')) wp_corenavi(); ?>

</div><!— .container —>
<!— <div class="beforethtbestfooteronthesite"><?//php echo do_shortcode("[mapsmarker marker='1']"); ?></div>
<?php get_footer(); ?>