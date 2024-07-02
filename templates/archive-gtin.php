<?php get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
<h1>All Offers</h1>
<div class="alloffers_inner">
    <div class="product-grid">
<?php 
$the_query = new WP_Query( 
    array('posts_per_page'=>30,
         'post_type'=>'gtin',
         'paged' => get_query_var('paged') ? get_query_var('paged') : 1) 
    );
while ($the_query -> have_posts()) : $the_query -> the_post(); 
echo $postids=get_the_ID();
$imgurl=get_post_meta($postids,'cusotm_img_url',true);
$price=get_post_meta($postids,'price',true);
$gourl=get_post_meta($postids,'custom_go_url',true);
?>
 <div class="offer-grid__item offer-grid__item--box">
   <div  class="offer-box">
      <a href="<?php echo  $gourl; ?>" target="_blank" rel="sponsored" class="offer-box__container">
         <div class="offer-box__img-container">
            <!----> <!----> 
            <div class="offer-image offer-image--box"><img height="224" width="224" src="<?php echo  $imgurl; ?>"  class="offer-image__img">
           
            </div>
            <!---->
         </div>
         <div class="offer-labels">
            <!----> <!---->
         </div>
         <h2  class="offer-box__title">
            <?php echo  get_the_title(); ?>
          </h2>
         
        
        
      </a>
      
   </div>
   <!---->
</div>

<?php
endwhile;
$big = 999999999; // need an unlikely integer
 echo paginate_links( array(
    'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $the_query->max_num_pages
) );

wp_reset_postdata();
?>
</div>
</div>
</main>
</div>

<?php get_footer(); ?>