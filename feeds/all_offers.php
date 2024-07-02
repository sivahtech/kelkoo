<?php
// Add admin menu
add_action('admin_menu', 'ecp_add_admin_menu');

function ecp_add_admin_menu()
{
    add_menu_page(
        'feeds exporter',            // Page title
        'Feeds exporter',            // Menu title
        'manage_options',            // Capability
        'feeds',                     // Menu slug
        'all_offers'             // Function to display the page
    );

}


// For logged-in users
add_action('wp_ajax_all_offers', 'all_offers');
add_action('wp_ajax_offers_by_brands', 'offers_by_brands');
add_action('wp_ajax_offers_by_category', 'offers_by_category');



function all_offers(){
    
    if(isset($_POST['type']) || isset($_POST['taxonomy'])){
        // Get pagination parameters from DataTables
        $limit = intval($_POST['length']);
        $offset = intval($_POST['start']);
    
        // Get order parameters from DataTables
        $order_column_index = intval($_POST['order'][0]['column']);
        $order_column = $_POST['columns'][$order_column_index]['data'];
        $order_dir = sanitize_text_field($_POST['order'][0]['dir']);
    
        // Get search parameter from DataTables
        $search_value = sanitize_text_field($_POST['search']['value']);
    
        if(!empty($_POST['type'])){
            // Set up WP_Query arguments
            $args = array(
                'post_type'      => 'offers',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'offset'         => $offset,
                'orderby'        => $order_column,
                'order'          => $order_dir,
                's'              => $search_value
            );
            
            // Get total records without pagination and search filters
            $total_records = new WP_Query(array(
                'post_type'      => 'offers',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids'
            ));
            $total_records_count = $total_records->found_posts;
        
            // Get total records with search filter but without pagination
            $filtered_records = new WP_Query(array(
                'post_type'      => 'offers',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                's'              => $search_value,
                'fields'         => 'ids'
            ));
            $filtered_records_count = $filtered_records->found_posts;
        }
        
        if(!empty($_POST['taxonomy'])){
            
            $type = $_POST['taxonomy'];
             // Set up WP_Query arguments
            $args = array(
                'post_type'      => 'offers',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'offset'         => $offset,
                'orderby'        => $order_column,
                'order'          => $order_dir,
                's'              => $search_value,
                'tax_query' => array(
                    array(
                    'taxonomy' => $type,
                    'field' => 'term_id',
                    'terms' => $_POST['searchKey']
                     )
                  )
            );
            
            // Get total records without pagination and search filters
            $total_records = new WP_Query(array(
                'post_type'      => 'offers',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query' => array(
                    array(
                   'taxonomy' => $type,
                    'field' => 'term_id',
                    'terms' => $_POST['searchKey']
                     )
                  )
            ));
            $total_records_count = $total_records->found_posts;
        
            // Get total records with search filter but without pagination
            $filtered_records = new WP_Query(array(
                'post_type'      => 'offers',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                's'              => $search_value,
                'fields'         => 'ids',
                'tax_query' => array(
                    array(
                    'taxonomy' => $type,
                    'field' => 'term_id',
                    'terms' => $_POST['searchKey']
                     )
                  )
            ));
            $filtered_records_count = $filtered_records->found_posts;
        }
    
        $query = new WP_Query($args);
        
        $data = array();
    
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $meta_data = get_post_meta($post_id);
    
                // Fetch post meta data
                $meta_data = get_post_meta($post_id);
                $image_url = $meta_data['feature_image'][0];
                if(empty($image_url)){
                    $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                }
                $price = ($meta_data['price'][0]) ? $meta_data['price'][0] : $meta_data['cutoffprice'][0];
                $sale_price = ($meta_data['cutoffprice'][0]) ? $meta_data['cutoffprice'][0] : $meta_data['price'][0];
                
                // Fetch categories for each post
                $categories = get_the_terms($post_id, 'offers_category');
                $category = '';
                if ($categories && !is_wp_error($categories)) {
                    // Extract names into an array
                    $category_names_array = wp_list_pluck($categories, 'name');
                    
                    // Join names with comma
                    $category = implode(', ', $category_names_array);
                }
                
                $title = get_the_title();
                    
                $data[] = array(
                    'id'               => $post_id,
                    'item_title'       => $title,
                    'final_url'        => get_the_permalink(),
                    'image_url'        => $image_url,
                    'item_subtitle'    => $title,
                    'item_description' => get_the_content(),
                    'item_category'    => $category,
                    'price'            => $price,
                    'sale_price'       => $sale_price,
                    'contextual_keywords' => $title,
                    'item_address'     => ''
                );
            }
            wp_reset_postdata();
        }
    
      
        // Prepare the response
        $response = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $total_records_count,
            "recordsFiltered" => $filtered_records_count,
            "data" => $data
        );
    
        wp_send_json($response);
    
    }
    
    if((isset($_GET['mode']) && $_GET['mode'] == 'all') && (isset($_GET['format']) && $_GET['format'] == 'first')){
        get_all_offers_feed($_GET['format']);
    }
    
    if((isset($_GET['mode']) && $_GET['mode'] == 'all') && (isset($_GET['format']) && $_GET['format'] == 'second')){
        get_all_offers_feed($_GET['format']);
    }
    
     feeds_exporter('all_offers');
        exit();
}

