<?php
/**
 * Listify child theme.
 */
function listify_child_styles() {
    wp_enqueue_style( 'listify-child', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'listify_child_styles', 999 );

/** Place any new code below this line */

/**
 * Plugin Name: Listify - Remove Single Listing Action Links
 */
 
function custom_listify_remove_action_links() { 
    remove_all_actions( 'listify_single_job_listing_actions_start' );
}
add_action( 'init', 'custom_listify_remove_action_links' );

if( !function_exists("breadcrumb_print_category") ){
    function breadcrumb_print_category( $category ){
        return '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">'.
        '<a itemscope itemtype="http://schema.org/Thing" itemprop="item" href="'.esc_url(get_category_link( $category->cat_ID )).'">'.
        '<span itemprop="name">'.$category->cat_name.'</span>'.
        '</a>'.
        '</li>';
    }
}

if( !function_exists("breadcrumb_get_parent_category") ){
    function breadcrumb_get_parent_category ( $category, $html ) {
        if ( $category->category_parent == 0 ) {
            $html = breadcrumb_print_category( $category, $html ).$html;
        }
        else{
            $html = breadcrumb_print_category( $category ).$html;
            $html = breadcrumb_get_parent_category( get_category( $category->category_parent ), $html );
        }
        return $html;
    }
}

/********raking*****/
if (!function_exists('get_last_category_id')):
    function get_last_category_id( $post_id = null )
    {
        if ($post_id == null) {
            $post_id = false;
        }
        $printed = array();
        $categories = get_the_category($post_id);
        if (!empty($categories)){
            foreach( $categories as $category){
                if( $category->category_parent != 0 && !in_array($category->category_parent, $printed )){
                    $printed[] = $category->category_parent;
                }
                if(!in_array($category->cat_ID, $printed )){
                    $printed[] = $category->cat_ID;
                }
            }
            return $printed[count($printed)-1];
        }

        return false;
    }
endif;
if (!function_exists('get_post_top_page_view')):
    function get_post_top_ranking_view($limit = 0, $cat_id = null, $current_post_id = null){
        $postTopList = get_access_ranking($cat_id, $limit, $current_post_id);
        if (count($postTopList)<$limit){
            $recent_posts = get_recent_post_top($cat_id, $limit, $postTopList, $current_post_id);
            $postTopList = array_merge($postTopList, $recent_posts);
        }
        return $postTopList;
    }
endif;
// Get access ranking data
if (!function_exists('get_access_ranking')):
    function get_access_ranking($cat_id = null, $limit = 0, $current_post_id = null)
    {
        global $wpdb;
        $cond = '';
        $limit_cond = '';
        $order_by = 'ORDER BY page_view DESC';
        $getdata = array();
        $table_name = $wpdb->prefix . 'access_ranking';

        if ($current_post_id && is_numeric($current_post_id)){
            $cond = "AND post_id <> $current_post_id";
        }
        if ($limit != 0 && is_numeric($limit)) {
            $limit_cond = "LIMIT $limit";
        }

        if(is_numeric($cat_id)){
            $getdata = $wpdb->get_results(" SELECT post_id as ID FROM $table_name WHERE 1 $cond AND cat_id = $cat_id $order_by $limit_cond", 'ARRAY_A');
        }else{
            $getdata = $wpdb->get_results(" SELECT post_id as ID FROM $table_name WHERE 1 $cond $order_by $limit_cond", 'ARRAY_A');
        }

        return array_map('get_map_post_id', $getdata);
    }
endif;
if (!function_exists('get_map_post_id')):
    function get_map_post_id($post){
        return $post['ID'];
    }
endif;
if (!function_exists('get_recent_post_top')):
function get_recent_post_top($cat_id, $limit = 0, $exclude_post_ids = array(), $current_post_id = ''){
    $args = array(
        'numberposts' => $limit - count($exclude_post_ids),
        'offset' => 0,
        'category' => $cat_id,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'include' => '',
        'exclude' => empty($exclude_post_ids)?$current_post_id:implode(',',$exclude_post_ids).','.$current_post_id,
        'meta_key' => '',
        'meta_value' =>'',
        'post_type' => 'post',
        'post_status' => 'publish',
        'suppress_filters' => true,
    );

    $recent_post = wp_get_recent_posts( $args, ARRAY_A );
    wp_reset_query();
    return array_map('get_map_post_id', $recent_post);
}
endif;

function get_ranking_pages(){
    $ranking_pages = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'page-templates/template-ranking.php'
    ));
    if ($ranking_pages) return $ranking_pages;
    return false;
}
function custom_ranking_category_rewrite_rule_vars($vars)
{
    $custom_ranking_category_rewrite_rule_vars = array(
        'cat_slug'
    );
    return array_merge($custom_ranking_category_rewrite_rule_vars, $vars);
}
add_filter('query_vars', 'custom_ranking_category_rewrite_rule_vars');
function custom_ranking_category_rewrite_rule() {
    if ($ranking_pages = get_ranking_pages()){
        $ranking_page_id = $ranking_pages[0]->post_id;
        $ranking_page_name = $ranking_pages[0]->post_name;
        add_rewrite_rule('^'.$ranking_page_name.'/?$', "index.php?page_id=$ranking_page_id", 'top');
        add_rewrite_rule('^'.$ranking_page_name.'/([-a-zA-Z0-9]*)/?$', "index.php?page_id=$ranking_page_id".'&cat_slug=$matches[1]', 'top');
    }
}
add_action('init', 'custom_ranking_category_rewrite_rule');
/********end*********/

