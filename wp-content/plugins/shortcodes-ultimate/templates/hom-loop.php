<div class="new-posts new-posts-default-loop">
    <?php
    // Posts are found
    if ( $posts->have_posts() ) {
        while ( $posts->have_posts() ) :
            $posts->the_post();
            global $post;
            ?>
            <? $image_urll = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');?>
            <div id="su-post-<?php the_ID(); ?>" class="new-post" style="background-image: url(<? echo $image_urll[0];?>); height: <? echo $image_urll[2];?>px;">
                <?php if ( has_post_thumbnail() ) : ?>



                    <div class="hidhomnews" >
                        <h2 class="new-post-title"><?php the_title(); ?></h2>
                        <div class="new-post-meta"><?php _e( 'Posted', 'su' ); ?>: <?php the_time( get_option( 'date_format' ) ); ?></div>
                        <div class="new-post-excerpt">
                            <?php the_excerpt(); ?>
                        </div><a href="<?php the_permalink(); ?>" class="new-buut">Дитальныше</a>
                    </div>

                <?php endif; ?>




            </div>

        <?php
        endwhile;
    }
    // Posts not found
    else {
        echo '<h4>' . __( 'Posts not found', 'su' ) . '</h4>';
    }
    ?><hr>
</div>