<?php
/**
 * Plugin Name: Woocommerce Connect For <strong>Tally Framework</strong>
 * Plugin URI:  http://tallythemes.com/
 * Description: Add basic woocommercee templating and Style for  <strong> Tally Framework</strong>
 * Author:      TallyThemes
 * Author URI:  http://tallythemes.com/
 * Version:     1.1
 * Text Domain: woocommerce_connect_for_tally
 * Domain Path: /languages/
 * Name Space: wootallyc
 * Name Space: WOOTALLYC
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

$path_dir = trailingslashit(str_replace('\\','/',dirname(__FILE__)));
$path_abs = trailingslashit(str_replace('\\','/',ABSPATH));

define('WOOTALLYC', 'Woocommerce Connect For Tally Framework' );
define('WOOTALLYC_URL', site_url(str_replace( $path_abs, '', $path_dir )) );
define('WOOTALLYC_DRI', $path_dir );
define('WOOTALLYC_TEMPLATE', WOOTALLYC_DRI.'woocommerce' );
define('WOOTALLYC_VERSION', 1.1 );


/*
 Load textdomain
--------------------------------*/
add_action('init', 'wootallyc_load_plugin_textdomain');
function wootallyc_load_plugin_textdomain(){
  load_plugin_textdomain( 'woocommerce_connect_for_tally', false, dirname(plugin_basename(__FILE__)).'/languages/' );
}



