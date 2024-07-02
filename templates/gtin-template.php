<?php
/*
Template Name: Gtin Template
*/
get_header(); 
 $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$url_segments = explode('/', rtrim($actual_link, '/'));
$gtinv = end($url_segments);

$cn=get_query_var('cn');
$gtin = get_query_var('gtin');
if(empty($cn)){
    $cn=$url_segments['4'];
}
if(empty($gtin)){
    $gtin=$gtinv;
}
$eancode=$gtin;
$country=$cn;
$response=get_offers_with_eancode($country,$eancode);
if($response['error']==0){
	$alloffers= json_decode($response['success']);
	$currentPage=$alloffers->meta->offers->currentPage;
	$NextPage=$alloffers->meta->offers->nextPage;
	$allgetoffers=$alloffers->offers;
	
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
                 <?php echo $allgetoffers['0']->title; ?>
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
         <!----> 
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
                        <span data-cy="offer-price-without-rebate" class="offer-price__without-rebate"><?php echo $allgetoffers->priceWithoutRebate; ?>,<span class="price-fraction"><?php echo $allgetoffers->rebatePercentage; ?></span>&nbsp;<?php echo $allgetoffers->currency; ?></span> <!----> <!---->
                        <?php } ?>
                     </div>
                     <div data-cy="offer-free-delivery" class="offer-price__delivery">Delivery:
                        <?php if($allgetoffers->deliveryCost > 0) { echo $allgetoffers->deliveryCost.' '. $allgetoffers->currency; 
                        }else{
                            echo "Free Delivery";
                        }
                        ?>
                     </div>
                  </div>
               </a>
            </div>
            <div class="offer-row__delivery">
               <!----> 
               <div title="Disponibilità immediata">Time: <?php echo $allgetoffers->timeToDeliver; ?></div>
            </div>
            <div class="offer-row__merchant-container">
               <div class="offer-row__merchant"><a href="<?php echo $allgetoffers->goUrl; ?>"><?php if($allgetoffers->merchant->logoUrl !=''){ ?><img src="<?php echo $allgetoffers->merchant->logoUrl; ?>" class="offer-row__merchant-logo"><?php }else{ echo $allgetoffers->merchant->name; } ?></a></div>
               
               <div class="offer-row__view-offer">
                  <div data-cy="visit-button" class="visit-button">
                     <a href="<?php echo $allgetoffers->goUrl; ?>" target="_blank" title="<?php echo $allgetoffers->title; ?>" data-cy="visit-button" rel="sponsored" class="visit-button__link visit-button__link-row">
                        <span>Vedi offerta</span> 
                        
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

<?php
get_footer();
?>