<?php
/*
Plugin Name: Kelkoo Group Search
Plugin URI: https://example.com/my-plugin
Description: This is a description of my plugin.
Version: 1.0
Author: John Doe
Author URI: https://example.com/author
License: GPL2
*/ 
ob_start();

include_once plugin_dir_path(__FILE__) . 'feeds/all_offers.php';
include_once plugin_dir_path(__FILE__) . 'includes/custom-functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/amazon_api.php';
ob_end_clean();

register_activation_hook(__FILE__, 'Kelkoo_group_search_activate');
register_deactivation_hook(__FILE__, 'deactivate_Kelkoo_group_search');
function Kelkoo_group_search_activate() {
	global $wpdb;
    $table_name = $wpdb->prefix . 'kelkoo_search_shortcodes';
    $table_name2 = $wpdb->prefix . 'kelkoo_search_countries';
	$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        country varchar(255) DEFAULT NULL,
		language varchar(255) DEFAULT NULL,
		api_key longtext DEFAULT NULL,
		offer_button_text varchar(255) DEFAULT NULL,
		find_out_more_text varchar(255) DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $sql2 = "CREATE TABLE $table_name2 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        country varchar(255) DEFAULT NULL,
		county_code varchar(255) DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');	
    dbDelta($sql);
    dbDelta($sql2);
}
add_action('init','create_a_query_table_tha_not_exist');
function create_a_query_table_tha_not_exist()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'all_query_table';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    	$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id varchar(255) NULL,
			user_query varchar(255) NULL,
			post_id varchar(255) NULL,
			status varchar(255) NULL,
			add_time varchar(255) NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
