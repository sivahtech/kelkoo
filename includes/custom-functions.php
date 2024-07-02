<?php
function delete_all_custom_post_types()
{
    if((isset($_GET['delete_all_gtin'])) && ($_GET['delete_all_gtin']==1)){
        $custom_post_type = 'offers';
        $posts = get_posts(array(
            'post_type' => $custom_post_type,
            'posts_per_page' => -1, // Retrieve all posts
            'fields' => 'ids', // Retrieve only post IDs for performance 
        ));
        
        foreach ($posts as $post_id) {
            wp_delete_post($post_id, true); 
        }
    }
    
}
add_action('init','delete_all_custom_post_types');
function register_offers_post_type() {

    $labels = array(
        'name'                  => _x( 'Offers', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Offer', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Offers', 'text_domain' ),
        'name_admin_bar'        => __( 'Offer', 'text_domain' ),
        'archives'              => __( 'Offer Archives', 'text_domain' ),
        'attributes'            => __( 'Offer Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Offer:', 'text_domain' ),
        'all_items'             => __( 'All Offers', 'text_domain' ),
        'add_new_item'          => __( 'Add New Offer', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Offer', 'text_domain' ),
        'edit_item'             => __( 'Edit Offer', 'text_domain' ),
        'update_item'           => __( 'Update Offer', 'text_domain' ),
        'view_item'             => __( 'View Offer', 'text_domain' ),
        'view_items'            => __( 'View Offers', 'text_domain' ),
        'search_items'          => __( 'Search Offer', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
        'items_list'            => __( 'Offers list', 'text_domain' ),
        'items_list_navigation' => __( 'Offers list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter offers list', 'text_domain' ),
    );

    $args = array(
        'label'                 => __( 'Offer', 'text_domain' ),
        'description'           => __( 'Custom Post Type for Offers', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );

    register_post_type( 'offers', $args );
}

add_action( 'init', 'register_offers_post_type', 0 );
function insert_a_parent_post($country)
{
    
     $post_type = 'offers';
     $content='';
    $new_post = array(
        'post_title'   => $country,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => $post_type,
        
    );
  
    $post_id = wp_insert_post($new_post);
    return $post_id;
}
function allow_additional_file_types($mime_types) {
    $mime_types['php'] = 'application/x-php'; // Allow PHP files
    $mime_types['html'] = 'text/html'; // Allow HTML files
    $mime_types['htm'] = 'text/html'; // Allow HTM files
    $mime_types['js'] = 'text/javascript'; // Allow JavaScript files
    // Add more file types as needed
    return $mime_types;
}
add_filter('upload_mimes', 'allow_additional_file_types');
function upload_image_from_url_and_set_featured_image($image_url, $post_id) {
    // Check if the URL is valid
    if (filter_var($image_url, FILTER_VALIDATE_URL) === false) {
        return new WP_Error('invalid_url', 'Invalid URL');
    }
    
    update_post_meta( $post_id, 'feature_image', $image_url  );
    
    return $image_url;

    // Download image from URL
    $image_data = wp_remote_get($image_url);
    $image_body = wp_remote_retrieve_body($image_data);

    // Check if image download was successful
    if (is_wp_error($image_data) || empty($image_body)) {
        return new WP_Error('image_download_failed', 'Failed to download image');
    }

   // echo basename($image_url);
    $filename = wp_unique_filename(wp_upload_dir()['path'], 'featured_image_'.$post_id.'.jpg');

    // Upload image to media library
    $upload = wp_upload_bits($filename, null, $image_body);
  
    if (!$upload['error']) {
        $file_path = $upload['file'];
        $file_name = basename($file_path);

        // Set uploaded image as post thumbnail (featured image)
        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
        if (!is_wp_error($attach_id)) {
            // Set post thumbnail (featured image)
            set_post_thumbnail($post_id, $attach_id);
            return $attach_id; // Return attachment ID if successful
        } else {
            return $attach_id; // Return error if setting post thumbnail failed
        }
    } else {
        return new WP_Error('upload_failed', 'Failed to upload image');
    }
}
function insert_custom_post_offers_cat($title, $content,$country,$eancode,$imgurl,$price,$gourl,$seemore,$currency,$merchantids,$offerids,$mainmerchatids)
{
    global $wpdb;
    if($eancode !=''){
        $post_type = 'offers';
        $post_status = 'publish';
        $post_author = 1;
        $post_title=$title;
        $postdate=date('Y-m-d H:i:s');
        $args = array(
            'post_type' => $post_type, 
            'meta_key' => 'code_ean',
            'meta_value' => $eancode
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) {
             while ( $query->have_posts() ) {
            $query->the_post();
           //return "Ean Already Exist";
           $getid=get_the_ID();
           $offers_category_terms = array($categoryname);
            /*wp_set_object_terms($getid, $offers_category_terms, 'offers_category');
            foreach ($offers_category_terms as $term_name) {
                $term = get_term_by('name', $term_name, 'offers_category');
                if ($term && !is_wp_error($term)) {
                    add_term_meta($term->term_id, 'kelkoo_category_id', $categoryid, true);
                }
            }*/
            
            return get_the_permalink($getid);
            }
        }else{

         $url_friendly_title = sanitize_title($title);   
         if($content !=''){
             $postcontent=$content;
         }else{
             $postcontent=$title;
         }
         
        $new_post = array(
                'post_title'   => $title,
                'post_content' => $postcontent,
                'post_status'  => 'publish',
                'post_type'    => 'offers',
                'post_author'    => $post_author,
                'post_name'    => $url_friendly_title,
                
            );
            
            $post_id = wp_insert_post($new_post);
            if($imgurl !=''){
               // echo 'here';
                // $attchmentid = upload_image_from_url_and_set_featured_image($imgurl, $post_id);
                $image_url = upload_image_from_url_and_set_featured_image($imgurl, $post_id);
                
            
            }
            update_post_meta($post_id,'code_ean',$eancode);
            update_post_meta($post_id,'offers_country',$country);
            update_post_meta($post_id,'offers_ean_code',$eancode);
            update_post_meta($post_id,'cusotm_img_url',$imgurl);
            update_post_meta($post_id,'price',$price);
            update_post_meta($post_id,'custom_go_url',$gourl);
            update_post_meta($post_id,'see_button_text',$seemore);
            update_post_meta($post_id,'price_cuurency',$currency);
            update_post_meta($post_id,'merchants',$merchantids);
            update_post_meta($post_id,'offers_ids',$offerids);
            update_post_meta($post_id,'merchants_ids',$mainmerchatids);
            if (!is_wp_error($post_id)) {
                return get_the_permalink($post_id); // Return the new post ID
            } else {
                return $post_id->get_error_message(); // Return the error message
            }
        }
        wp_reset_postdata();
    }
}
function insert_custom_post_offers_amazon($ProductInfo,$Images,$features,$cuoffoffers,$discount,$SavingBasis,$DetailPageURL,$ean,$brand,$country,$categoryArray,$asin)
{
    global $wpdb;
    if($ean !=''){
        $post_type = 'offers';
        $post_status = 'publish';
        $post_author = 1;
        $post_title=$ProductInfo;
        $postdate=date('Y-m-d H:i:s');
        $args = array(
            'post_type' => $post_type, 
            'meta_key' => 'code_ean',
            'meta_value' => $ean
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) {
             while ( $query->have_posts() ) {
            $query->the_post();
           $getid=get_the_ID();
           if($brand !='')
            {
                $offersbrands=explode(',',$brand);
                wp_set_object_terms($getid, $offersbrands, 'offers_brands');
                foreach ($offersbrands as $term_name) {
                    $term = get_term_by('name', $term_name, 'offers_brands');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'amazon_brand_id', $brand, true);
                        $catlink= get_category_link($term->term_id);
                        $trmsliks[]= '<a href="'.$catlink.'">'.$term_name.'</a>';  
                    }
                }
            }
            if(isset($categoryArray)){
                $indexCategoryArray = array_values($categoryArray);
               
                wp_set_object_terms($getid, $indexCategoryArray , 'offers_category');
                foreach ($categoryArray as $key =>  $term_name) {
                    
                    $term = get_term_by('name', $term_name, 'offers_category');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'amazon_category_id', $key, true);
                       $catlink= get_category_link($term->term_id);
                       $category[]= '<a href="'.$catlink.'">'.$term_name.'</a>';
                    }
                }
            }else{
                $category = [];
            }
            
            update_post_meta($getid,'code_asin',$asin);
            
            $returndata['post_id']=$getid;
            $returndata['category']=$category;
            $returndata['cats']=$trmsliks;
            $returndata['postlink']=get_the_permalink($getid);
            return $returndata;
            }
        }else{
            $url_friendly_title = sanitize_title($ProductInfo);   
             if($features !=''){
                 $postcontent = implode("\n", $features);
             }else{
                 $postcontent=$ProductInfo;
             }
             $new_post = array(
                'post_title'   => $ProductInfo,
                'post_content' => $postcontent,
                'post_status'  => 'publish',
                'post_type'    => 'offers',
                'post_author'    => $post_author,
                'post_name'    => $url_friendly_title,
                
            );
            
            $post_id = wp_insert_post($new_post);
            if($Images !=''){
               $attchmentid = upload_image_from_url_and_set_featured_image($Images, $post_id);
            }
            if($brand !='')
            {
                $offersbrands=explode(',',$brand);
                wp_set_object_terms($post_id, $offersbrands, 'offers_brands');
                foreach ($offersbrands as $term_name) {
                    $term = get_term_by('name', $term_name, 'offers_brands');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'amazon_brand_id', $brand, true);
                        $catlink= get_category_link($term->term_id);
                        $trmsliks[]= '<a href="'.$catlink.'">'.$term_name.'</a>';   
                    }
                }
            }
            
            if(!empty($categoryArray)){
                $indexCategoryArray = array_values($categoryArray);
               
                wp_set_object_terms($post_id, $indexCategoryArray , 'offers_category');
                foreach ($categoryArray as $key =>  $term_name) {
                    
                    $term = get_term_by('name', $term_name, 'offers_category');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'amazon_category_id', $key, true);
                        $catlink= get_category_link($term->term_id);
                        $category[]= '<a href="'.$catlink.'">'.$term_name.'</a>';
                    }
                }
            }else{
                $category = [];
            }
            
            
            update_post_meta($post_id,'code_asin',$asin);
            update_post_meta($post_id,'code_ean',$ean);
            update_post_meta($post_id,'offers_country',$country);
            update_post_meta($post_id,'offers_ean_code',$ean);
            update_post_meta($post_id,'cusotm_img_url',$Images);
            update_post_meta($post_id,'price',$SavingBasis);
            update_post_meta($post_id,'cutoffprice',$cuoffoffers);
            update_post_meta($post_id,'discount',$discount);
            update_post_meta($post_id,'custom_go_url',$DetailPageURL);
            update_post_meta($post_id,'kelkoo_category_id',$categoryId);
            update_post_meta($post_id,'amazon_category_id',$categoryId);
           
            
            if (!is_wp_error($post_id)) {
                
                $returndata['post_id']=$post_id;
                $returndata['category']=$category;
                $returndata['cats']=$trmsliks;
                $returndata['postlink']=get_the_permalink($post_id);
            return $returndata;// Return the new post ID
            } else {
                return $post_id->get_error_message(); // Return the error message
            }
        }
    }
}
function insert_custom_post_offers($title, $content,$country,$eancode,$imgurl,$price,$gourl,$seemore,$currency,$merchantids,$offerids,$mainmerchatids,$categoryname,$categoryid,$brandid="",$brandname="") 
{
    global $wpdb;
    if($eancode !=''){
        $post_type = 'offers';
        $post_status = 'publish';
        $post_author = 1;
        $post_title=$title;
        $postdate=date('Y-m-d H:i:s');
        $args = array(
            'post_type' => $post_type, 
            'meta_key' => 'code_ean',
            'meta_value' => $eancode
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) {
             while ( $query->have_posts() ) {
            $query->the_post();
           //return "Ean Already Exist";
           $getid=get_the_ID();
           if($brandid !='')
            {
                $offersbrands=explode(',',$brandname);
                wp_set_object_terms($getid, $offersbrands, 'offers_brands');
                foreach ($offersbrands as $term_name) {
                    $term = get_term_by('name', $term_name, 'offers_brands');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'kelkoo_brand_id', $brandid, true);
                        //add_term_meta($term->term_id, 'kelkoo_brand_image', $attchmentid, true);
                    }
                }
            }
             if($categoryid !=''){
                $offers_category_terms = explode(',',$categoryname);
               
                wp_set_object_terms($getid, $offers_category_terms, 'offers_category');
                foreach ($offers_category_terms as $term_name) {
                    $term = get_term_by('name', $term_name, 'offers_category');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'kelkoo_category_id', $categoryid, true);
                        // add_term_meta($term->term_id, 'kelkoo_category_image', $attchmentid, true);
                    }
                }
            }
            return get_the_permalink($getid);
            }
        }else{

         $url_friendly_title = sanitize_title($title);   
         if($content !=''){
             $postcontent=$content;
         }else{
             $postcontent=$title;
         }
         
        $new_post = array(
                'post_title'   => $title,
                'post_content' => $postcontent,
                'post_status'  => 'publish',
                'post_type'    => 'offers',
                'post_author'    => $post_author,
                'post_name'    => $url_friendly_title,
                
            );
            
            $post_id = wp_insert_post($new_post);
            if($imgurl !=''){
               // echo 'here';
                $image_link = upload_image_from_url_and_set_featured_image($imgurl, $post_id);
                
            
            }
            
            if($brandid !='')
            {
                $offersbrands=explode(',',$brandname);
                wp_set_object_terms($post_id, $offersbrands, 'offers_brands');
                foreach ($offersbrands as $term_name) {
                    $term = get_term_by('name', $term_name, 'offers_brands');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'kelkoo_brand_id', $brandid, true);
                        //add_term_meta($term->term_id, 'kelkoo_brand_image', $attchmentid, true);
                    }
                }
            }
            if($categoryid !=''){
                $offers_category_terms = explode(',',$categoryname);
               
                wp_set_object_terms($post_id, $offers_category_terms, 'offers_category');
                foreach ($offers_category_terms as $term_name) {
                    $term = get_term_by('name', $term_name, 'offers_category');
                    if ($term && !is_wp_error($term)) {
                        add_term_meta($term->term_id, 'kelkoo_category_id', $categoryid, true);
                        // add_term_meta($term->term_id, 'kelkoo_category_image', $attchmentid, true);
                        add_term_meta($term->term_id, 'kelkoo_feature_image', $image_link, true);
                    }
                }
            }
            
            update_post_meta($post_id,'code_ean',$eancode);
            update_post_meta($post_id,'offers_country',$country);
            update_post_meta($post_id,'offers_ean_code',$eancode);
            update_post_meta($post_id,'cusotm_img_url',$imgurl);
            update_post_meta($post_id,'price',$price);
            update_post_meta($post_id,'custom_go_url',$gourl);
            update_post_meta($post_id,'see_button_text',$seemore);
            update_post_meta($post_id,'price_cuurency',$currency);
            update_post_meta($post_id,'merchants',$merchantids);
            update_post_meta($post_id,'offers_ids',$offerids);
            update_post_meta($post_id,'merchants_ids',$mainmerchatids);
            if (!is_wp_error($post_id)) {
                return get_the_permalink($post_id); // Return the new post ID
            } else {
                return $post_id->get_error_message(); // Return the error message
            }
        }
        wp_reset_postdata();
    }
    
}
add_filter( 'cron_schedules', 'isa_add_every_five_minutes' );
function isa_add_every_five_minutes( $schedules ) {
    $schedules['every_five_minutes'] = array(
            'interval'  => 60 * 5,
            'display'   => __( 'Every 5 Minutes Custom', 'textdomain' )
    );
    return $schedules;
}
if ( ! wp_next_scheduled( 'isa_add_every_five_minutes' ) ) {
    wp_schedule_event( time(), 'every_five_minutes', 'isa_add_every_five_minutes' );
}
/*add_action( 'isa_add_every_five_minutes', 'every_five_minutes_event_func' );
function every_five_minutes_event_func()
{
	
	$post_type = 'offers';
	$args = array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
	);
	$query2 = new WP_Query($args);
	$post_count = $query2->post_count;
	if ($post_count > 10000) {
		$args = array(
			'post_type' => $post_type,
			'posts_per_page' => 1000,
		);
		$query = new WP_Query($args);
		 if ($query->have_posts()) {
			 
			while ($query->have_posts()) {
				$query->the_post();
				wp_delete_post(get_the_ID(), true); // True to delete permanently
			}
			wp_reset_postdata();
			echo 'All posts deleted successfully.';
		}
		
		
	}

}

add_action( 'isa_add_every_five_minutes', 'every_five_minutes_event_func_delete_not_exist' );
function every_five_minutes_event_func_delete_not_exist() 
{
    global $wpdb;
    $args = array(
        'post_type'      => 'offers', 
        'posts_per_page' => 200, 
        'meta_query'     => array(
            array(
                'key'     => 'check_offers_exists', 
                'compare' => 'NOT EXISTS', 
            ),
        ),
    );

$query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $postids=get_the_ID();
            update_post_meta($postids,'check_offers_exists','checked');
           $offers_country=get_post_meta($postids,'offers_country',true);
            $offers_ids=get_post_meta($postids,'offers_ids',true);
           
            if($offers_ids){
                $response=check_if_offers_stillexists($offers_country,$offers_ids);
                
                if($response['error']==0){
        			$alloffers= json_decode($response['success']);
        			if($alloffers->offers){
            			$allgetoffers=$alloffers->offers;
            			if(empty($allgetoffers)){
            			    $result = wp_delete_post($postids, true);
            			}
        			}
        		}
            }
        }
        wp_reset_postdata(); 
    }else{
        $meta_key = 'check_offers_exists';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta WHERE meta_key = %s",
                $meta_key
            )
        );
    }
}
*/
add_filter('pre_get_document_title', 'change_404_title', 50);