function get_all_offers_feed($format) {
    if (!empty($_GET['taxonomy']) && !empty($_GET['key'])) {
        $posts_per_page = 500; // Number of posts to fetch per page

        // Determine the file path and URL based on the format and query parameters
        $filepath = get_csv_filepath($format);
        $csv_url = get_csv_url($format);
        
        if(file_exists($filepath)){
            $fp = file($filepath);
            $offset =  count($fp)-1;
            $paged = floor($offset / $posts_per_page) + 1;
            $fp = fopen($filepath, 'a');
            
        }else{
            $fp = fopen($filepath, 'w');

            // Output the column headings
            $headers = $format == 'first' ? array('Page URL','Custom label') : array('ID','Item title','Final URL','Image URL','Item subtitle','Item description','Item category','Price','Sale price','Contextual keywords','Item address');
            fputcsv($fp, $headers);
            $paged = 1; // Start with the first page
        }
     
        while (true) {
            // Set up WP_Query arguments
            $args = array(
                'post_type'      => 'offers',
                'post_status'    => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged
            );
    
            // Modify query if taxonomy and key are provided
            if (!empty($_GET['taxonomy']) && !empty($_GET['key'])) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => $_GET['taxonomy'],
                        'field'    => 'term_id',
                        'terms'    => $_GET['key']
                    )
                );
            }
    
            $query = new WP_Query($args);
    
            if (!$query->have_posts()) {
                break; // Break the loop if no more posts are found
            }
    
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $link = get_the_permalink();
    
                if ($format == 'first') {
                    fputcsv($fp, array(urldecode($link)));
                } elseif ($format == 'second') {
                    $meta_data = get_post_meta($post_id);
                    $image_url = $meta_data['feature_image'][0];
                    if(empty($image_url)){
                        $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                    }
                    $price = isset($meta_data['price'][0]) ? $meta_data['price'][0] : (isset($meta_data['cutoffprice'][0]) ? $meta_data['cutoffprice'][0] : '');
                    $sale_price = isset($meta_data['cutoffprice'][0]) ? $meta_data['cutoffprice'][0] : (isset($meta_data['price'][0]) ? $meta_data['price'][0] : '');
    
                    // Fetch categories for each post
                    $categories = get_the_terms($post_id, 'offers_category');
                    $category = '';
                    if ($categories && !is_wp_error($categories)) {
                        // Extract names into an array and join names with comma
                        $category = implode(', ', wp_list_pluck($categories, 'name'));
                    }
    
                    $title = urldecode(get_the_title());
    
                    $data_row = array(
                        $title,
                        $title,
                        get_the_permalink(),
                        $image_url,
                        $title,
                        get_the_content(),
                        $category,
                        $price,
                        $sale_price,
                        $title,
                        ''
                    );
    
                    fputcsv($fp, $data_row);
                }
            }
    
            wp_reset_postdata();
            $paged++; // Increment the page number
        }
    
        fclose($fp); 
    
        wp_redirect($csv_url);
        exit;
    }else{
        // these file have high amount of data we are creating them using cron job here we have to pas only link to download that file 
        $csv_url = get_csv_url($format);
        wp_redirect($csv_url);
    }
}

