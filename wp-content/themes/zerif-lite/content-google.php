<?php
/**
 * Created by PhpStorm.
 * User: MRX
 * Date: 17.08.15
 * Time: 18:52
 */

/*
Template Name: No Google Map
*/




get_header();?>


<div class="clear"></div>

</header> <!-- / END HOME SECTION  -->

<? $image_urll = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');?>
<div class="imegmegapost" style="background-image: url(<? echo $image_urll[0];?>); height: <? echo $image_urll[2];?>px;">
    <div class="header-content-wrap"><h1 class="entry-title-true"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1></div>
</div>

<div id="content" class="site-content">

    <div class="container">
        <?php the_widget('WP_Widget_Our_focus', ''); ?>



        <div class="content-left-wrap col-md-12">

            <div id="primary" class="content-area">

                <main id="main" class="site-main" role="main">



                    <?php while ( have_posts() ) : the_post(); ?>



                        <?php get_template_part( 'content', 'page' ); ?>



                        <?php

                        // If comments are open or we have at least one comment, load up the comment template

                        if ( comments_open() || '0' != get_comments_number() ) :

                            comments_template();

                        endif;

                        ?>



                    <?php endwhile; // end of the loop. ?>



                </main><!-- #main -->
                <?php include "sections/images_our_focus.php"?>
            </div><!-- #primary -->

        </div><!-- .content-left-wrap -->

    </div><!-- .container -->
  <!--  <div class="beforethtbestfooteronthesite"><?//php echo do_shortcode("[mapsmarker marker='1']"); ?></div>--!>
    <?php get_footer(); ?>
