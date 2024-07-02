<?php get_header(); ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
        <h1>All Merchants</h1>
        <div class="alloffers_inner">
            <div class="product-grid">
            <?php 
            $the_query = new WP_Query( 
            array('posts_per_page'=>20,
                 'post_type'=>'merchants',
                 'paged' => get_query_var('paged') ? get_query_var('paged') : 1) 
            );
            while ($the_query -> have_posts()) : $the_query -> the_post(); 
            $postids=get_the_ID();
            $logourl=get_post_meta($postids,'logourl',true);
            $merchaturl=get_post_meta($postids,'merchaturl',true);
            $merchatsummary=get_post_meta($postids,'merchatsummary',true);
            ?>
            <div class="offer-grid__item offer-grid__item--box">
               <div  class="offer-box">
                  <a href="<?php echo get_the_permalink(); ?>" target="_blank" rel="sponsored" class="offer-box__container">
                     <div class="offer-box__img-container">
                        <div class="offer-image offer-image--box">
                            <?php if($logourl !=''){ ?>
                            <img height="224" width="224" src="<?php echo  $logourl; ?>"  class="offer-image__img">
                            <?php }else{ ?>
                            <?php echo  get_the_title(); ?>
                            <?php } ?>
                        </div>
                    </div>
                    <h2  class="offer-box__title"><?php echo  get_the_title(); ?></h2>
                    </a>
                </div>
            </div>

            <?php endwhile; ?>
            </div>
        </div>
        <div class="paginate-archive">
        <?php
        $big = 999999999; 
         echo paginate_links( array(
            'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
            'format' => '?paged=%#%',
            'current' => max( 1, get_query_var('paged') ),
            'total' => $the_query->max_num_pages
        ) );
        ?>
        </div>
        <?php
        wp_reset_postdata();
        ?>

        </main>
    </div>

<?php get_footer(); ?>