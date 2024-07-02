<?php
get_header(); 
$postids=get_the_ID();
$posturls=get_the_permalink($postids);
$logourl=get_post_meta($postids,'logourl',true);
$merchantids=get_post_meta($postids,'merchantids',true);
$merchaturl=get_post_meta($postids,'merchaturl',true);
$merchatsummary=get_post_meta($postids,'merchatsummary',true);
$buttontext=get_option('keloo_view_offer');
$find_moretext=get_option('find_more');
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php while (have_posts()) : the_post(); ?>

            <h1>Merchant: <?php echo get_the_title(); ?></h1>
            <div class="alloffers_inner">
                <div class="product-grid">
                    <div class="offer-grid__item offer-grid__item--box" style="width:100%;">
                       <div  class="offer-box">
                          <a href="<?php echo  $merchaturl; ?>" target="_blank" rel="sponsored" class="offer-box__container">
                             <div class="offer-box__img-container">
                                <div class="offer-image offer-image--box">
                                    <?php if($logourl !=''){ ?>
                                    <img height="224" width="224" src="<?php echo  $logourl; ?>"  class="offer-image__img">
                                    <?php }else{ ?>
                                    <?php echo  get_the_title(); ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <h2  class="offer-box__title_single"><?php echo  get_the_title(); ?></h2>
                            </a>
                            
                             <div class="entry-content">
                                <?php if($merchatsummary !=''){
                                echo $merchatsummary;
                                }
                                ?>
                            </div>
                              
                        </div>
                    </div>
                    
                    
                    
                </div>
            </div>    
        <?php endwhile; 
        $response=kelkoo_get_all_merchats_offers_by_id_by_limit($merchantids);
		//echo "<pre>";
		//print_r($response);
		//echo "</pre>";
        if($response['error']==0){
			$alloffers= json_decode($response['success']);
			$currentPage=$alloffers->meta->offers->currentPage;
			$NextPage=$alloffers->meta->offers->nextPage;
		
			$allgetoffers=$alloffers->offers;
		}
        if($allgetoffers){
		
    	?>
	  <div class="container">
        <h2 style="text-align:center;padding: 20px 0px;
    font-size: 26px;">All Offers for <?php echo get_the_title(); ?></h2>
        <div class="alloffers_inner">
        <div class="product-grid">
         <?php foreach($allgetoffers as $allgetoffers){ 
            $offerids=$allgetoffers->offerId; 
            $mainmerchatids=$allgetoffers->merchant->id;
			$merchantids=$allgetoffers->merchant->name;
			$codeean=$allgetoffers->code->ean;
			$imgurl=$allgetoffers->images['0']->url;
			$price=$allgetoffers->price;
			$gourl=$allgetoffers->goUrl;
			$categoryname=$allgetoffers->category->name;
			$categoryid=$allgetoffers->category->id;
		?>
            <div class="offer-grid__item offer-grid__item--box" id="<?php echo $offerids; ?>">
               <div  class="offer-box">
                  <a href="<?php echo  $gourl; ?>" target="_blank" rel="sponsored" class="offer-box__container">
                     <div class="offer-box__img-container">
                        <!----> <!----> 
                        <div class="offer-image offer-image--box"><img height="224" width="224" src="<?php echo  $imgurl; ?>" alt="<?php echo  $allgetoffers->title; ?>"  class="offer-image__img">
                        <?php if($allgetoffers->rebatePercentage > 0){ ?>
                        <span class="sale-badge"><?php echo $allgetoffers->rebatePercentage ?> % off</span>
                        <?php } ?>
                        </div>
                        <!---->
                     </div>
                     <div class="offer-labels">
                        <!----> <!---->
                     </div>
                     <h2  class="offer-box__title">
                        <?php echo  $allgetoffers->title; ?>
                      </h2>
                     <div class="offer-price offer-box__price">
                        <div class="offer-price__price-container">
                           <span  class="offer-price__price"><?php echo  $allgetoffers->currency; ?> <?php echo  $price; ?> 
                           <?php if($allgetoffers->rebatePercentage > 0){ ?>
                           <span class="price-fraction strikethrought"><?php echo  $allgetoffers->currency; ?> <?php echo  $allgetoffers->priceWithoutRebate; ?></span>
                           <?php } ?>
                           </span> 
                        </div>
            			 <?php if($allgetoffers->deliveryCost > 0 ){
            			 	$delcost=$allgetoffers->deliveryCost;
            		 }else{
            			 $delcost=0;
            		 } ?>
                        <div class="offer-price__delivery">Delivery: &nbsp;<?php echo  $allgetoffers->currency; ?> <span class="price-fraction"><?php echo  $delcost; ?></span></div>
                     </div>
                     <div  class="offer-box__merchant-name"><?php echo $merchantids; ?></div>
                     <div class="visit-button offer-box__visit-button">
                        <span class="visit-button__link visit-button__link-box">
                           <?php echo $buttontext; ?>
                          
                        </span>
                     </div>
                  </a>
                  <?php
                  $desc=$allgetoffers->description;
                  $seemore=$buttontext;
                  $currency=$allgetoffers->currency;
                  $country=get_option('kelkoo_defualt_country');
                  //echo $codeean;
                  if($codeean !=''){
                      $offersids=insert_custom_post_offers($allgetoffers->title,$desc,$country,$codeean,$imgurl,$price,$gourl,$seemore,$currency,$merchantids,$offerids,$mainmerchatids,$categoryname,$categoryid);
                      if($offersids !='Ean Already Exist'){
                  ?>
                  <div class="view-offers-link">
                     <a target="_blank" href="<?php echo $offersids; ?>" >
                      <?php echo $find_moretext; ?>
                     </a>
                  </div>
                  <?php } } ?>
               </div>
  
</div>
		 <?php 
	  
		 } ?>
        </div>
		</div>
		<?php if($currentPage){ ?>
		<div class="pagination">
		
			<div class="content">
				<ul class="pagination">
				<?php if($currentPage > 1){ ?>
					<li><a href="<?php echo $posturls; ?>?next=<?php echo $currentPage-1; ?>" class="prev">&laquo; Previous</a></li>
				<?php } ?> 
					<li><a href="#" class="current"><?php echo $currentPage; ?></a></li>
				<?php if($NextPage < 42){ ?>	
					<li><a href="<?php echo $posturls; ?>?next=<?php echo $NextPage; ?>" class="next">Next &raquo;</a></li>
				<?php } ?>	
				</ul>
			</div>
		
		</div>
		<?php } ?>
    </div>
	
	
	
	<?php } ?>
        

    </main>
</div>

<?php
get_footer();

?>