// determine the topmost parent of a term
function get_term_top_most_parent($term_id, $taxonomy){
    // start from the current term
    $parent  = get_term_by( 'id', $term_id, $taxonomy);
    // climb up the hierarchy until we reach a term with parent = '0'
    while ($parent->parent != '0'){
        $term_id = $parent->parent;

        $parent  = get_term_by( 'id', $term_id, $taxonomy);
    }
    return $parent;
}

function job_listing_post_type_link( $permalink, $post ) {

    // Abort if post is not a job
    $job_listing_region = '';
    if ( $post->post_type !== 'job_listing' ) {

        return $permalink;

    }

    // Abort early if the placeholder rewrite tag isn't in the generated URL

    if ( false === strpos( $permalink, '%' ) ) {

        return $permalink;

    }

    // Get the custom taxonomy terms in use by this post

    // $categories = wp_get_post_terms( $post->ID, 'job_listing_category', array( 'orderby' => 'parent', 'order' => 'ASC' ) );

    $regions    = wp_get_post_terms( $post->ID, 'job_listing_region', array( 'orderby' => 'parent', 'order' => 'ASC' ) );
    $parent_region = 'country';


    // if ( empty( $categories ) ) {

    //     // If no terms are assigned to this post, use a string instead (can't leave the placeholder there)

    //     $job_listing_category = _x( 'uncategorized', 'slug' );

    // } else {

    //     // Replace the placeholder rewrite tag with the first term's slug

    //     $first_term = array_shift( $categories );

    //     $job_listing_category = $first_term->slug;

    // }



    if ( empty( $regions ) ) {

        // If no terms are assigned to this post, use a string instead (can't leave the placeholder there)

        $job_listing_region = _x( 'anywhere', 'slug' );

    } else {

        // Replace the placeholder rewrite tag with the first term's slug
        // $first_term = array_shift( $regions );
        $first_term = array_pop($regions);
        $parent = get_term_top_most_parent($first_term->term_id, 'job_listing_region' );

        if($parent && $first_term->slug != $parent->slug){
            $parent_region = $parent->slug;
        }
        $job_listing_region .= $first_term->slug;
    }



    $find = array(

        '%category%',
        '%region%'

    );

    $replace = array(

        $parent_region,
        $job_listing_region

    );

    $replace = array_map( 'sanitize_title', $replace );

    $permalink = str_replace( $find, $replace, $permalink );

    return $permalink;

}

add_filter( 'post_type_link', 'job_listing_post_type_link', 10, 2 );