function get_csv_filepath($format) {
    if (empty($_GET['taxonomy']) && empty($_GET['key'])) {
        $filename = $format == 'first' ? 'exported_data_file.csv' : 'exported_data_file_format_second.csv';
        return ABSPATH . $filename; // Save to root folder
    } else {
        $filename = $format == 'first' ? 'exported_data_file_' . $_GET['taxonomy'] . '_' . $_GET['key'] . '.csv' : 'exported_data_file_format_second_' . $_GET['taxonomy'] . '_' . $_GET['key'] . '.csv';
        return ABSPATH . 'feeds_csv/' . $filename; // Save to feeds folder
    }
}

function get_csv_url($format) {
    if (empty($_GET['taxonomy']) && empty($_GET['key'])) {
        $filename = $format == 'first' ? 'exported_data_file.csv' : 'exported_data_file_format_second.csv';
        return site_url('/' . $filename); // URL to access the CSV file
    } else {
        $filename = $format == 'first' ? 'exported_data_file_' . $_GET['taxonomy'] . '_' . $_GET['key'] . '.csv' : 'exported_data_file_format_second_' . $_GET['taxonomy'] . '_' . $_GET['key'] . '.csv';
        return site_url('/feeds_csv/' . $filename); // URL to access the CSV file
    }
}


function offers_by_brands()
{
    
    if(isset($_GET['term'])){
            $term = $_GET['term'];
            
            $args = array(
                'taxonomy'   => 'offers_brands',
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => true,
                'search'     => $term,
                'number'     => 25
            );
        
            $brands = get_terms($args);
        
            $results = array();
            foreach ($brands as $brand) {
                $results[] = array(
                    'id'   => $brand->term_id,
                    'text' => $brand->name,
                );
            }
        
            wp_send_json($results);
            exit();
    }
   
}

function offers_by_category()
{
    
    if(isset($_GET['term'])){
            $term = $_GET['term'];
            
            $args = array(
                'taxonomy'   => 'offers_category',
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => true,
                'search'     => $term,
                'number'     => 25
            );
        
            $brands = get_terms($args);
        
            $results = array();
            foreach ($brands as $brand) {
                $results[] = array(
                    'id'   => $brand->term_id,
                    'text' => $brand->name,
                );
            }
        
            wp_send_json($results);
            exit();
    }
   
}

