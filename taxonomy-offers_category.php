<?php
get_header();

if(isset($_GET['brand']))
{ 
    
    $allbrands = $_GET['brand'];
    $allbrands =str_replace('ts','&',$allbrands);
    $allbrands=str_replace('_',' ',$allbrands);
    $allbrandslist=explode(',',$allbrands);
    foreach($allbrandslist as $bkey=>$bval){
       // echo $bval;
        $bterm = get_term_by('name', $bval, 'offers_brands');
        if ($bterm) {
            $kelkoobrandids = get_term_meta($bterm->term_id, 'kelkoo_brand_id', true);
            $allbrandsarray[]=$kelkoobrandids;
        }
        
    }
}else{
    $allbrandsarray=array();
}


$buttontext=get_option('keloo_view_offer');
$find_moretext=get_option('find_more');
$term = get_queried_object();
$term_id = $term->term_id;
$kelkoo_attchment_id = get_term_meta( $term_id, 'kelkoo_category_image', true );
    if($kelkoo_attchment_id){
            $attachment_url = wp_get_attachment_image_src( $kelkoo_attchment_id, 'full' );
            $attachment_url = $attachment_url[0];
    }else{
            $attachment_url = get_term_meta( $term->term_id, 'kelkoo_feature_image', true );
    }
         