// function wpb_woo_my_account_order() {
//  $myorder = array(
//  'my-custom-endpoint' => __( 'My Stuff', 'woocommerce' ),
//  '/testing/edit-account/acc' => __( 'Change My Details', 'woocommerce' ),
//  'dashboard' => __( 'Dashboard', 'woocommerce' ),
//  'orders' => __( 'Orders', 'woocommerce' ),
//  'downloads' => __( 'Download MP4s', 'woocommerce' ),
//  'edit-address' => __( 'Addresses', 'woocommerce' ),
//  'payment-methods' => __( 'Payment Methods', 'woocommerce' ),
//  'customer-logout' => __( 'Logout', 'woocommerce' ),
//  );
//  return $myorder;
// }
//add_filter ( 'woocommerce_account_menu_items', 'wpb_woo_my_account_order' );

add_action( 'init', 'custom_page_rules' );
 
function custom_page_rules() {
    global $wp_rewrite;
    $wp_rewrite->page_structure = $wp_rewrite->root . 'p/%pagename%'; 
}

//add_filter('query_vars', 'add_account_edit_var', 0, 1);
// function add_account_edit_var($vars){
//     $vars[] = 'edit-account';
//     return $vars;
// }

add_action( 'init', 'add_account_edit_rule' );
function add_account_edit_rule() {
    //add_rewrite_rule(
    //    '^myaccount/?$',
    //    'index.php?pagename=myaccount',
    //    'top'
    //);
    add_rewrite_rule(
        '^myaccount/([^/]*)/?$',
        'index.php?pagename=myaccount&$matches[1]',
        'top'
    );
}

function change_job_listing_slug( $args ) {

  $args['rewrite']['slug'] = '%category%/%region%';
  // $args['rewrite']['with_front'] = false;
  // $args['rewrite']['hierarchical'] = true;
  return $args;

}
//here
add_filter( 'register_post_type_job_listing', 'change_job_listing_slug');

//function add_category_endpoint_tag() {
//    add_rewrite_tag( '%category%', '([^/]*)' );
//}
//add_action( 'init', 'add_category_endpoint_tag' );

function add_region_endpoint_tag() {
    add_rewrite_tag( '%region%', '([^/]*)' );
}
add_action( 'init', 'add_region_endpoint_tag' );

/*function custom_job_manager_regions_dropdown_args( $args ) {
    $args['rewrite']['slug'] = 'testing/%region%';
    
    return $args;
}*/
//add_filter( 'job_manager_regions_dropdown_args', 'custom_job_manager_regions_dropdown_args' );

function myplugin_rewrite_rule() {
	add_rewrite_rule( '^p/myaccount/([^/]*)/?$', 'index.php?pagename=myaccount/$matches[1]','top' );
    add_rewrite_rule( '^p/([^/]*)/?$', 'index.php?pagename=$matches[1]','top' );
    add_rewrite_rule( '^magazine/?$', 'index.php?pagename=magazine','top' );
    add_rewrite_rule( '^magazine/page/([0-9]+)/?$', 'index.php?pagename=magazine&paged=$matches[1]','top' );
    add_rewrite_rule( '^magazine/([^/]*)/?$', 'index.php?post_type=post&name=$matches[1]','top' );
}
add_action('init', 'myplugin_rewrite_rule', 10, 0);

// // Change the 'job-region' slug
// add_filter( 'register_taxonomy_job_listing_region_args', 'change_job_listing_region_rewrite' );

// function change_job_listing_region_rewrite( $options ) {

//     $options['rewrite'] =  array(
//         'slug'         => 'industria',
//         'with_front'   => false,
//         'hierarchical' => false,
//     );
//     return $options;
// }


// function diww_menu_logout_link( $nav, $args ) {
// 	$logoutlink = '<li><a href="'.wp_logout_url(home_url()).'">Logout</a></li>';
// 	if( $args->theme_location == 'primary' ) {
// 		return $nav.$logoutlink ;
// 	} else {
// 	return $nav;
// 	}
// }
//add_filter('wp_nav_menu_items','diww_menu_logout_link', 10, 2);

// function exclude_children($wp_query) {
//     if ( isset ( $wp_query->query_vars['job_listing_region'] ) ) {
//         $wp_query->set('tax_query', array( array (
//             'taxonomy' => 'job_listing_region',
//             'field' => 'slug',
//             'terms' => $wp_query->query_vars['job_listing_region'],
//             'include_children' => true
//         ) )
//     }
// }  
// add_filter('pre_get_posts', 'exclude_children'); 

