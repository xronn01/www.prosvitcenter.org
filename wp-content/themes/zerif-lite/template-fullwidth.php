<?php

/*
Template Name: Full Width Page
*/



get_header();

?>
<?php $post_id = get_the_ID();  //Dlya vidobragenya Slayda na golovniy storintsi
      if($post_id === 24) wowslider(12); ?>

<div class="clear"></div>

</header> <!-- / END HOME SECTION  -->

    <div class="beforethtbestfooteronthesite"><?php $post_id = get_the_ID();
      if($post_id === 39) echo do_shortcode("[mapsmarker marker='2']"); ?></div>

	<div id="content" class="site-content">

<div class="container">



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

	</div><!-- #primary -->

</div><!-- .content-left-wrap -->




</div><!-- .container -->
<div class="beforethtbestfooteronthesite"><?php if($post_id === 24) echo do_shortcode("[mapsmarker marker='1']"); ?></div>
<?php get_footer(); ?>