$kelkoo_category_id = get_term_meta( $term_id, 'kelkoo_category_id', true );
$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
$taxonomy ='offers_category';
$posts_per_page = 12;
$terms = get_terms( array(
    'taxonomy' => $taxonomy,
    'hide_empty' => false,
    'number' => $posts_per_page,
    'offset' => ( $paged - 1 ) * $posts_per_page,
) );
$total_terms = wp_count_terms( $taxonomy, array( 'hide_empty' => false ) );
$total_pages = ceil( $total_terms / $posts_per_page );
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        
            <div class="alloffers_inner mobilehide">
                <div class="product-grid">
                    <div class="offer-grid__item offer-grid__item--box" style="width:100%;">
                       <div  class="offer-box">
                          
                             <div class="offer-box__img-container">
                                <div class="offer-image offer-image--box">
                                    <?php if($attachment_url){ ?>
                                    <img height="224" width="224" src="<?php echo  $attachment_url; ?>"  class="offer-image__img">
                                    <?php }else{ ?>
                                    <?php echo  $term->name; ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <h2  class="offer-box__title_single"><?php echo  $term->name; ?></h2>
                        </div>
                    </div>
                </div>
            </div>  
         <?php 
          
            if(!empty($allbrandsarray)){  
                 if($allbrandsarray['0'] !=''){
                 $response=kelkoo_get_all_category_offers_by_brand_id($allbrandsarray,$kelkoo_category_id);
                }
            }else{
             $response=kelkoo_get_all_category_offers_by_id_by_limit($kelkoo_category_id);
            }
         
        if(isset($_GET['next'])){
        	$page=$_GET['next'];
        }else{
        	$page=1;
        } 
        
        if(isset($_GET['brand'])){
            $brand = $_GET['brand'];
        }else{
            $brand = '';
        }

        $amazonproducts_by_category=getAmazonProductsbycategory($term->name,$page,$brand);
        $amazonresponse=json_decode($amazonproducts_by_category);

        if($response['error']==0){
			$alloffers= json_decode($response['success']);
			
			if($alloffers->error == ''){
			$currentPage=$alloffers->meta->offers->currentPage;
			$NextPage=$alloffers->meta->offers->nextPage;
		
			$allgetoffers=$alloffers->offers;
			}else{
			    $allgetoffers = [];
			}
		}
// 		print_r($allgetoffers);
// 		die();
        if($allgetoffers || $amazonresponse ){
		
    	?>
	  <div class="container">
        <h2 style="text-align:center;padding: 5px 0px;
    font-size: 26px;">All Offers for <?php echo $term->name; ?></h2>
        <div class="alloffers_inner customgridtax">
        <div class="brandsfilter">
        <?php  

        $kelkoo_category_brands = get_term_meta( $term_id, 'all_linked_brands' );
        $amazone_category_brands = get_term_meta( $term_id, 'all_linked_brands_amazone' );
                
                // echo '<pre>';
                // print_r($kelkoo_category_brands);
                // print_r($amazone_category_brands);
                // die();

        if(!empty($kelkoo_category_brands)){
        $termbs=json_decode($kelkoo_category_brands['0']);
        $termbs= array_unique($termbs);
        /*$termbs = get_terms(array(
                'taxonomy' => 'offers_brands',
                'hide_empty' => false,
            ));
            */
        ?>
        <div class="brandslist">
            <h2>Choose Brands</h2>
            <?php if($allbrandsarray['0'] !=''){ ?>
            <a style="color:red;" href="<?php echo get_term_link($term_id);  ?>"><i class="fa fa-cross"></i> Clear Filter</a>
            <?php } ?>
            <ul class="bmainlist">
                <?php  foreach ($termbs as $tkey=>$tval) :  
                        
                        $termname=get_term_name_by_meta_val($tval);
                        $mainternmna=$termname;
                        $term_idb=   $termname->term_id;
                        $kelkoo_brand_id = $tval;
                        if($termname !=''){
                            $termname=str_replace('&','ts',$termname);
                ?>
                <li><div class="form-group"><label><input type="checkbox"name="selectbrands[]" class="selectbrands"  value="<?php echo esc_attr(str_replace(' ','_',$termname)); ?>" <?php if(in_array($tval,$allbrandsarray)){ echo "checked";} ?>><?php echo $mainternmna; ?></label></div></li>
                <?php } endforeach; ?>
            </ul>
            
        </div>
        <?php  } else{  
        
         if($amazone_category_brands){
            $termbs=json_decode($amazone_category_brands['0']);
            $termbs= array_unique($termbs);
            
        ?>
         <div class="brandslist">
            <h2>Choose Brands</h2>
            <?php if($allbrandsarray['0'] !=''){ ?>
            <a style="color:red;" href="<?php echo get_term_link($term_id);  ?>"><i class="fa fa-cross"></i> Clear Filter</a>
            <?php } ?>
            <ul class="bmainlist">
                <?php  foreach ($termbs as $tkey=>$tval) :  
                        
                        // $termname=get_term_name_by_meta_val_amazone($tval);
                        
                        $termname=$tval;
                        $mainternmna=$tval;
                        $term_idb=   $termname->term_id;
                        $amazone_brand_id = $tval;
                        if($termname !=''){
                            $termname=str_replace('&','ts',$mainternmna);
                ?>
                <li><div class="form-group"><label><input type="checkbox"name="selectbrands[]" class="selectbrands"  value="<?php echo esc_attr(str_replace(' ','_',$termname)); ?>" <?php if($brand == $tval){ echo "checked";} ?>><?php echo $mainternmna; ?></label></div></li>
                <?php } endforeach; ?>
            </ul>
            
        </div>
        
        <?php } } ?>
        </div>    
        <div class="product-grid">
        <?php
        
        if(!empty($amazonresponse->SearchResult->Items)){
        foreach($amazonresponse->SearchResult->Items as $amazonresponseItems){ 
        
            $DetailPageURL=$amazonresponseItems->DetailPageURL; 
            $Images=$amazonresponseItems->Images->Primary->Large->URL;
            $brand=$amazonresponseItems->ItemInfo->ByLineInfo->Brand->DisplayValue;
            $features=$amazonresponseItems->ItemInfo->Features->DisplayValues;
            $ProductInfo=$amazonresponseItems->ItemInfo->Title->DisplayValue;
            $cuoffoffers=$amazonresponseItems->Offers->Listings['0']->Price->DisplayAmount;
            $discount=$amazonresponseItems->Offers->Listings['0']->Price->Savings->Percentage;
            $SavingBasis=$amazonresponseItems->Offers->Listings['0']->SavingBasis->DisplayAmount;
            $ean=$amazonresponseItems->ItemInfo->ExternalIds->EANs->DisplayValues['0'];
            $category = $amazonresponseItems->BrowseNodeInfo->BrowseNodes;
            $asin = $amazonresponseItems->ASIN;
          
            $categoryArray = [];
            
            $productdata=insert_custom_post_offers_amazon($ProductInfo,$Images,$features,$cuoffoffers,$discount,$SavingBasis,$DetailPageURL,$ean,$brand,$country,$categoryArray,$asin);

            // if(isset($category)){
            //     foreach($category as $key => $val){
            //       $catlink=get_or_create_category_url_amazone($val->ContextFreeName,'offers_category');
            //     }
            // }
            
             if(isset($category)){
                foreach($category as $key => $val){
                  $categoryArray[$val->Id] = $val->ContextFreeName;
                  get_or_create_category_url_amazone($val->ContextFreeName,'offers_category',$brand);
                  get_or_create_brand_url_amazone($val->ContextFreeName,'offers_brands' ,$brand);
                }
            }
            
             
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
         
         <?php  if(!empty($productdata['cats'])){ ?>
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
            <span class="visit-button__link visit-button__link-box">Vedi Offerta</span>
         </div></a>
      
      <?php
      if(!empty($productdata['postlink'])){
      ?>
      <div class="view-offers-link">
         <a target="_blank" href="<?php echo $productdata['postlink']; ?>" > Scopri di più </a>
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
                     
                   
                     <h2  class="offer-box__title">
                        <?php echo  $allgetoffers->title; ?>
                      </h2>
                    <?php  if($brandname !=''){ ?>  
                    <div class="offer-labels">
                         <span class="category">Brands:<?php echo implode(',',$allbrandsarray); ?></span>
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
	
	
	
	<?php } ?>
    </main>
</div>       

<?php get_footer(); ?>
<script>
    jQuery(document).ready(function($) {
    $('.selectbrands').change(function() {
        /*var selectedBrands = [];
        $('.selectbrands:checked').each(function() {
            selectedBrands.push($(this).val());
        });*/
        var str = jQuery(this).val();
         var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
         var catid="<?php echo $kelkoo_category_id; ?>";
         //var str = selectedBrands.join(',');
         var currentUrl = "<?php echo get_term_link($term_id);  ?>";
         var querystring='brand='+str;
         var reurl=currentUrl+'?'+querystring;
        /*$.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'update_selected_brands',
                selectedBrands: str,
                catid:catid
            },
            success: function(response) {
                // Handle the response
                console.log(response);
            }
        });*/
        window.location.href=reurl;
    });
});
</script>