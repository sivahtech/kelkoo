<?php
get_header(); 
$postids=get_the_ID();
 $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$url_segments = explode('/', rtrim($actual_link, '/'));
$gtinv = end($url_segments);
$cn=get_option('kelkoo_defualt_country');
$gtin=get_post_meta($postids,'offers_ean_code',true);
$eancodeadded=get_post_meta($postids,'code_ean',true);
if($gtin ==''){
    $offers_ids=get_post_meta($postids,'offers_ids',true);
    $response=check_if_offers_stillexists($cn,$offers_ids);
    
    if($response['error']==0){
        $alloffers= json_decode($response['success']);
        if($alloffers->offers){
         	$allgetoffers=$alloffers->offers;
            $codeean=$allgetoffers[0]->code->ean;
            $gtin=$codeean;
            $descriptioncontent=$allgetoffers[0]->description;
        }else{
			$psoturl=get_permalink($postids);
			$searchstring=get_search_string_from_url($psoturl);
			wp_delete_post($postids);
			$redirecturl=site_url().'/?q='.$searchstring;
			wp_redirect($redirecturl);
			exit();
		}
    }
    
}

$buttontext=get_option('keloo_view_offer');
$eancode=$gtin;
$country=$cn;
if($eancode !=''){
$response=get_offers_with_eancode($country,$eancode);

if($response['error']==0){
	$alloffers= json_decode($response['success']);
	if($alloffers->offers){
		$currentPage=$alloffers->meta->offers->currentPage;
		$NextPage=$alloffers->meta->offers->nextPage;
		$allgetoffers=$alloffers->offers;
	}else{
		$psoturl=get_permalink($postids);
		$searchstring=get_search_string_from_url($psoturl);
		wp_delete_post($postids);
		$redirecturl=site_url().'/?q='.$searchstring;
		wp_redirect($redirecturl);
		exit();
	}
	
}

$lastelement=end($allgetoffers);
//print_r($lastelement);
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
          <h1 data-cy="product-name" class="gtin-header__title">
             <?php echo $allgetoffers['0']->title; ?> (EAN:<?php echo $eancodeadded; ?>)
          </h1>
          <div class="gtin-header__price-popularity-container">
             <div data-cy="product-price-range" class="gtin-header__price-range">
               <?php echo $allgetoffers['0']->price; ?> <?php echo $allgetoffers['0']->currency; ?>
                <span>
                -
                <?php echo $lastelement->price; ?> <?php echo $lastelement->currency; ?>
                
                </span> 
             </div>
             
          </div>
          <div data-cy="product-description" class="gtin-header__description">
             <div><?php echo $allgetoffers['0']->description; ?></div>
            
          </div>
        </div>
    </div>
    <div data-cy="offers-grid" monitor_offers="true" class="result-offers">
       
       <div id="see-all-products" class="offer-grid">
           <?php foreach($allgetoffers as $allgetoffers){ ?>
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
        <?php } ?>
         
          
          
        </div>
       <!---->
    </div>
    </main>
</div>
<?php }else{ ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

    <div class="gtin-header">
       <div class="gtin-header__picture-container">
          <div data-cy="offer-carousel-images" class="offer-carousel__images">
             <!----> 
             <div>
                <div class="offer-carousel__images-main">
                   <!----> <img src="<?php echo $allgetoffers['0']->images['0']->url; ?>" alt="<?php echo get_the_title(); ?>" title="<?php echo get_the_title(); ?>" data-cy="img-main"> <!---->
                </div>
             </div>
          </div>
       </div>
        <div class="gtin-header__container">
          <h1 data-cy="product-name" class="gtin-header__title">
             <?php echo get_the_title(); ?>
          </h1>
          <div class="gtin-header__price-popularity-container">
             <div data-cy="product-price-range" class="gtin-header__price-range">
               <?php echo $allgetoffers['0']->price; ?> <?php echo $allgetoffers['0']->currency; ?>
              
             </div>
             
          </div>
          <div data-cy="product-description" class="gtin-header__description">
             <div><?php
             if($descriptioncontent){
                 echo $descriptioncontent;
             }
             ?></div>
            
          </div>
        </div>
    </div>
    <div data-cy="offers-grid" monitor_offers="true" class="result-offers">
       
       <div id="see-all-products" class="offer-grid">
           
           <div class="offer-grid__item offer-grid__item--row">
             <div data-cy="offer-row" class="offer-row">
                <div class="offer-row__name">
                   <a href="<?php echo $allgetoffers[0]->goUrl; ?>" target="_blank" title="<?php echo $allgetoffers[0]->title; ?>" rel="sponsored">
                      <h2 class="offer-row__title offer-row__title--margin">
                         <?php echo $allgetoffers[0]->title; ?>
                      </h2>
                   </a>
                </div>
                <div class="offer-row__price">
                   <a href="<?php echo $allgetoffers[0]->goUrl; ?>" target="_blank" title="<?php echo $allgetoffers[0]->title; ?>">
                      <div class="offer-price offer-row__price">
                         <div class="offer-price__price-container">
                            <span data-cy="offer-price" class="offer-price__price">Price: <?php echo $allgetoffers[0]->price; ?></span><?php echo $allgetoffers[0]->currency; ?></span> 
                            <?php if($allgetoffers[0]->rebatePercentage > 0){ ?>
                            <span data-cy="offer-price-without-rebate" class="offer-price__without-rebate"><?php echo $allgetoffers[0]->priceWithoutRebate; ?>,<span class="price-fraction"><?php echo $allgetoffers[0]->rebatePercentage; ?></span>&nbsp;<?php echo $allgetoffers[0]->currency; ?></span> 
                            <?php } ?>
                         </div>
                         <div data-cy="offer-free-delivery" class="offer-price__delivery">Delivery:
                            <?php if($allgetoffers[0]->deliveryCost > 0) { 
                                echo $allgetoffers[0]->deliveryCost.' '. $allgetoffers[0]->currency; 
                            }else{
                                echo "Free Delivery";
                            }
                            ?>
                         </div>
                      </div>
                   </a>
                </div>
                <div class="offer-row__delivery">
                   <div title="Disponibilità immediata">Time: <?php echo $allgetoffers[0]->timeToDeliver; ?>
                   </div>
                </div>
                <div class="offer-row__merchant-container">
                    <div class="offer-row__merchant">
                       <a href="<?php echo $allgetoffers[0]->goUrl; ?>"><?php if($allgetoffers[0]->merchant->logoUrl !=''){ ?><img src="<?php echo $allgetoffers[0]->merchant->logoUrl; ?>" class="offer-row__merchant-logo"><?php }else{ echo $allgetoffers[0]->merchant->name; } ?></a>
                    </div>
                   
                    <div class="offer-row__view-offer">
                      <div data-cy="visit-button" class="visit-button">
                        <a href="<?php echo $allgetoffers[0]->goUrl; ?>" target="_blank" title="<?php echo $allgetoffers[0]->title; ?>" data-cy="visit-button" rel="sponsored" class="visit-button__link visit-button__link-row">
                            <span><?php echo $buttontext; ?></span> 
                        </a>
                      </div>
                    </div>
                </div>
            </div>
        </div>
           
           
           
           
            <!--div class="offer-grid__item offer-grid__item--row">
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
                            <span><?php echo get_post_meta($postids,'see_button_text',true); ?></span> 
                        </a>
                      </div>
                    </div>
                </div>
            </div>
        </div-->
        
         
          
          
        </div>
       <!---->
    </div>
    </main>
</div>


<?php
}
get_footer();
?>