function iconic_bypass_logout_confirmation() {
    global $wp;
 
    if ( isset( $wp->query_vars['customer-logout'] ) ) {
        wp_redirect( str_replace( '&amp;', '&', wp_logout_url( wc_get_page_permalink( 'myaccount' ) ) ) );
        exit;
    }
}
 
add_action( 'template_redirect', 'iconic_bypass_logout_confirmation' );

 // function custom_login_url( $url, $redirect ) {
 //   global $listify_woocommerce;

 //   return $listify_woocommerce->login_url( $url, $redirect );
 // }
 // add_filter( 'login_url', 'custom_login_url', 10, 2 );

function inventory_replace_listing_regions_object_label() {
    global $wp_taxonomies;
    if ( ! isset( $wp_taxonomies['job_listing_region'] ) ) {
        return;
    }
    // get the arguments of the already-registered taxonomy
    $job_listing_region_args = get_taxonomy( 'job_listing_region' ); // returns an object
    // $labels = &$job_listing_region_args->labels;
    // $labels->name                       = esc_html__( 'Listing Regions', 'inventory' );
    // $labels->singular_name              = esc_html__( 'Region', 'inventory' );
    // $labels->search_items               = esc_html__( 'Search Regions', 'inventory' );
    // $labels->popular_items              = esc_html__( 'Popular Regions', 'inventory' );
    // $labels->all_items                  = esc_html__( 'All Regions', 'inventory' );
    // $labels->parent_item                = esc_html__( 'Parent Region', 'inventory' );
    // $labels->parent_item_colon          = esc_html__( 'Parent Region:', 'inventory' );
    // $labels->edit_item                  = esc_html__( 'Edit Region', 'inventory' );
    // $labels->view_item                  = esc_html__( 'View Region', 'inventory' );
    // $labels->update_item                = esc_html__( 'Update Region', 'inventory' );
    // $labels->add_new_item               = esc_html__( 'Add New Region', 'inventory' );
    // $labels->new_item_name              = esc_html__( 'New Region Name', 'inventory' );
    // $labels->separate_items_with_commas = esc_html__( 'Separate regions with commas', 'inventory' );
    // $labels->add_or_remove_items        = esc_html__( 'Add or remove regions', 'inventory' );
    // $labels->choose_from_most_used      = esc_html__( 'Choose from the most used regions', 'inventory' );
    // $labels->not_found                  = esc_html__( 'No regions found.', 'inventory' );
    // $labels->no_terms                   = esc_html__( 'No regions', 'inventory' );
    // $labels->menu_name                  = esc_html__( 'Regions', 'inventory' );
    // $labels->name_admin_bar             = esc_html__( 'Listing Region', 'inventory' );
    // $job_listing_region_args->label     = esc_html__( 'Listing Regions', 'inventory' );
    $job_rewrite     = array(
        'slug'         => _x( 'job-region', 'Job region slug - resave permalinks after changing this', 'wp-job-manager-locations' ),
        'with_front'   => false,
        'hierarchical' => true
    );

    $job_listing_region_args->rewrite = $job_rewrite;
    // re-register the taxonomy
    // register_taxonomy( 'job_listing_region', array( 'job_listing' ), (array) $job_listing_region_args );
}
add_action( 'init', 'inventory_replace_listing_regions_object_label');

add_filter( 'lostpassword_url',  'wdm_lostpassword_url', 10, 0 );
function wdm_lostpassword_url() {
    return site_url('/myaccount/lost-password');
}


// add_filter('pre_get_document_title', 'my_custom_title');
// function my_custom_title( $title )
// {
//     if( is_tax("job_listing_region"))
//     {
//         return sprintf("List of %s Luxury Hotels | %s", $title, strtoupper(get_bloginfo('name')));
//     }
//     // Return my custom title
//     return $title;
// }

add_action('wp_head', 'wpb_add_googleanalytics');
function wpb_add_googleanalytics() { ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-107353590-2"></script> 
    <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'UA-107353590-2'); </script>
<?php } ?>