function feeds_exporter($action){?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <div>
        <select name="brand_dropdown" id="brand_dropdown" class="form-control" style="width: 45%;">
            <option value="">Select a Brand</option>
        </select>
        
        <select name="category_dropdown" id="category_dropdown" class="form-control" style="width: 45%;">
            <option value="">Select a Category</option>
        </select>
    </div>
    <div class="wrap">
        
        <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.css">
        <h1>Export Data to CSV</h1>
        <table id="example" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item title</th>
                    <th>Final URL</th>
                    <th>Image URL</th>
                    <th>Item subtitle</th>
                    <th>Item description</th>
                    <th>Item category</th>
                    <th>Price</th>
                    <th>Sale price</th>
                    <th>Contextual keywords</th>
                    <th>Item address</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
        </table>
        
        <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.dataTables.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.colVis.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var dataTable = '';
        var key = '';
        var taxonomy = '';
                
            function table () {
                dataTable = new DataTable('#example', {
                            ajax: {
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: '<?php echo $action; ?>',
                                    type:'<?php echo $action; ?>',
                                    searchKey : key,
                                    taxonomy:taxonomy
                                }
                            },
                            processing: true,
                            serverSide: true,
                            columns: [
                                { data: 'id'},
                                { data: 'item_title'},
                                { data: 'final_url' },
                                { data: 'image_url' },
                                { data: 'item_subtitle' },
                                { data: 'item_description',
                                    render: function(data, type, row) {
                                         // Adjust based on the rendering type
                                        if (type === 'display') {
                                            return data.length > 100 ?
                                                data.substr(0, 100) + '...' :
                                                data;
                                        } else if (type === 'export') {
                                            // Return full content for export
                                            return data;
                                        }
                                        return data;  // Default return for other types
                                    }
                                },
                                { data: 'item_category' },
                                { data: 'price' },
                                { data: 'sale_price' },
                                { data: 'contextual_keywords' },
                                { data: 'item_address' },
                            ],
                            layout: {
                                    top1Start: {
                                        buttons: [
                                            'copyHtml5', 'excelHtml5', 'pdfHtml5', 'csvHtml5' ,
                                            {
                                                text: 'Export All Data',
                                                split: [
                                                    {
                                                        text: 'format 1',
                                                        action: function(e, dt, node, config) {
                                                            // Get the current page URL
                                                            var currentUrl = window.location.href;
                                                            // Check if the URL already has query parameters
                                                            var separator = currentUrl.includes('?') ? '&' : '?';
                                                            // Redirect to the current page URL with added mode parameter
                                                            window.location.href = currentUrl + separator + 'mode=all'+separator+'format=first'+separator+'taxonomy='+taxonomy+separator+'key='+key;
                                                        }
                                                    },
                                                    {
                                                        text: 'format 2',
                                                        action: function(e, dt, node, config) {
                                                            // Get the current page URL
                                                            var currentUrl = window.location.href;
                                                            // Check if the URL already has query parameters
                                                            var separator = currentUrl.includes('?') ? '&' : '?';
                                                            // Redirect to the current page URL with added mode parameter
                                                            window.location.href = currentUrl + separator + 'mode=all'+separator+'format=second'+separator+'taxonomy='+taxonomy+separator+'key='+key;
                                                        }
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                            }
                    });
                }
                
            jQuery(document).ready(() => {
                table();
                
                //  Brands select 2 drop down
                jQuery('#brand_dropdown').select2({
                    ajax: {
                        url: ajaxurl, // WordPress AJAX URL
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term || '', // search term
                                action: 'offers_by_brands' // AJAX action
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    // minimumInputLength: 3//
                    placeholder: 'Search for an Brand',
                    allowClear: true
                });
            
                jQuery('#brand_dropdown').on('select2:open', function() {
                    var select2 = jQuery(this).data('select2');
                    if (!select2.dropdown.$search.val()) {
                        select2.trigger('query', {
                            term: ''
                        });
                    }
                });
            
                jQuery('#brand_dropdown').on('select2:select', function (e) {
                    //  key = e.params.data.text;
                    key = e.params.data.id;
                    taxonomy='offers_brands';
                    console.log(key);
                    reloadDataTable();
                    jQuery("#category_dropdown").select2("val",0);
                });
                
                jQuery('#brand_dropdown').on('select2:unselect', function (e) {
                    //  key = e.params.data.text;
                    key = '';
                    taxonomy='';
                    console.log(key);
                    reloadDataTable();
                });
                
                //  Category select 2 drop down
                jQuery('#category_dropdown').select2({
                    ajax: {
                        url: ajaxurl, // WordPress AJAX URL
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term || '', // search term
                                action: 'offers_by_category' // AJAX action
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    // minimumInputLength: 3//
                    placeholder: 'Search for an Category',
                    allowClear: true
                });
                
                jQuery('#category_dropdown').on('select2:open', function() {
                    var select2 = jQuery(this).data('select2');
                    if (!select2.dropdown.$search.val()) {
                        select2.trigger('query', {
                            term: ''
                        });
                    }
                });
            
                jQuery('#category_dropdown').on('select2:select', function (e) {
                    //  key = e.params.data.text;
                    key = e.params.data.id;
                    taxonomy='offers_category';
                    console.log(key);
                    reloadDataTable();
                    jQuery("#brand_dropdown").select2("val",0);
                });
                
                jQuery('#category_dropdown').on('select2:unselect', function (e) {
                    key = '';
                    taxonomy='';
                    console.log(key);
                    reloadDataTable();
                });
            });  

            
            function reloadDataTable() {
                 dataTable.destroy(); 
                 table();
            }
                
        </script>
    </div>
<?php
}
?>