/*
 Load the plugin functionality
--------------------------------*/
add_action( 'after_setup_theme', 'wootallyc_init_load' );
function wootallyc_init_load(){
    /** Fail silently if WooCommerce is not activated */
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;	
		
	if(!function_exists('tally_option')) return;	
	
	global $woocommerce;
	
	/** Ensure WooCommerce 2.0+ compatibility */
	add_theme_support( 'woocommerce' );
	
	/** Take control of shop template loading */
	remove_filter( 'template_include', array( &$woocommerce, 'template_loader' ) );
	add_filter( 'template_include', 'wootallyc_template_loader', 20 );
	
	
	/** Removing some unwanted element from woocommerce page content */
	remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
	add_filter('woocommerce_show_page_title', '__return_false');
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	
	/* Setup Shop sidebar*/
	register_sidebar( array(
		'name' => __('Woo Shop Sidebar', 'woocommerce_connect_for_tally'),
		'id' => 'wooshop',
		'description' => __('Woocommerce shop Sidebar Widgets', 'woocommerce_connect_for_tally'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div><div class='clear' style='height:30px;'></div>",
		'before_title' => '<h4 class="heading">',
		'after_title' => '</h4>',
	));
	remove_action( 'tally_sidebar', 'tally_do_sidebar' );
	add_action( 'tally_sidebar', 'wootallyc_add_shop_sidebar' );
	
	
	/* Add Tally Framework's page metabox to the product page*/
	add_filter('tally_ot_page_metabox', 'wootallyc_add_tally_page_metabox');
	
	/*Add Theme option*/
	add_filter('option_tree_settings_args', 'wootallyc_add_theme_option');
	
	/*
	 Apply filter for the sidebar layout by theme options
	--------------------------------*/
	add_filter('tally_sitebar_layout_option', 'wootallyc_tally_sitebar_layout_option');
}



/*
 Adding Shop sidebar in the theme
--------------------------------*/
function wootallyc_add_shop_sidebar(){
	if ( ! dynamic_sidebar( 'wooshop' ) && current_user_can( 'edit_theme_options' )  ) {
		if(function_exists('tally_default_widget_area_content')){ tally_default_widget_area_content( __( 'WooCommerce Shop Sidebar Widget Area', 'tally_textdomain' ) ); };
	}	
}


/*
 Add Theme option
--------------------------------*/
function wootallyc_add_theme_option($custom_settings){
	$custom_settings['sections'][] = array( 'id' => 'woocommerce','title' => 'WooCommerce');
	
	$custom_settings['settings']['woocommerce_archive_sidebar_layout'] = array(
		'id'          => 'woocommerce_archive_sidebar_layout',
        'label'       => __('Default Sidebar Layout for Product Archive Page', 'tally_taxdomain'),
        'desc'        => __('This is the global sidebar layout for WooCommerce Product Archive Pages.', 'tally_taxdomain'),
        'std'         => tally_option_std('woocommerce_archive_sidebar_layout'),
        'type'        => 'radio-image',
        'section'     => 'woocommerce',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => '',
		'choices'     => array(
			 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
			 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
			 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
			 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
			 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
			 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
		)
	);
	
	
	$custom_settings['settings']['woocommerce_cat_sidebar_layout'] = array(
		'id'          => 'woocommerce_cat_sidebar_layout',
        'label'       => __('Default Sidebar Layout for Product Category Archive Page', 'tally_taxdomain'),
        'desc'        => __('This is the global sidebar layout for WooCommerce Product Category Archive Pages.', 'tally_taxdomain'),
        'std'         => tally_option_std('woocommerce_cat_sidebar_layout'),
        'type'        => 'radio-image',
        'section'     => 'woocommerce',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => '',
		'choices'     => array(
			 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
			 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
			 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
			 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
			 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
			 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
		)
	);
	
	
	$custom_settings['settings']['woocommerce_tags_sidebar_layout'] = array(
		'id'          => 'woocommerce_tags_sidebar_layout',
        'label'       => __('Default Sidebar Layout for Product Tags Archive Page', 'tally_taxdomain'),
        'desc'        => __('This is the global sidebar layout for WooCommerce Product Tags Archive Pages.', 'tally_taxdomain'),
        'std'         => tally_option_std('woocommerce_tags_sidebar_layout'),
        'type'        => 'radio-image',
        'section'     => 'woocommerce',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => '',
		'choices'     => array(
			 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
			 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
			 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
			 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
			 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
			 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
		)
	);
	
	
	$custom_settings['settings']['woocommerce_single_sidebar_layout'] = array(
		'id'          => 'woocommerce_single_sidebar_layout',
        'label'       => __('Default Sidebar Layout for Single Product Page', 'tally_taxdomain'),
        'desc'        => __('This is the global sidebar layout for WooCommerce Single Product Page', 'tally_taxdomain'),
        'std'         => tally_option_std('woocommerce_single_sidebar_layout'),
        'type'        => 'radio-image',
        'section'     => 'woocommerce',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => '',
		'choices'     => array(
			 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
			 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
			 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
			 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
			 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
			 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
		)
	);
		
	return $custom_settings;
}


/*
 Apply filter for the sidebar layout by theme options
--------------------------------*/
function wootallyc_tally_sitebar_layout_option($sidebar_layout){
	global $wp_query;
	$custom_field = get_post_meta( get_the_ID(), 'tally_sidebar_layout', true );
	
	if(is_single() && 'product' == get_post_type()){
		
		$sidebar_layout  = $custom_field ? $custom_field : tally_option( 'woocommerce_single_sidebar_layout' );
		
	}elseif(is_post_type_archive( 'product' ) ||  is_page( get_option( 'woocommerce_shop_page_id' ) )){
		
		$sidebar_layout  = $custom_field ? $custom_field : tally_option( 'woocommerce_archive_sidebar_layout' );
		
	}elseif(is_tax('product_tag')){
		
		$sidebar_layout  = $custom_field ? $custom_field : tally_option( 'woocommerce_tags_sidebar_layout' );
		
	}elseif(is_tax('product_cat')){
		
		$sidebar_layout  = $custom_field ? $custom_field : tally_option( 'woocommerce_cat_sidebar_layout' );
	}
	
	return $sidebar_layout;
}


/*
 Add Tally Framework's page metabox to the product page
--------------------------------*/
function wootallyc_add_tally_page_metabox($post){
	if(post_type_exists('product')){
		$post[] = 'product';
	}
	
	return $post;
}



/*
 Woocommerce template loading 
--------------------------------*/
function wootallyc_template_loader( $template ) {


	if ( is_single() && 'product' == get_post_type() ) {

		$template = locate_template( array( 'woocommerce/single-product.php' ) );

		if ( ! $template )
			$template = WOOTALLYC_TEMPLATE . '/single-product.php';
		

	}
	elseif ( is_post_type_archive( 'product' ) ||  is_page( get_option( 'woocommerce_shop_page_id' ) ) ) {

		$template = locate_template( array( 'woocommerce/archive-product.php' ) );

		if ( ! $template )
			$template = WOOTALLYC_TEMPLATE . '/archive-product.php';

	}
	elseif ( is_tax() ) {

		$term = get_query_var( 'term' );

		$tax = get_query_var( 'taxonomy' );

		/** Get an array of all relevant taxonomies */
		$taxonomies = get_object_taxonomies( 'product', 'names' );

		if ( in_array( $tax, $taxonomies ) ) {

			$tax = sanitize_title( $tax );
			$term = sanitize_title( $term );

			$templates = array(
				'woocommerce/taxonomy-'.$tax.'-'.$term.'.php',
				'woocommerce/taxonomy-'.$tax.'.php',
				'woocommerce/taxonomy.php',
			);

			$template = locate_template( $templates );

			/** Fallback to GCW template */
			if ( ! $template )
				$template = WOOTALLYC_TEMPLATE . '/taxonomy.php';
		}
	}

	return $template;

}


add_action('wp_head', 'wootallyc_show_page_content');
function wootallyc_show_page_content(){
	/** Adding content to the pages */
	if(is_single() && 'product' == get_post_type()){
		remove_action('tally_loop', 'tally_do_loop_content');
		add_action('tally_loop', 'wootallyc_do_single_template_content');
	}
	elseif(is_post_type_archive( 'product' ) ||  is_page( get_option( 'woocommerce_shop_page_id' ) ) ){
		remove_action('tally_loop', 'tally_do_loop_content');
		add_action('tally_loop', 'wootallyc_do_archive_template_content');
	}
	elseif(is_tax()){
		$term = get_query_var( 'term' );
		$tax = get_query_var( 'taxonomy' );
		/** Get an array of all relevant taxonomies */
		$taxonomies = get_object_taxonomies( 'product', 'names' );
		
		if ( in_array( $tax, $taxonomies ) ) {
			remove_action('tally_loop', 'tally_do_loop_content');
			add_action('tally_loop', 'wootallyc_do_archive_template_content');
		}
	}	
}




/*
  Supply single product page content
  @ Used in a hook - tally_loop
------------------------------------------*/
function wootallyc_do_single_template_content(){
	?>
    <?php
		/**
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );
	?>

		<?php while ( have_posts() ) : the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>

	<?php
		/**
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>
    <?php
}



/*
  Supply Archive product page content
  @ Used in a hook - tally_loop
------------------------------------------*/
function wootallyc_do_archive_template_content(){
	?>
    <?php
		/**
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );
	?>

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

			<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

		<?php endif; ?>

		<?php do_action( 'woocommerce_archive_description' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; ?>

	<?php
		/**
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>
    <?php
}