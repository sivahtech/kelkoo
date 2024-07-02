<?php

get_header(); 
$postids=get_the_ID();
$cn=get_option('kelkoo_defualt_country');
$gtin=get_post_meta($postids,'offers_ean_code',true); 
$eancodeadded=get_post_meta($postids,'code_ean',true);
$asin=get_post_meta($postids,'code_asin',true);


$buttontext=get_option('keloo_view_offer');
$eancode=$gtin;
$country=$cn;
if($asin){
    
$amazonProduct = getAmazonProduct($asin);
    if($amazonProduct){
    $amazonProduct = json_decode($amazonProduct);
    $amazoneProduct = $amazonProduct->ItemsResult->Items[0];
    $brand=$amazoneProduct->ItemInfo->ByLineInfo->Brand->DisplayValue;
    $category = $amazoneProduct->BrowseNodeInfo->BrowseNodes[0];
    $amazoneProduct->cat_url =  get_or_create_category_url_amazone($category->ContextFreeName,'offers_category',$brand);
    $amazoneProduct->brand_url =  get_or_create_brand_url_amazone($category->ContextFreeName,'offers_brands' ,$brand);
   
        if(count($amazoneProduct->Offers->Summaries) > 0){
            foreach($amazoneProduct->Offers->Summaries as $summary){
                  if($summary->Condition->Value == 'New'){
                    $amazoneProduct->minPrice = $summary->LowestPrice->Amount;
                    $amazoneProduct->maxPrice = $summary->HighestPrice->Amount;
                }
            }
        }
        if(isset($amazoneProduct->minPrice)){
            $amazoneProduct->price = $amazoneProduct->minPrice;
        }else{
            $amazoneProduct->price = $amazoneProduct->Offers->Listings[0]->Price->Amount;
        }    
    }
    
    
}