function change_404_title($title) {
    if (isset($_GET['q'])) {
        return 'Search: '.$_GET['q'].'';
    }
    return $title;
}
add_action('init','delete_all_custom_post_types');

add_action( 'init', 'register_offers_post_type_merchants', 0 );
function register_offers_post_type_merchants() {

    $labels = array(
        'name'                  => _x( 'Merchants', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Merchant', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'Merchants', 'text_domain' ),
        'name_admin_bar'        => __( 'Merchant', 'text_domain' ),
        'archives'              => __( 'Merchant Archives', 'text_domain' ),
        'attributes'            => __( 'Merchant Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Merchant:', 'text_domain' ),
        'all_items'             => __( 'All Merchants', 'text_domain' ),
        'add_new_item'          => __( 'Add New Merchant', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Merchant', 'text_domain' ),
        'edit_item'             => __( 'Edit Merchant', 'text_domain' ),
        'update_item'           => __( 'Update Merchant', 'text_domain' ),
        'view_item'             => __( 'View Merchant', 'text_domain' ),
        'view_items'            => __( 'View Merchants', 'text_domain' ),
        'search_items'          => __( 'Search Merchant', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
        'items_list'            => __( 'Merchant list', 'text_domain' ),
        'items_list_navigation' => __( 'Merchant list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter offers list', 'text_domain' ),
    );

    $args = array(
        'label'                 => __( 'Merchant', 'text_domain' ),
        'description'           => __( 'Custom Post Type for Merchant', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );

    register_post_type( 'merchants', $args );
}
function insert_custom_post_type_merchants($name,$id,$url,$summary,$logoUrl)
{
   
    $post_author = 1;
    $post_title=$name;
    $existing_post = get_page_by_title($post_title, OBJECT, 'merchants');
    if ($existing_post) {
       return $existing_post->ID;
    }else{
    $url_friendly_title = sanitize_title($name);   
    $new_post = array(
            'post_title'   => $name,
            'post_content' => $name,
            'post_status'  => 'publish',
            'post_type'    => 'merchants',
            'post_author'    => $post_author,
            'post_name'    => $url_friendly_title,
            
        );
        
        $post_id = wp_insert_post($new_post);
       
        update_post_meta($post_id,'merchantids',$id);
        update_post_meta($post_id,'logourl',$logoUrl);
        update_post_meta($post_id,'merchaturl',$url);
        update_post_meta($post_id,'merchatsummary',$summary);
        if (!is_wp_error($post_id)) {
            return get_the_permalink($post_id); // Return the new post ID
        } else {
            return $post_id->get_error_message(); // Return the error message
        }
    }
}

function my_custom_function_to_check_merchants() 
{
    $response=kelkoo_get_all_merchats_provide_offers();
    
    if($response['error']==0){
		$alloffers= json_decode($response['success']);
	
		foreach($alloffers as $alloffers){
		    $name=$alloffers->name;
		    $id=$alloffers->id;
		    $url=$alloffers->url;
		    $summary=$alloffers->summary;
		    $logoUrl=$alloffers->logoUrl;
		    $merchantids=insert_custom_post_type_merchants($name,$id,$url,$summary,$logoUrl);
		}
	}
   
}

/*
if (!wp_next_scheduled('my_custom_event')) {
    wp_schedule_event(time(), 'daily', 'my_custom_event');
}
add_action('my_custom_event', 'my_custom_function_to_check_merchants');
function add_custom_cron_intervals($schedules) {
    $schedules['every_three_minutes'] = array(
        'interval' => 180, 
        'display'  => __('Every 3 Minutes'),
    );
    return $schedules;
}

add_filter('cron_schedules', 'add_custom_cron_intervals');
if (!wp_next_scheduled('my_custom_event_merchants_offers')) {
    wp_schedule_event(time(), 'every_three_minutes', 'my_custom_event_merchants_offers');
}
add_action('my_custom_event_merchants_offers', 'my_custom_function_merchant_offers');
function my_custom_function_merchant_offers()
{
    $country=get_option('kelkoo_defualt_country');
    $buttontext=get_option('keloo_view_offer');
    $args = array(
        'post_type'      => 'merchants', 
        'posts_per_page' => 1, 
        'meta_query'     => array(
            array(
                'key'     => 'check_merchant_check_new_old', 
                'compare' => 'NOT EXISTS', 
            ),
        ),
    );

$query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $postids=get_the_ID();
            update_post_meta($postids,'check_merchant_check_new_old','checked');
            $merchantids=get_post_meta($postids,'merchantids',true);
            $response=kelkoo_get_all_merchats_offers_by_id($merchantids);
            if($response['error']==0){
                $alloffers= json_decode($response['success']);
                $allgetoffers=$alloffers->offers;
                foreach($allgetoffers as $allgetoffers){
                    $offerids=$allgetoffers->offerId; 
                    $mainmerchatids=$allgetoffers->merchant->id;
        			$merchantids=$allgetoffers->merchant->name;
        			$codeean=$allgetoffers->code->ean;
        			$imgurl=$allgetoffers->images['0']->url;
        			$price=$allgetoffers->price;
        			$gourl=$allgetoffers->goUrl;
                    $desc=$allgetoffers->description;
                    $currency=$allgetoffers->currency;
                    $seemore=$buttontext;
                    $offersids=insert_custom_post_offers($allgetoffers->title,$desc,$country,$codeean,$imgurl,$price,$gourl,$seemore,$currency,$merchantids,$offerids,$mainmerchatids);
                }
            }
            
        }
        wp_reset_postdata(); 
    }else{
        $meta_key = 'check_merchant_check';
       /* $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta WHERE meta_key = %s",
                $meta_key
            )
        );
    }
}*/
/*
function exclude_post_type_from_sitemap($excluded_post_types) {
    $excluded_post_types[] = 'merchants'; 
    return $excluded_post_types;
}
add_filter('wpseo_exclude_from_sitemap_by_post_type', 'exclude_post_type_from_sitemap');

function exclude_sitemap_from_index($excluded_sitemaps) {
    $excluded_sitemaps[] = site_url().'/merchants-sitemap.xml';
    return $excluded_sitemaps;
}
add_filter('wpseo_sitemap_index', 'exclude_sitemap_from_index');
*/

function get_or_create_brand_url($catname="",$tax="",$brandid="") {
   if($catname !=''){
    $category_name = sanitize_text_field($catname);


    $category = get_term_by('name', $category_name, $tax);

    if ($category) {
        return get_category_link($category->term_id);
    } else {
        $new_category = wp_insert_term($category_name, $tax);
        if (is_wp_error($new_category)) {
            return 'Error creating category: ' . $new_category->get_error_message();
        } else {
            add_term_meta($new_category['term_id'], 'kelkoo_brand_id', $brandid, true);
            
            return get_category_link($new_category['term_id']);
        }
    }
   }else{
       return "";
   }
}

function get_or_create_brand_url_amazone($catname="",$tax="",$brandName="") {
   if($catname !=''){
    $category_name = sanitize_text_field($catname);


    $category = get_term_by('name', $category_name, $tax);

    if ($category) {
        return get_category_link($category->term_id);
    } else {
        $new_category = wp_insert_term($category_name, $tax);
        if (is_wp_error($new_category)) {
            return 'Error creating category: ' . $new_category->get_error_message();
        } else {
            add_term_meta($new_category['term_id'], 'amazone_brand_id', $brandName, true);
            
            return get_category_link($new_category['term_id']);
        }
    }
   }else{
       return "";
   }
}

function get_or_create_category_url($catname="",$tax="",$categoryid="",$brandid="") {
   
    if($catname !=''){
    $category_name = sanitize_text_field($catname);


    $category = get_term_by('name', $category_name, $tax);
    
    if($brandid == ''){
        $brandid = $category->term_id;
    }

    if ($category) {
        $termbrandsids = get_term_meta( $category->term_id, 'all_linked_brands', true);
        if($termbrandsids){
            //echo 'here';
            $allbrands=json_decode($termbrandsids);
            if (!in_array($brandid, $allbrands)) {
                array_push($allbrands,$brandid);
                //print_r($allbrands);
                $allbrandslist=json_encode($allbrands);
               update_term_meta($category->term_id, 'all_linked_brands', $allbrandslist);
            }
        }else{
           
            $allbrands[]=$brandid;
           // print_r($allbrands);
             $allbrandslist=json_encode($allbrands);
            update_term_meta($category->term_id, 'all_linked_brands', $allbrandslist);
        }
    
        return get_category_link($category->term_id);
    } else {
        if($imgurl !=''){
               // echo 'here';
                // $attchmentid = upload_image_from_url_and_set_featured_image($imgurl, $post_id);
                $image_link = upload_image_from_url_and_set_featured_image($imgurl, $post_id);
                
            }
        $new_category = wp_insert_term($category_name, $tax);
        if (is_wp_error($new_category)) {
            return 'Error creating category: ' . $new_category->get_error_message();
        } else {
            add_term_meta($new_category['term_id'], 'kelkoo_category_id', $categoryid, true);
            // add_term_meta($new_category['term_id'], 'kelkoo_category_image', $attchmentid, true);
            add_term_meta($new_category['term_id'], 'kelkoo_feature_image', $image_link, true);
             $termbrandsids = get_term_meta( $category->term_id, 'all_linked_brands', true);
            if($termbrandsids){
                $allbrands=json_decode($termbrandsids);
                if (!in_array($brandid, $allbrands)) {
                    array_push($allbrands,$brandid);
                    $allbrandslist=json_encode($allbrands);
                    update_term_meta($new_category['term_id'], 'all_linked_brands', $allbrandslist);
                }
            }else{
                $allbrands[]=$brandid;
                $allbrandslist=json_encode($allbrands);
                update_term_meta($new_category['term_id'], 'all_linked_brands', $allbrandslist);
            }
            
            return get_category_link($new_category['term_id']);
        }
    }
    }else{
       return "";
   }
}

function get_or_create_category_url_amazone($catname="",$tax="",$brandName) {  
   
    if($catname !=''){
    $category_name = sanitize_text_field($catname);

    $category = get_term_by('name', $category_name, $tax);
   
    if ($category) {
        $termbrandsids = get_term_meta( $category->term_id, 'all_linked_brands_amazone', true);
        if($termbrandsids){
            //echo 'here';
            $allbrands=json_decode($termbrandsids);
            if($allbrands && $brandName){
             array_push($allbrands,$brandName);
            }
            //print_r($allbrands);
            $allbrandslist=json_encode($allbrands);
           update_term_meta($category->term_id, 'all_linked_brands_amazone', $allbrandslist);
        }else{
           
            $allbrands[]=$brandName;
           // print_r($allbrands);
             $allbrandslist=json_encode($allbrands);
            update_term_meta($category->term_id, 'all_linked_brands_amazone', $allbrandslist);
        }
    
        return get_category_link($category->term_id);
    } else {
        // if($imgurl !=''){
        //       // echo 'here';
        //         $attchmentid = upload_image_from_url_and_set_featured_image($imgurl, $post_id);
                
        //     }
        $new_category = wp_insert_term($category_name, $tax);
        if (is_wp_error($new_category)) {
            return 'Error creating category: ' . $new_category->get_error_message();
        } else {
             add_term_meta($new_category['term_id'], 'amazone_category_id', $category_name, true);
             $termbrandsids = get_term_meta( $category->term_id, 'all_linked_brands_amazone', true);
            if($termbrandsids){
                $allbrands=json_decode($termbrandsids);
                if($allbrands && $brandName){
                array_push($allbrands,$brandName);
                }
                $allbrandslist=json_encode($allbrands);
                update_term_meta($new_category['term_id'], 'all_linked_brands_amazone', $allbrandslist);
            }else{
                $allbrands[]=$brandName;
                $allbrandslist=json_encode($allbrands);
                update_term_meta($new_category['term_id'], 'all_linked_brands_amazone', $allbrandslist);
            }
            
            return get_category_link($new_category['term_id']);
        }
    }
    }else{
       return "";
   }
}

function get_term_name_by_meta_val($metaval)
{
    $meta_key = 'kelkoo_brand_id';
    $meta_value = 'desired_value'; // Replace with the value you're looking for

    $args = array(
        'taxonomy'   => 'offers_brands', // Replace with your taxonomy
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key'     => $meta_key,
                'value'   => $metaval,
                'compare' => '='
            )
        )
    );
    
    $terms = get_terms($args);
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            
            $termname=$term->name;
        }
    } else {
        $termname="";
    }
    
    return $termname; 
}

function get_term_name_by_meta_val_amazone($metaval)
{
    
//   global $wpdb;

//     // Prepare the SQL query to fetch the term name by term ID
//     $query = $wpdb->prepare(
//         "SELECT *
//         FROM {$wpdb->terms}
//         WHERE term_id = %d
//         LIMIT 1",
//         $metaval
//     );

//     // Execute the query
//     $term = $wpdb->get_row($query);
//     // Check if a term name was found
//     if ($term) {
//         return $term;
//     } else {
//         return ""; // Return an empty string if no term found
//     }


    $meta_key = 'amazone_brand_id';
    $meta_value = 'desired_value'; // Replace with the value you're looking for

    $args = array(
        'taxonomy'   => 'offers_brands', // Replace with your taxonomy
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key'     => $meta_key,
                'value'   => $metaval,
                'compare' => '='
            )
        )
    );
    
    $terms = get_terms($args);
    
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            
            $termname=$term->name;
        }
    } else {
        $termname="";
    }
    
    return $termname; 
}