function deactivate_Kelkoo_group_search() {
	global $wpdb;
    $table_name = $wpdb->prefix . 'kelkoo_search_shortcodes';
    $table_name2 = $wpdb->prefix . 'kelkoo_search_countries';
	$wpdb->query("DROP TABLE IF EXISTS $table_name");
	$wpdb->query("DROP TABLE IF EXISTS $table_name2");
}
function enqueue_plugin_styles() {
     wp_enqueue_style('plugin-style', plugin_dir_url(__FILE__) . 'assets/custom-style.css?uyt', array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'enqueue_plugin_styles');
add_action('admin_menu', 'Kelkoo_group_search_menu');
function Kelkoo_group_search_menu() {
    add_menu_page('Kelkoo Search', 'Kelkoo Search', 'manage_options', 'kelkoo-search', 'kelkoo_search_page');
    add_submenu_page('kelkoo-search', 'All Shortcodes', 'All Shortcodes', 'manage_options', 'all-shortcodes', 'all_shortcodes_page');
	add_submenu_page('kelkoo-search', 'Api Key', 'Api Key', 'manage_options', 'api-key', 'api_key_page');
	add_submenu_page('kelkoo-search', 'Country List', 'Country List', 'manage_options', 'country-list', 'country_list_page');
}
function country_list_page()
{
    global $wpdb;
    $table_name2 = $wpdb->prefix . 'kelkoo_search_countries';
    if(isset($_POST['delete_common_leage_shedule'])){
        $scid=$_POST['schedule_id'];
		$where=array(
			'id'=>$scid
		);
		$wpdb->delete($table_name2,$where);
    }
    if(isset($_POST['add_country'])){
        $data=array(
            'country'=>$_POST['country_name'], 
            'county_code'=>$_POST['country_code'],
        );
        $wpdb->insert($table_name2,$data);
    }
    $allcoun = $wpdb->get_results('select * from '.$table_name2.'');
    ?>
	<style>
        /* Basic CSS for styling the form */
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
	<div class="container">
        <h2>Add Country</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Country Name:</label>
                <input type="text" name="country_name" id="country_name" required/>
            </div>
            <div class="form-group">
                <label for="name">Country Code:</label>
                <input type="text" name="country_code" id="country_code" required/>
            </div>
			<input type="hidden" name="add_country" value="add_country"/>	
            <button type="submit">Save</button>
        </form>
        <?php if($allcoun){?>
	<div class="coomon_league">
		<div class="wrap">
			<table id="common_league_table">
				<thead><tr><th>Country</th><th>Country Code</th><th>Acion</th></tr></thead><tbody>
				<?php foreach($allcoun as $common_leagues){ ?>
					<tr>
					<td><?php echo $common_leagues->country; ?></td>
					<td><?php echo $common_leagues->county_code; ?></td>
					
					<td>
						<form method="post" action="">
							<input type="hidden" name="schedule_id" value="<?php echo $common_leagues->id; ?>"/> 
							<input type="submit" name="delete_common_leage_shedule" class="btn btn-info" value="Delete Country"/>
						</form>
					</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php } ?>
    </div>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>	
	
	<script>
		jQuery(document).ready(function(){
			jQuery("#common_league_table").DataTable();
			
		});
	</script>
	<?php
}
function api_key_page()
{
    global $wpdb;
    $table_name2 = $wpdb->prefix . 'kelkoo_search_countries';
	if(isset($_POST['update_key'])){
			$kelkoo_api_key=$_POST['kelkoo_api_key'];
			$defualt_country=$_POST['country'];
			$keloo_view_offer=$_POST['keloo_view_offer'];
			$find_more=$_POST['find_more'];
			
			
			$amazonaccesskey=$_POST['amazon_access_key'];
			$amazonsecretkey=$_POST['amazon_secret_key'];
			$regionname=$_POST['amazon_region_name'];
			if($amazonaccesskey){
				update_option('amazon_access_key',$amazonaccesskey);
			}
			if($amazonsecretkey){
				update_option('amazon_secret_key',$amazonsecretkey);
			}
			if($regionname){
				update_option('amazon_region_name',$regionname);
			}
			if($kelkoo_api_key){
				update_option('kelkoo_api_key',$kelkoo_api_key);
			}
			if($defualt_country){
				update_option('kelkoo_defualt_country',$defualt_country);
			}
			if($defualt_country){
				update_option('keloo_view_offer',$keloo_view_offer);
			}
			if($find_more){
				update_option('find_more',$find_more);
			}
	}
	$updatedkey=get_option('kelkoo_api_key');
	$updatedcountry=get_option('kelkoo_defualt_country');
	$buttontext=get_option('keloo_view_offer');
	$find_moretext=get_option('find_more');
	$amazon_access_key=get_option('amazon_access_key');
	$amazon_secret_key=get_option('amazon_secret_key');
	$amazon_region_name=get_option('amazon_region_name');
	
	$allcoun = $wpdb->get_results('select * from '.$table_name2.'');
	?>
	<style>
        /* Basic CSS for styling the form */
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
	<div class="container">
        <h2>Api Key</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Country:</label>
                <select name="country" id="country" required>
					<option value="">Choose Country</option>
					<?php foreach($allcoun as $common_leagues){ ?>
					<option value="<?php echo $common_leagues->county_code; ?>"<?php if($updatedcountry==$common_leagues->county_code){
					 echo "selected"; }?>><?php echo $common_leagues->country; ?></option>
					<?php } ?>
				</select>
            </div>
            <div class="form-group">
                <label for="name">View Offer Button Text:</label>
                <input type="text" name="keloo_view_offer" id="keloo_view_offer" value="<?php echo $buttontext; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Find Out More Button Text:</label>
                <input type="text" id="find_more" name="find_more" value="<?php echo $find_more; ?>" required>
            </div>
            <div class="form-group">
                <label for="name">Key:</label>
                <textarea name="kelkoo_api_key" id="kelkoo_api_key" rows="4" required><?php echo $updatedkey; ?></textarea>
            </div>
            <div class="form-group">
                <label for="name">Amazon Access Key:</label>
                <input type="text" id="amazon_access_key" name="amazon_access_key" value="<?php echo $amazon_access_key; ?>" required>
            </div>
            <div class="form-group">
                <label for="name">Amazon Secret Key:</label>
                 <input type="text" id="amazon_secret_key" name="amazon_secret_key" value="<?php echo $amazon_secret_key; ?>" required>
            </div>
            <div class="form-group">
                <label for="name">Amazon Region Name:</label>
                <input type="text" id="amazon_region_name" name="amazon_region_name" value="<?php echo $amazon_region_name; ?>" required>
            </div>
			<input type="hidden" name="update_key" value="update_key"/>	
            <button type="submit">Update Key</button>
        </form>
    </div>
	<?php
}
function kelkoo_search_page()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'kelkoo_search_shortcodes';
	$table_name2 = $wpdb->prefix . 'kelkoo_search_countries';
	if(isset($_POST['create_shortcode'])){
		$country=$_POST['country'];
		$language="";
		$see_offer=$_POST['see_offer'];
		$find_more=$_POST['find_more'];
		$data = array(
			'country' => $country,
			'language' => $language,
			'offer_button_text' => $see_offer,
			'find_out_more_text' => $find_more,
		);
		if($wpdb->insert($table_name, $data)){
			$shortcode='[kelkoo_group_shortcode_to_search cn="'.$country.'"  s_f="'.$see_offer.'" f_m="'.$find_more.'"]';
			$successdata='Your shortcode has been created successfully ! Copy and paste this shortcode '.$shortcode.' on page/post you want to add!';
		}			
		
	}
	$allcoun = $wpdb->get_results('select * from '.$table_name2.'');
	?>
	<style>
        /* Basic CSS for styling the form */
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
	<div class="container">
        <h2>Create A Shortcode</h2>
		<?php if(isset($successdata)){ ?>
			<div class="alert alert-success"><?php echo $successdata; ?></div>
		<?php } ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Country:</label>
                <select name="country" id="country" required>
					<option value="">Choose Country</option>
					<?php foreach($allcoun as $common_leagues){ ?>
					<option value="<?php echo $common_leagues->county_code; ?>"><?php echo $common_leagues->country; ?></option>
					<?php } ?>
				</select>
            </div>

            <div class="form-group">
                <label for="password">See Offer Button Text:</label>
                <input type="text" id="see_offer" name="see_offer" required>
            </div>

            <div class="form-group">
                <label for="password">Find Out More Button Text:</label>
                <input type="text" id="find_more" name="find_more" required>
            </div>
			<input type="hidden" name="create_shortcode" value="create_shortcode"/>	
            <button type="submit">Submit</button>
        </form>
    </div>
	
	<?php
}

function all_shortcodes_page()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'kelkoo_search_shortcodes';
	if(isset($_POST['delete_common_leage_shedule'])){
		$scid=$_POST['schedule_id'];
		$where=array(
			'id'=>$scid
		);
		$wpdb->delete($table_name,$where);
	}
	$all_shortcodes = $wpdb->get_results('select * from '.$table_name.'');
	?>
	<style>
	a.btn.btn-info,input.btn.btn-info {
		display: inline-block;
		padding: 10px 20px; 
		background-color: #007bff; 
		color: #fff; 
		text-decoration: none; 
		border: 1px solid #007bff;
		border-radius: 4px;
		cursor: pointer;
		transition: background-color 0.3s, color 0.3s;
		margin-top: 10px;
	}
	.notice.notice-warning.inline {
		display: none;
	}
	</style>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
	<h1>All Shortcodes</h1>
	<?php if($all_shortcodes){?>
	<div class="coomon_league">
		<div class="wrap">
			<table id="common_league_table">
				<thead><tr><th>country</th><th>See More Button</th><th>Find More Button</th><th>Shortcode</th><th>Acion</th></tr></thead><tbody>
				<?php foreach($all_shortcodes as $common_leagues){ ?>
					<tr>
					<td><?php echo $common_leagues->country; ?></td>
					<td><?php echo $common_leagues->offer_button_text; ?></td>
					<td><?php echo $common_leagues->find_out_more_text; ?></td>
					<td>[kelkoo_group_shortcode_to_search cn="<?php echo $common_leagues->country; ?>"  s_f="<?php echo $common_leagues->offer_button_text; ?>" f_m="<?php echo $common_leagues->find_out_more_text; ?>"]</td>
					<td>
						<form method="post" action="">
							<input type="hidden" name="schedule_id" value="<?php echo $common_leagues->id; ?>"/> 
							<input type="submit" name="delete_common_leage_shedule" class="btn btn-info" value="Delete Schedule"/>
						</form>
					</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php } ?>
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>	
	
	<script>
		jQuery(document).ready(function(){
			jQuery("#common_league_table").DataTable();
			
		});
	</script>
	<?php
}

add_shortcode('kelkoo_group_shortcode_to_search','create_shortcode_for_kelkoo_search');
function create_shortcode_for_kelkoo_search($atts = '')
{
	ob_start();
	$value = shortcode_atts( array(
        'cn' => '#',
		's_f'=>'#',
		'f_m'=>'#',
    ), $atts );
	$shortcodepid=get_the_ID();
	$pagelinks=get_permalink($shortcodepid);
	$country=$value['cn'];
	
	if((isset($_POST['q'])) || (isset($_GET['q']))){
		
		if(isset($_POST['q'])){
			$keyword=urlencode($_POST['q']);
		}else{
			$keyword=urlencode($_GET['q']);
		}
		if(isset($_GET['next'])){
        	$page=$_GET['next'];
        }else{
        	$page=1;
        }
        
        
		$amazonproducts=getAmazonProducts($keyword,$page);
		$amazonresponse=json_decode($amazonproducts);
		$response=get_data_from_kelkoo_api($country,$keyword);

	    if($response['error']==0){
			$alloffers= json_decode($response['success']);
			$currentPage=$alloffers->meta->offers->currentPage;
			$NextPage=$alloffers->meta->offers->nextPage;
		
			$allgetoffers=$alloffers->offers;
		}
		if(count($allgetoffers) > 0){
			if(isset($_GET['savesearch']))
			{
				global $wpdb;
				$table_name = $wpdb->prefix . 'all_query_table';
				$searchq=$_GET['q'];
				$search=str_replace(' ', '_', $searchq);
				$posttitle=$searchq;
				$postname='search_'.$search;
				$post = get_page_by_path($postname, OBJECT, 'post');
				if ($post) {
					$responsedata= "Query with title '$posttitle' exists.";
				} else {
					$authorid=get_current_user_id();
					$post_data = array(
						'post_title'    => $posttitle, 
						'post_name'     => $postname, 
						'post_content'  => $posttitle, 
						'post_status'   => 'publish', 
						'post_type'     => 'post', 
						'post_author'   => $authorid 
					);

					$post_id = wp_insert_post($post_data);
					if($post_id){
						$datatoadd=array(
							'user_id'=>$authorid,
							'user_query'=>$postname,
							'post_id'=>$post_id,
							'status'=>2,
							'add_time'=>date('Y-m-d H:i:s'),
						);
						$wpdb->insert($table_name,$datatoadd);
						$responsedata="Query Added Successfully!";
					}
				}
				
			}
			
		}
	}else{
	    $response=get_data_from_kelkoo_api_without_keyord($country);
		if($response['error']==0){
			$alloffers= json_decode($response['success']);
			$currentPage=$alloffers->meta->offers->currentPage;
			$NextPage=$alloffers->meta->offers->nextPage;
			$allgetoffers=$alloffers->offers;
			
		}
		
		$searchWords = ['iphone','mobiles','books','Sports, Fitness & Outdoors','Computers & Accessories','fitness'];
		
		$key = $searchWords[array_rand($searchWords)];
		
		$amazonproducts=getAmazonProducts($key,1);
		
		$amazonresponse=json_decode($amazonproducts);
	
	}
	?>

	 <div class="container">
       <?php if ( is_user_logged_in() ) { ?>
			<div class="response"><?php echo $responsedata; ?></div>		
	   <?php } ?>
        <form action="" method="GET" class="calc-search-form">
            <div class="form-group">
                <input type="text" id="search" name="q" placeholder="Enter your search term" <?php if(isset($keyword)){ echo 'value="'.urldecode($keyword).'"';} ?>required >
            </div>
			<input type="submit" name="searchs" class="searchadd" value="Search"/>
			<?php /*if ( is_user_logged_in() ) { ?>
			<input type="submit" name="savesearch" class="queryadd" value="Query"/>
			<?php }*/ ?>
        </form>
    </div>
    <div class="container mobilehide" id="mobilehide">
        <div class="category-crousel">
            <div class="crousel-inner">
                <h2 class="h4 serif mb-3">Categorie di tendenza</h2>
               <?php echo do_shortcode('[taxonomy_carousel_on_top]') ; ?>
            </div>
        </div>
    </div>
	<?php 
	
	
	if($allgetoffers || $amazonresponse){
		
	?>
	  <div class="container">
	      
        <h2>All Offers</h2>
        <div class="alloffers_inner">
       
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
            if(isset($category)){
                foreach($category as $key => $val){
                  $categoryArray[$val->Id] = $val->ContextFreeName;
                  get_or_create_category_url_amazone($val->ContextFreeName,'offers_category',$brand);
                  get_or_create_brand_url_amazone($val->ContextFreeName,'offers_brands' ,$brand);
                }
            }
                if(isset($amazonresponseItems->Offers->Summaries) && !empty($amazonresponseItems->Offers->Summaries)){
                 if(count($amazonresponseItems->Offers->Summaries) > 0){
                    foreach($amazonresponseItems->Offers->Summaries as $summary){
                          if($summary->Condition->Value == 'New'){
                            $amazonresponseItems->minPrice = $summary->LowestPrice->Amount;
                            $amazonresponseItems->maxPrice = $summary->HighestPrice->Amount;
                        }
                    }
                 }
                }
                
                if(isset($amazonresponseItems->maxPrice)){
                    $cuoffoffers = $amazonresponseItems->maxPrice; 
                }
      
            $productdata=insert_custom_post_offers_amazon($ProductInfo,$Images,$features,$cuoffoffers,$discount,$SavingBasis,$DetailPageURL,$ean,$brand,$country,$categoryArray,$asin);
            
            
            
      
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
          
          <?php  if($productdata['category']){ ?>
          <div class="offer-labels">
               <span class="category">Category: <?php echo  $productdata['category'][0]; ?>
                    
             </span>
         </div>
         <?php  } ?>
         
         <?php  if($productdata['cats'] !=''){ ?>
         <div class="offer-labels">
             <span class="category">Brands:<?php echo implode(',',$productdata['cats']); ?></span>
         </div>
         <?php  } ?>
         <div class="offer-price offer-box__price">
            <div class="offer-price__price-container">
               <span  class="offer-price__price"> <?php echo $amazonresponseItems->Offers->Listings[0]->Price->Currency; ?> <?php echo  $cuoffoffers; ?> 
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
               <?php echo $value['s_f']; ?>
              
            </span>
         </div></a>
      
      <?php
      if($productdata['postlink'] !=''){
      ?>
      <div class="view-offers-link">
         <a target="_blank" href="<?php echo $productdata['postlink']; ?>" >
          <?php echo $value['f_m']; ?>
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
         <a href="<?php echo  $gourl; ?>" target="_blank" rel="sponsored" class="offer-box__container"> <h2  class="offer-box__title">
            <?php echo  $allgetoffers->title; ?>
          </h2>
          </a>
         <?php  if($categoryname !=''){ ?>
          <div class="offer-labels">
             <span class="category">Category:<?php echo implode(',',$allcatsarray); ?></span>
         </div>
         <?php  } ?>
         <?php  if($brandname !=''){ ?>
         <div class="offer-labels">
             <span class="category">Brands:<?php echo implode(',',$allbrandsarray); ?></span>
         </div>
         <?php  } ?>
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
        <a href="<?php echo  $gourl; ?>" target="_blank" rel="sponsored" class="offer-box__container"> <div class="visit-button offer-box__visit-button">
            <span class="visit-button__link visit-button__link-box">
               <?php echo $value['s_f']; ?>
              
            </span>
         </div></a>
      
      <?php
     
      if($allgetoffers->description !=''){
        $desc=$allgetoffers->description;
      }else{
          $desc="";
      }
      $seemore=$value['s_f'];
      $currency=$allgetoffers->currency;
      if($codeean !=''){
          $offersids=insert_custom_post_offers($allgetoffers->title,$desc,$country,$codeean,$imgurl,$price,$gourl,$seemore,$currency,$merchantids,$offerids,$mainmerchatids,$categoryname,$categoryid,$brandid,$brandname);
          if($offersids !='Ean Already Exist'){
      ?>
      <div class="view-offers-link">
         <a target="_blank" href="<?php echo $offersids; ?>" >
          <?php echo $value['f_m']; ?>
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
					<li><a href="/?next=<?php echo $currentPage-1; ?><?php if($keyword !=''){ ?>&q=<?php echo $keyword; ?> <?php } ?>" class="prev">&laquo; Previous</a></li>
				<?php } ?> 
					<li><a href="#" class="current"><?php echo $currentPage; ?></a></li>
				<?php if(($NextPage > 1) & ($NextPage < 41)){ ?>	
					<li><a href="/?next=<?php echo $NextPage; ?><?php if($keyword !=''){ ?>&q=<?php echo $keyword; ?> <?php } ?>" class="next">Next &raquo;</a></li>
				<?php } ?>	
				</ul>
			</div>
		
		</div>
		<?php } ?>
    </div>
	
	
	
	<?php } ?>
	<?php
	return ob_get_clean();
}


add_shortcode('shortcode_to_show_all_offers_brands','custom_function_to_show_all_brands');
function custom_function_to_show_all_brands()
{
    ob_start();
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $taxonomy ='offers_brands';
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
    <div class="alloffers_inner">
        <div class="product-grid">
        <?php  foreach ( $terms as $term ) { 
        if($term){    
        
        ?>
        <div class="offer-grid__item offer-grid__item--box">
           <div  class="offer-box">
              <a href="<?php echo get_term_link( $term ); ?>" target="_blank" rel="sponsored" class="offer-box__container">
                 
                <h2  class="offer-box__title"><?php echo $term->name; ?></h2>
                </a>
            </div>
        </div>
    
        
        <?php }} ?>
         </div>
    </div>
      <?php  if ( $total_pages > 1 ) {
        $pagination = paginate_links( array(
            'base' => get_pagenum_link( 1 ) . '%_%',
            'format' => 'page/%#%/',
            'current' => $paged,
            'total' => $total_pages,
            'prev_text' => __('&laquo; Previous'),
            'next_text' => __('Next &raquo;'),
        ) );

        echo '<div class="pagination">' . $pagination . '</div>';
    }
    
    return ob_get_clean();
}



add_shortcode('shortcode_to_show_all_offers_category','custom_function_to_show_all_category');
function custom_function_to_show_all_category()
{
    ob_start();
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
    <div class="alloffers_inner">
        <div class="product-grid">
        <?php  foreach ( $terms as $term ) { 
        if($term){    
        //    print_r($term);
         $kelkoo_attchment_id = get_term_meta( $term->term_id, 'kelkoo_category_image', true );
         if($kelkoo_attchment_id){
            $attachment_url = wp_get_attachment_image_src( $kelkoo_attchment_id, 'full' );
            $attachment_url = $attachment_url[0];
         }else{
            $attachment_url = get_term_meta( $term->term_id, 'kelkoo_feature_image', true );
         }
        ?>
        <div class="offer-grid__item offer-grid__item--box">
           <div  class="offer-box">
              <a href="<?php echo get_term_link( $term ); ?>" target="_blank" rel="sponsored" class="offer-box__container">
                 <div class="offer-box__img-container">
                    <div class="offer-image offer-image--box">
                        <?php if($attachment_url){ ?>
                            <img height="224" width="224" src="<?php echo  $attachment_url; ?>"  class="offer-image__img">
                        
                        <?php }else{ ?>
                        
                        <?php echo  $term->name; ?>
                        
                        <?php } ?>
                    </div>
                </div>
                <h2  class="offer-box__title"><?php echo $term->name; ?></h2>
                </a>
            </div>
        </div>
    
        
        <?php }} ?>
         </div>
    </div>
      <?php  if ( $total_pages > 1 ) {
        $pagination = paginate_links( array(
            'base' => get_pagenum_link( 1 ) . '%_%',
            'format' => 'page/%#%/',
            'current' => $paged,
            'total' => $total_pages,
            'prev_text' => __('&laquo; Previous'),
            'next_text' => __('Next &raquo;'),
        ) );

        echo '<div class="pagination">' . $pagination . '</div>';
    }
    
    return ob_get_clean();
}
function get_offers_with_category_id($catids)
{
    if(isset($_GET['next'])){
		$page=$_GET['next'];
	}else{
		$page=1;
	}
    $updatedkey=get_option('kelkoo_api_key');
    $country=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/feeds/offers?country='.$country.'&categoryId='.$catids.'&format=json&additionalFields=description,codeEan,merchantName,merchantLogoUrl,brandId,brandName,categoryName&pageSize=12&page='.$page.'';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
		'Accept-encoding: gzip',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	$decodedData = gzdecode($response);
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$decodedData;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function get_allcategory_list_kelkoo($country)
{
    $updatedkey=get_option('kelkoo_api_key');
    $country=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/feeds/category-list?country='.$country.'&format=json';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
		'Accept-encoding: gzip',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	$decodedData = gzdecode($response);
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$decodedData;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
    
}
function check_if_offers_stillexists($country,$offerids)
{
    $updatedkey=get_option('kelkoo_api_key');
    $country=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$country.'&filterBy=offerId:'.$offerids.'&additionalFields=description,codeEan,merchantName,merchantLogoUrl';
   
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function get_offers_with_eancode($country,$eancode)
{
    $updatedkey=get_option('kelkoo_api_key');
    $country=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$country.'&filterBy=codeEan:'.$eancode.'&additionalFields=description,codeEan,merchantName,merchantLogoUrl,categoryName,brandId,brandName,categoryLogoUrl&sortBy=price&sortDirection=asc';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function get_data_from_kelkoo_api_without_keyord($country)
{
	$updatedkey=get_option('kelkoo_api_key');
	if(isset($_GET['next'])){
		$page=$_GET['next'];
	}else{
		$page=1;
	}
	$country=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$country.'&additionalFields=description,codeEan,merchantName,categoryName,brandId,brandName&pageSize=12&page='.$page.'';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function kelkoo_get_all_category_offers_by_brand_id($brandsids,$catids)
{
    if(isset($_GET['next'])){
		$page=$_GET['next'];
	}else{
		$page=1;
	}
    foreach($brandsids as $key=>$val)
    {
        $bfilter .='&filterBy=brandId:'.$val.'';
    }
    $updatedkey=get_option('kelkoo_api_key');
    $updatedcountry=get_option('kelkoo_defualt_country');
     $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$updatedcountry.'&filterBy=categoryId:'.$catids.''.$bfilter.'&additionalFields=description,codeEan,merchantName,brandId,brandName,categoryName&pageSize=12&page='.$page.'';
 
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	//var_dump($response);
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function kelkoo_get_all_category_offers_by_id_by_limit($merchantids)
{
    if(isset($_GET['next'])){
		$page=$_GET['next'];
	}else{
		$page=1;
	}
    $updatedkey=get_option('kelkoo_api_key');
    $updatedcountry=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$updatedcountry.'&filterBy=categoryId:'.$merchantids.'&additionalFields=description,codeEan,merchantName,brandId,brandName,categoryName&pageSize=12&page='.$page.'';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function kelkoo_get_all_brand_offers_by_id_by_limit($merchantids)
{
    if(isset($_GET['next'])){
		$page=$_GET['next'];
	}else{
		$page=1;
	}
    $updatedkey=get_option('kelkoo_api_key');
    $updatedcountry=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$updatedcountry.'&filterBy=brandId:'.$merchantids.'&additionalFields=description,codeEan,merchantName,,brandId,brandName,categoryName&pageSize=12&page='.$page.'';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function kelkoo_get_all_merchats_offers_by_id_by_limit($merchantids)
{
    if(isset($_GET['next'])){
		$page=$_GET['next'];
	}else{
		$page=1;
	}
    $updatedkey=get_option('kelkoo_api_key');
    $updatedcountry=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$updatedcountry.'&filterBy=merchantId:'.$merchantids.'&additionalFields=description,codeEan,merchantName,categoryName,brandId,brandName&pageSize=12&page='.$page.'';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function kelkoo_get_all_merchats_offers_by_id($merchantids)
{
    $updatedkey=get_option('kelkoo_api_key');
    $updatedcountry=get_option('kelkoo_defualt_country');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$updatedcountry.'&filterBy=merchantId:'.$merchantids.'&additionalFields=codeEan,merchantName,brandId,brandName,categoryName&pageSize=100&page=5';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function get_data_from_kelkoo_api($country,$keyword)
{
	if(isset($_GET['next'])){
		$page=$_GET['next'];
	}else{
		$page=1;
	}
	
	$params = array(
        'country' => $country,
        'query' => urldecode($keyword),
        'additionalFields' => 'codeEan,merchantName',
        'pageSize' => '12',
        'page'=>$page
    );
    $encodedQueryString = http_build_query($params);
    $baseUrl='https://api.kelkoogroup.net/publisher/shopping/v2/search/offer';
    $url = $baseUrl . '?' . $encodedQueryString;
    $queryString = parse_url($url, PHP_URL_QUERY);
    $decodedQueryString = urldecode($queryString);
    $decodedUrl = str_replace($queryString, $decodedQueryString, $url);
    $url=$decodedUrl;
  
    $country=get_option('kelkoo_defualt_country');
	$updatedkey=get_option('kelkoo_api_key');
    $url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers?country='.$country.'&query='.$keyword.'&additionalFields=description,codeEan,brandId,brandName,merchantName,categoryName&pageSize=12&page='.$page.'';
    
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function kelkoo_get_all_merchats_provide_offers()
{
    $updatedkey=get_option('kelkoo_api_key');
    $updatedcountry=get_option('kelkoo_defualt_country');
    $url ='https://api.kelkoogroup.net/publisher/shopping/v2/feeds/merchants?country='.$updatedcountry.'&format=json&offerMatch=any&merchantMatch=any';
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
		'Accept-encoding: gzip',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	$decodedData = gzdecode($response);
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$decodedData;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
}
function get_merchant_details($merchantid,$country)
{
	$updatedkey=get_option('kelkoo_api_key');
	$country=get_option('kelkoo_defualt_country');
	$url ='https://api.kelkoogroup.net/publisher/shopping/v2/feeds/merchants?country='.$country.'&format=json&offerMatch=any&merchantMatch=any&merchantId='.$merchantid.'';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
	$headers = [
		'Authorization: Bearer '.$updatedkey.'',
		'accept: application/json', 
		'Accept-encoding: gzip',
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	var_dump($response);
	die();
	if ($response === false) {
		$error= 'cURL error: ' . curl_error($ch);
		$data='';
	} else {
		$error=0;
		$data=$response;
	}
	curl_close($ch);
	$result=array(
		'error'=>$error,
		'success'=>$data,
	);
	return $result;
	
}
/*
function custom_rewrite_rules_offers_category() {
    add_rewrite_rule(
        '^offers_category/([^/]*)/([^/]*)/?$',
        'index.php?offers_category=$matches[1]&brand=$matches[2]',
        'top'
    );
}
add_action('init', 'custom_rewrite_rules_offers_category');

// Register the 'brand' query variable
function add_custom_query_vars_offers_category($vars) {
    $vars[] = 'brand';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars_offers_category');

// Redirect to the custom taxonomy template
function custom_template_redirect_offers_category() {
    if (get_query_var('offers_category') && get_query_var('brand')) {
        set_query_var('brand', get_query_var('brand')); // Ensure the brand parameter is set for the template
        // Load the taxonomy template
        include(get_template_directory() . '/taxonomy-offers_category.php');
        exit();
    }
}
add_action('template_redirect', 'custom_template_redirect_offers_category');
*/
function custom_rewrite_rules() {
    add_rewrite_rule('^gtin/([^/]*)/([^/]*)/?', 'index.php?pagename=gtin&cn=$matches[1]&gtin=$matches[2]', 'top');
}
add_action('init', 'custom_rewrite_rules');
function custom_query_vars($vars) {
    $vars[] = 'cn';
    $vars[] = 'gtin';
    return $vars;
}
add_filter('query_vars', 'custom_query_vars');
function custom_post_type_archive_template($template) {
    if (is_post_type_archive('offers')) {
        $new_template = plugin_dir_path(__FILE__) . 'archive-offers.php';
        if ('' !== $new_template) {
            return $new_template;
        }
    }

    return $template;
}
add_filter('template_include', 'custom_post_type_archive_template');

function custom_post_type_merchants_archive_template($template) {
    if (is_post_type_archive('merchants')) {
        $new_template = plugin_dir_path(__FILE__) . 'archive-merchants.php';
        if ('' !== $new_template) {
            return $new_template;
        }
    }

    return $template;
}
add_filter('template_include', 'custom_post_type_merchants_archive_template');

function custom_taxonomy_archive_template( $template ) {
    if ( is_tax( 'offers_category' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'taxonomy-offers_category.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'custom_taxonomy_archive_template' );

function custom_taxonomy_archive_template_brands( $template ) {
    if ( is_tax( 'offers_brands' ) ) {
        $plugin_template = plugin_dir_path( __FILE__ ) . 'taxonomy-offers_brands.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'custom_taxonomy_archive_template_brands' );

function custom_single_template($single_template) {
    global $post;

    if ($post->post_type == 'offers') {
        $template_file = plugin_dir_path(__FILE__) . 'single-offers.php';
        if (file_exists($template_file)) {
            return $template_file;
        }
    }
    return $single_template;
}
add_filter('single_template', 'custom_single_template');

function custom_single_template_merchants($single_template) {
    global $post;

    if ($post->post_type == 'merchants') {
        $template_file = plugin_dir_path(__FILE__) . 'single-merchants.php';
        if (file_exists($template_file)) {
            return $template_file;
        }
    }
    return $single_template;
}
add_filter('single_template', 'custom_single_template_merchants');
function custom_title_tag($title) {
    
    if (isset($_GET['q'])) {
        $post_id = get_the_ID();

        $custom_title = 'Search: '.$_GET['q'];

        $title= $custom_title ? $custom_title : $title;
    }

    return $title;
}

add_filter('pre_get_document_title', 'custom_title_tag');
function custom_terms_pagination_query_vars( $query_vars ) {
    if ( is_tax( 'offers_category' ) && ! is_admin() ) {
        $query_vars['posts_per_page'] = 12;
    }
    return $query_vars;
}
add_filter( 'request', 'custom_terms_pagination_query_vars' );

function enqueue_swiper_slider() {
    wp_enqueue_style('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.css');
    wp_enqueue_script('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);
    wp_enqueue_script('swiper-init',  plugin_dir_url(__FILE__) . 'assets/js/owl-carousel-init.js?tgh', array('swiper'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_slider');
function custom_taxonomy_carousel_shortcode() {
    
    $terms = get_terms(array(
        'taxonomy' => 'offers_category',
        'hide_empty' => true,
        'number'     => 15
    ));

    if (empty($terms) || is_wp_error($terms)) {
        return '<p>No terms found.</p>';
    }

    ob_start();
    ?>
    <div class="swiper-container">
        <div class="swiper-wrapper">
        <?php foreach ($terms as $term) : 
         $term_id=   $term->term_id;
         $term_link = get_term_link($term);
        $kelkoo_attchment_id = get_term_meta( $term_id, 'kelkoo_category_image', true );
         if($kelkoo_attchment_id){
            $attachment_url = wp_get_attachment_image_src( $kelkoo_attchment_id, 'full' );
            $attachment_url = $attachment_url[0];
         }else{
            $attachment_url = get_term_meta( $term->term_id, 'kelkoo_feature_image', true );
         }
        ?>
             <div class="swiper-slide">
                <a href="<?php echo $term_link; ?>">
                <?php if ($attachment_url): ?>
                    <img src="<?php echo $attachment_url; ?>" alt="<?php echo esc_attr($term->name); ?>"/>
                <?php else: ?>
                <img src="/wp-content/uploads/2024/05/1200x900.jpg" alt="<?php echo esc_attr($term->name); ?>"/>
                <?php endif; ?>
                
                </a>
                <h4><?php echo esc_html($term->name); ?></h4>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('taxonomy_carousel_on_top', 'custom_taxonomy_carousel_shortcode');

add_action('wp_ajax_update_selected_brands', 'update_selected_brands');
add_action('wp_ajax_nopriv_update_selected_brands', 'update_selected_brands');

function update_selected_brands() {
    $selectedBrands = $_POST['selectedBrands']; 
    $catids=$_POST['catid']; 
    $allbrands=explode(',',$selectedBrands);
    $response=kelkoo_get_all_category_offers_by_brand_id($allbrands,$catids);
    if($response['error']==0){
			$alloffers= json_decode($response['success']);
		    $currentPage=$alloffers->meta->offers->currentPage;
			$NextPage=$alloffers->meta->offers->nextPage;
		    $allgetoffers=$alloffers->offers;
		    if($allgetoffers){
		    foreach($allgetoffers as $allgetoffers){ 
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
			$allkcategories=explode(',',$categoryname);
			unset($allcatsarray);
			foreach($allkcategories as $key=>$val){
			    $catlink=get_or_create_category_url($val,'offers_category',$categoryid,$brandid);
			    if($catlink !=''){
			        $allcatsarray[]='<a href="'.$catlink.'">'.$val.'</a>';
			    }
			}
			$allkbrands=explode(',',$brandname);
			unset($allbrandsarray);
			foreach($allkbrands as $keyb=>$valb){
			    $blink=get_or_create_brand_url($valb,'offers_brands',$brandid);
			    if($blink !=''){
			        $allbrandsarray[]='<a href="'.$blink.'">'.$valb.'</a>';
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
                     
                     <div class="offer-labels">
                         <span class="category">Brands:<?php echo implode(',',$allbrandsarray); ?></span>
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
	  
		 } 
		        
		    }else{
		        echo "There is no offer matching your criterai right now!";
		    }
	}else{
	    echo "There is no offer matching your criterai right now!";
	}

    wp_die(); // Always include this to terminate the script
}