if($eancode !=''){
    
$response=get_offers_with_eancode($country,$eancode);


$alloffers= json_decode($response['success']);


if(!empty($alloffers->offers)){
	if($alloffers->offers){
		$currentPage=$alloffers->meta->offers->currentPage;
		$NextPage=$alloffers->meta->offers->nextPage;
		$allgetoffers=$alloffers->offers;
	}


$lastelement=end($allgetoffers);

$category=$allgetoffers['0']->category->name;
$categoryid=$allgetoffers['0']->category->id;
$brandname=$allgetoffers['0']->brand->name;

$brandid=$allgetoffers['0']->brand->id;
if($category !=''){
$allkcategories=explode(',',$category);
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

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

    <div class="gtin-header">
       <div class="gtin-header__picture-container">
          <div data-cy="offer-carousel-images" class="offer-carousel__images">
             <!----> 
             <div>
                <div class="offer-carousel__images-main">
                   <!----> <img src="<?php echo $allgetoffers['0']->images['0']->url; ?>" alt="<?php echo $allgetoffers['0']->title; ?>" title="<?php echo $allgetoffers['0']->title; ?>" data-cy="img-main"> <!---->
                </div>
             </div>
          </div>
       </div>
        <div class="gtin-header__container">
        <div class="headertitle">    
          <h1 data-cy="product-name" class="gtin-header__title">
             <?php echo $allgetoffers['0']->title; ?> (EAN:<?php echo $eancodeadded; ?>)
          </h1>
           <?php if($category !=''){ ?>
          <span class="cat">Category: <?php echo implode(',',$allcatsarray); ?></span>
          <?php } if($brandname !=''){ ?>
          <span class="brands">Brand: <?php echo implode(',',$allbrandsarray); ?></span>
          <?php } ?>
        </div>  
          <div class="gtin-header__price-popularity-container">
             <div data-cy="product-price-range" class="gtin-header__price-range">
               <?php echo $allgetoffers['0']->price; ?> <?php echo $allgetoffers['0']->currency; ?>
                <span>
                -
                <?php echo $lastelement->price; ?> <?php echo $lastelement->currency; ?>
                
                </span> 
             </div>
             
          </div>
          <?php $description = $allgetoffers['0']->description;  ?>
          <!--<div data-cy="product-description" class="gtin-header__description">-->
          <!--   <div><?php echo $allgetoffers['0']->description; ?></div>-->
            
          <!--</div>-->
        </div>
    </div>
    <div data-cy="offers-grid" monitor_offers="true" class="result-offers">
       
       <div id="see-all-products" class="offer-grid">
           <?php
           
                if($amazoneProduct){
                array_push($allgetoffers,$amazoneProduct);
                
                // Function to compare prices in descending order
                function comparePriceDesc($a, $b) {
                    return $a->price <=> $b->price;
                }
                
                // Sort the array using usort
                usort($allgetoffers, 'comparePriceDesc');
                
                }

           foreach($allgetoffers as $allgetoffers){
            if(!isset($allgetoffers->ASIN)){ ?>
            <div class="offer-grid__item offer-grid__item--row">
             <div data-cy="offer-row" class="offer-row">
                <div class="offer-row__name">
                   <a href="<?php echo $allgetoffers->goUrl; ?>" target="_blank" title="<?php echo $allgetoffers->title; ?>" rel="sponsored">
                      <h2 class="offer-row__title offer-row__title--margin">
                         <?php echo $allgetoffers->title; ?> 
                      </h2>
                   </a>
                </div>
                <div class="offer-row__price">
                   <a href="<?php echo $allgetoffers->goUrl; ?>" target="_blank" title="<?php echo $allgetoffers->title; ?>">
                      <div class="offer-price offer-row__price">
                         <div class="offer-price__price-container">
                            <span data-cy="offer-price" class="offer-price__price">Price: <?php echo $allgetoffers->price; ?></span><?php echo $allgetoffers->currency; ?></span> 
                            <?php if($allgetoffers->rebatePercentage > 0){ ?>
                            <span data-cy="offer-price-without-rebate" class="offer-price__without-rebate"><?php echo $allgetoffers->priceWithoutRebate; ?>,<span class="price-fraction"><?php echo $allgetoffers->rebatePercentage; ?></span>&nbsp;<?php echo $allgetoffers->currency; ?></span> 
                            <?php } ?>
                         </div>
                         <div data-cy="offer-free-delivery" class="offer-price__delivery">Delivery:
                            <?php if($allgetoffers->deliveryCost > 0) { 
                                echo $allgetoffers->deliveryCost.' '. $allgetoffers->currency; 
                            }else{
                                echo "Free Delivery";
                            }
                            ?>
                         </div>
                      </div>
                   </a>
                </div>
                <div class="offer-row__delivery">
                   <div title="Disponibilità immediata">Time: <?php echo $allgetoffers->timeToDeliver; ?>
                   </div>
                </div>
                <div class="offer-row__merchant-container">
                    <div class="offer-row__merchant">
                       <a href="<?php echo $allgetoffers->goUrl; ?>"><?php if($allgetoffers->merchant->logoUrl !=''){ ?><img src="<?php echo $allgetoffers->merchant->logoUrl; ?>" class="offer-row__merchant-logo"><?php }else{ echo $allgetoffers->merchant->name; } ?></a>
                    </div>
                   
                    <div class="offer-row__view-offer">
                      <div data-cy="visit-button" class="visit-button">
                        <a href="<?php echo $allgetoffers->goUrl; ?>" target="_blank" title="<?php echo $allgetoffers->title; ?>" data-cy="visit-button" rel="sponsored" class="visit-button__link visit-button__link-row">
                            <span><?php echo $buttontext; ?></span> 
                        </a>
                      </div>
                    </div>
                </div>
            </div>
            
        </div>
        <?php } else { ?>
        
         <div class="offer-grid__item offer-grid__item--row">
             <div data-cy="offer-row" class="offer-row">
                <div class="offer-row__name">
                   <a href="<?php echo $allgetoffers->DetailPageURL; ?>" target="_blank" title="<?php echo $allgetoffers->ItemInfo->Title->DisplayValue; ?>" rel="sponsored">
                      <h2 class="offer-row__title offer-row__title--margin">
                         <?php echo $allgetoffers->ItemInfo->Title->DisplayValue; ?>
                      </h2>
                   </a>
                </div>
                <div class="offer-row__price">
                   <a href="<?php echo $allgetoffers->DetailPageURL; ?>" target="_blank" title="<?php echo $allgetoffers->ItemInfo->Title->DisplayValue; ?>">
                      <div class="offer-price offer-row__price">
                         <div class="offer-price__price-container">
                             <?php if(isset($allgetoffers->minPrice) && isset($allgetoffers->maxPrice) ){ ?>
                                 
                            <span data-cy="offer-price" class="offer-price__price">Price:<?php echo $allgetoffers->minPrice; ?> - <?php echo $allgetoffers->maxPrice; ?> </span><?php echo $allgetoffers->Offers->Listings[0]->Price->Currency; ?></span> 
                                 
                             <?php } else {  ?>
                            <span data-cy="offer-price" class="offer-price__price">Price: <?php echo $allgetoffers->Offers->Listings[0]->Price->Amount; ?></span><?php echo $allgetoffers->Offers->Listings[0]->Price->Currency; ?></span> 
                            
                            <?php if($allgetoffers->Offers->Listings[0]->Price->Savings->Percentage > 0){ ?>
                            <span data-cy="offer-price-without-rebate" class="offer-price__without-rebate"><?php echo $allgetoffers->Offers->Listings[0]->SavingBasis->Amount ?>,<span class="price-fraction"><?php echo $allgetoffers->Offers->Listings[0]->Price->Savings->Percentage ?></span>&nbsp;<?php echo $allgetoffers->Offers->Listings[0]->Price->Currency; ?></span> 
                            <?php } } ?>
                            
                         </div>
                         <div data-cy="offer-free-delivery" class="offer-price__delivery">Delivery:
                            <?php if($allgetoffers->Offers->Listings[0]->DeliveryInfo->IsFreeShippingEligible == true) { 
                                echo "Free Delivery";
                            }else{?>
                            
                                <a href="<?php echo $allgetoffers->DetailPageURL; ?>" target="_blank" title="<?php echo $allgetoffers->ItemInfo->Title->DisplayValue; ?>"> Check Delivery Status</a>
                            <?php }
                            ?>
                         </div>
                      </div>
                   </a>
                </div>
                <div class="offer-row__delivery">
                   <div title="Disponibilità immediata">Time:<a href="<?php echo $allgetoffers->DetailPageURL; ?>" target="_blank" title="<?php echo $allgetoffers->ItemInfo->Title->DisplayValue; ?>"> Check Time</a>
                   </div>
                </div>
                <div class="offer-row__merchant-container">
                    <div class="offer-row__merchant">
                       <a href="<?php echo $allgetoffers->DetailPageURL; ?>"><img src="/wp-content/uploads/2024/05/amazon.webp" alt="image" width="40px"></a>
                    </div>              
                   
                    <div class="offer-row__view-offer">
                      <div data-cy="visit-button" class="visit-button">
                        <a href="<?php echo $allgetoffers->DetailPageURL; ?>" target="_blank" title="<?php echo $allgetoffers->ItemInfo->Title->DisplayValue; ?>" data-cy="visit-button" rel="sponsored" class="visit-button__link visit-button__link-row">
                            <span><?php echo $buttontext; ?></span> 
                        </a>
                      </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        
        <?php   }
           }
        ?>
        
        </div>
       <!---->
    </div>
    <div data-cy="product-description" class="gtin-header__description">
             <div><?php echo $description; ?></div>
            
    </div>
    </main>
</div>
<?php }else{ 
    
$featured_image_id= get_post_thumbnail_id($postids);
$price=get_post_meta($postids,'price',true);
$currency=get_post_meta($postids,'price_cuurency',true);
    if($featured_image_id){
          $featured_image_url = wp_get_attachment_url($featured_image_id);
    }else{
          $featured_image_url = get_post_meta($postids, 'feature_image', true );
    }

?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

    <div class="gtin-header">
       <div class="gtin-header__picture-container">
          <div data-cy="offer-carousel-images" class="offer-carousel__images">
             <!----> 
             <div>
                <div class="offer-carousel__images-main">
                   <!----> <img src="<?php echo $featured_image_url; ?>" alt="<?php echo get_the_title(); ?>" title="<?php echo get_the_title(); ?>" data-cy="img-main"> <!---->
                </div>
             </div>
          </div>
       </div>
        <div class="gtin-header__container">
          <h1 data-cy="product-name" class="gtin-header__title">
             <?php echo get_the_title(); ?> (EAN:<?php echo $eancode; ?>)
          </h1>
           <?php if($amazoneProduct->cat_url !=''){ ?>
          <span class="cat">Category: <a href=" <?php echo $amazoneProduct->cat_url ?>"><?php echo $category->ContextFreeName ?></a></span>
          <?php } if($amazoneProduct->brand_url !=''){ ?>
          <span class="brands">Brand: <a href=" <?php echo $amazoneProduct->brand_url ?>"><?php echo $brand ?></a></span>
          <?php } ?>
          
          <div class="gtin-header__price-popularity-container">
             <div data-cy="product-price-range" class="gtin-header__price-range">
               <?php echo $price; ?> <?php echo $currency; ?>
              
             </div>
             
          </div>
          
        </div>
    </div>
    <div data-cy="offers-grid" monitor_offers="true" class="result-offers">
       
       <div id="see-all-products" class="offer-grid">
          
          <!-- Amazon api  result-->
        <?php if($amazoneProduct){ ?>
            
       
        <div class="offer-grid__item offer-grid__item--row">
             <div data-cy="offer-row" class="offer-row">
                <div class="offer-row__name">
                   <a href="<?php echo $amazoneProduct->DetailPageURL; ?>" target="_blank" title="<?php echo $amazoneProduct->ItemInfo->Title->DisplayValue; ?>" rel="sponsored">
                      <h2 class="offer-row__title offer-row__title--margin">
                         <?php echo $amazoneProduct->ItemInfo->Title->DisplayValue; ?>
                      </h2>
                   </a>
                </div>
                <div class="offer-row__price">
                   <a href="<?php echo $amazoneProduct->DetailPageURL; ?>" target="_blank" title="<?php echo $amazoneProduct->ItemInfo->Title->DisplayValue; ?>">
                      <div class="offer-price offer-row__price">
                         <div class="offer-price__price-container">
                           
                           <?php if(isset($amazoneProduct->minPrice) && isset($amazoneProduct->maxPrice) ){ ?>
                                 
                            <span data-cy="offer-price" class="offer-price__price">Price:<?php echo $amazoneProduct->minPrice; ?> - <?php echo $amazoneProduct->maxPrice; ?> </span><?php echo $amazoneProduct->Offers->Listings[0]->Price->Currency; ?></span> 
                                 
                             <?php } else {  ?>
                            <span data-cy="offer-price" class="offer-price__price">Price: <?php echo $amazoneProduct->Offers->Listings[0]->Price->Amount; ?></span><?php echo $amazoneProduct->Offers->Listings[0]->Price->Currency; ?></span> 
                            
                            <?php if($amazoneProduct->Offers->Listings[0]->Price->Savings->Percentage > 0){ ?>
                            <span data-cy="offer-price-without-rebate" class="offer-price__without-rebate"><?php echo $amazoneProduct->Offers->Listings[0]->SavingBasis->Amount ?>,<span class="price-fraction"><?php echo $amazoneProduct->Offers->Listings[0]->Price->Savings->Percentage ?></span>&nbsp;<?php echo $amazoneProduct->Offers->Listings[0]->Price->Currency; ?></span> 
                            <?php } } ?>
                            
                         </div>
                         <div data-cy="offer-free-delivery" class="offer-price__delivery">Delivery:
                            <?php if($amazoneProduct->Offers->Listings[0]->DeliveryInfo->IsFreeShippingEligible == true) { 
                                echo "Free Delivery";
                            }else{?>
                            
                                <a href="<?php echo $amazoneProduct->DetailPageURL; ?>" target="_blank" title="<?php echo $amazoneProduct->ItemInfo->Title->DisplayValue; ?>"> Check Delivery Status</a>
                            <?php }
                            ?>
                         </div>
                      </div>
                   </a>
                </div>
                <div class="offer-row__delivery">
                   <div title="Disponibilità immediata">Time:<a href="<?php echo $amazoneProduct->DetailPageURL; ?>" target="_blank" title="<?php echo $amazoneProduct->ItemInfo->Title->DisplayValue; ?>"> Check Time</a>
                   </div>
                </div>
                <div class="offer-row__merchant-container">
                    <div class="offer-row__merchant">
                       <a href="<?php echo $amazoneProduct->DetailPageURL; ?>"><img src="/wp-content/uploads/2024/05/amazon.webp" alt="image" width="40px"></a>
                    </div>              
                   
                    <div class="offer-row__view-offer">
                      <div data-cy="visit-button" class="visit-button">
                        <a href="<?php echo $amazoneProduct->DetailPageURL; ?>" target="_blank" title="<?php echo $amazoneProduct->ItemInfo->Title->DisplayValue; ?>" data-cy="visit-button" rel="sponsored" class="visit-button__link visit-button__link-row">
                            <span><?php echo $buttontext; ?></span> 
                        </a>
                      </div>
                    </div>
                </div>
            </div>
            
        </div>
         
           <?php }else{ ?>
          
          <div class="offer-grid__item offer-grid__item--row">
             <div data-cy="offer-row" class="offer-row">
                <div class="offer-row__name">
                   <a href="<?php echo get_post_meta($postids,'custom_go_url',true); ?>" target="_blank"  rel="sponsored">
                      <h2 class="offer-row__title offer-row__title--margin">
                          <?php echo get_the_title(); ?>
                      </h2>
                   </a>
                </div>
                <div class="offer-row__price">
                   <a href="<?php echo get_post_meta($postids,'custom_go_url',true); ?>" target="_blank">
                      <div class="offer-price offer-row__price">
                         <div class="offer-price__price-container">
                            <span data-cy="offer-price" class="offer-price__price">Price:<?php echo get_post_meta($postids,'price',true); ?></span><?php echo get_post_meta($postids,'price_cuurency',true); ?></span> 
                            
                         </div>
                        </div> 
                      
                   </a>
                </div>
                
                <div class="offer-row__merchant-container">
                    
                   
                    <div class="offer-row__view-offer">
                      <div data-cy="visit-button" class="visit-button">
                        <a href="<?php echo get_post_meta($postids,'custom_go_url',true); ?>" target="_blank" class="visit-button__link visit-button__link-row">
                            <span>Vedi Offerta </span> 
                        </a>
                      </div>
                    </div>
                </div>
            </div>
        </div>
          
          <?php } ?>
        </div>
       <!---->
    </div>
    <div data-cy="product-description" class="gtin-header__description">
             <div><?php
             echo get_the_content();
             ?></div>
            
    </div>
    </main>
</div>


<?php
}
}
get_footer();
?>