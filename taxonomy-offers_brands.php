<?php

get_header();
$buttontext=get_option('keloo_view_offer');
$find_moretext=get_option('find_more');
$term = get_queried_object();
$term_id = $term->term_id;
//$kelkoo_attchment_id = get_term_meta( $term_id, 'kelkoo_category_image', true );
//$attachment_url = wp_get_attachment_image_src( $kelkoo_attchment_id, 'full' );
$kelkoo_category_id = get_term_meta( $term_id, 'kelkoo_brand_id', true );
if(isset($_GET['next'])){
	$page=$_GET['next'];
}else{
	$page=1;
}

$amazonproducts=getAmazonProductsbybrand($term->name,$page);
		
$amazonresponse=json_decode($amazonproducts);
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        
            <div class="alloffers_inner mobilehide">
                <div class="product-grid">
                    <div class="offer-grid__item offer-grid__item--box" style="width:100%;">
                       <div  class="offer-box">
                          
                            <h2  class="offer-box__title_single"><?php echo  $term->name; ?></h2>
                          
                              
                        </div>
                    </div>
                    
                    
                    
                </div>
            </div>  
       <?php 
       if($kelkoo_category_id || $amazonresponse){
       $response=kelkoo_get_all_brand_offers_by_id_by_limit($kelkoo_category_id);
		
        if($response['error']==0){
			$alloffers= json_decode($response['success']);
		//	echo "<pre>";
		//print_r($alloffers);
		//echo "</pre>";
			$currentPage=$alloffers->meta->offers->currentPage;
			$NextPage=$alloffers->meta->offers->nextPage;
		
			$allgetoffers=$alloffers->offers;
		}
        if($allgetoffers || $amazonresponse){
		
    	?>
	  <div class="container">
        <h2 style="text-align:center;padding: 5px 0px; font-size: 26px;">All Offers for <?php echo $term->name; ?></h2>
        <div class="alloffers_inner">
        <div class="product-grid">
            <?php
        
        
        if(!empty($amazonresponse->SearchResult->Items)){
        foreach($amazonresponse->SearchResult->Items as $amazonresponseItems){ 
         //   echo "<pre>";
         //   print_r($amazonresponseItems->Offers->Listings);
         //   echo "</pre>";
           
            $DetailPageURL=$amazonresponseItems->DetailPageURL; 
            $Images=$amazonresponseItems->Images->Primary->Large->URL;
            $brand=$amazonresponseItems->ItemInfo->ByLineInfo->Brand->DisplayValue;
            $features=$amazonresponseItems->ItemInfo->Features->DisplayValues;
           $ProductInfo=$amazonresponseItems->ItemInfo->Title->DisplayValue;
             $cuoffoffers=$amazonresponseItems->Offers->Listings['0']->Price->DisplayAmount;
            $discount=$amazonresponseItems->Offers->Listings['0']->Price->Savings->Percentage;
            $SavingBasis=$amazonresponseItems->Offers->Listings['0']->SavingBasis->DisplayAmount;
            $ean=$amazonresponseItems->ItemInfo->ExternalIds->EANs->DisplayValues['0'];
            $asin = $amazonresponseItems->ASIN;
            $category = [];
            $productdata=insert_custom_post_offers_amazon($ProductInfo,$Images,$features,$cuoffoffers,$discount,$SavingBasis,$DetailPageURL,$ean,$brand,$country,$category,$asin);
          //  print_r($productdata);
           //  die();
        ?>
        <div class="offer-grid__item offer-grid__item--box amzondata">
   <div  class="offer-box">
      <a href="<?php echo  $DetailPageURL; ?>" target="_blank" rel="sponsored" class="offer-box__container">
         <div class="offer-box__img-container">
            <!----> <!----> 
            <div class="offer-image offer-image--box"><img height="224" width="224" src="<?php echo  $Images; ?>" alt="<?php echo  $ProductInfo; ?>"  class="offer-image__img">
            <span class="badgeapp"><img src="/wp-content/uploads/2024/05/amazon.webp"/> amazon.it</span>
            <?php if($discount > 0){ ?>
            <span class="sale-badge"><?php echo $discount; ?> % off</span>
            <?php } ?>
            </div>
            <!---->
         </div>
         </a>
         <a href="<?php echo  $DetailPageURL; ?>" target="_blank" rel="sponsored" class="offer-box__container"> <h2  class="offer-box__title">
            <?php echo  $ProductInfo; ?>
          </h2>
          </a>
         
         <?php  if($productdata['cats'] !=''){ ?>
         <div class="offer-labels">
             <span class="category">Brands:<?php echo implode(',',$productdata['cats']); ?></span>
         </div>
         <?php  } ?>
         <div class="offer-price offer-box__price">
            <div class="offer-price__price-container">
               <span  class="offer-price__price"> <?php echo  $cuoffoffers; ?> 
               <?php if($discount > 0){ ?>
               <span class="price-fraction strikethrought"> <?php echo  $SavingBasis; ?></span>
               <?php } ?>
               </span> 
            </div>
            </div>
			 <?php if($amazonresponseItems->Offers->DeliveryInfo->IsFreeShippingEligible == 1 ){
			 	$delcost='Free Shipping';
		  ?>
            <div class="offer-price__delivery">Delivery: &nbsp; <span class="price-fraction"><?php echo  $delcost; ?></span></div>
         
         <?php } ?>
         
        <a href="<?php echo $DetailPageURL; ?>" target="_blank" rel="sponsored" class="offer-box__container"> <div class="visit-button offer-box__visit-button">
            <span class="visit-button__link visit-button__link-box">
               <?php echo $buttontext; ?>
              
            </span>
         </div></a>
      
      <?php
      if($productdata['postlink'] !=''){
      ?>
      <div class="view-offers-link">
         <a target="_blank" href="<?php echo $productdata['postlink']; ?>" >
          <?php echo $find_moretext; ?>
         </a>
      </div>
      <?php } ?>
   </div>

</div>
        
        <?php 
            
           
        } 
            
        }  
        
        ?>
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
			$brandid=$allgetoffers->brand->id;
			$brandname=$allgetoffers->brand->name;
			if($categoryname !=''){
			$allkcategories=explode(',',$categoryname);
			unset($allcatsarray);
			foreach($allkcategories as $key=>$val){
			    $catlink=get_or_create_category_url($val,'offers_category',$categoryid,$brandid);
			    if($catlink !=''){
			        $allcatsarray[]='<a href="'.$catlink.'">'.$val.'</a>';
			    }
			}
			}
			if($brandname !=''){
			$allkbrands=explode(',',$brandname);
			unset($allbrandsarray);
			foreach($allkbrands as $keyb=>$valb){
			    $blink=get_or_create_brand_url($valb,'offers_brands',$brandid);
			    if($blink !=''){
			        $allbrandsarray[]='<a href="'.$blink.'">'.$valb.'</a>';
			    }
			}
			}
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
                     </a>
                     <h2  class="offer-box__title">
                        <?php echo  $allgetoffers->title; ?>
                      </h2>
                      <?php  if($categoryname !=''){ ?>
                      <div class="offer-labels">
                         <?php if($allcatsarray){ ?>
                         <span class="category">Category:<?php echo implode(',',$allcatsarray); ?></span>
                         <?php } ?>
                     </div>
                     <?php } ?>
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
                     <a href="<?php echo  $gourl; ?>" target="_blank" rel="sponsored" class="offer-box__container"><div class="visit-button offer-box__visit-button">
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
                      $offersids=insert_custom_post_offers_cat($allgetoffers->title,$desc,$country,$codeean,$imgurl,$price,$gourl,$seemore,$currency,$merchantids,$offerids,$mainmerchatids);
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
				<?php if(($NextPage > 1) & ($NextPage < 42)){ ?>	
					<li><a href="<?php echo $posturls; ?>?next=<?php echo $NextPage; ?>" class="next">Next &raquo;</a></li>
				<?php } ?>	
				</ul>
			</div>
		
		</div>
		<?php } ?>
    </div>
	
	
	
	<?php } } ?>
    </main>
</div>       

<?php get_footer(); ?>