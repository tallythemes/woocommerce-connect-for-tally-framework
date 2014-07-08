<?php
/**
 * Plugin Name: Woocommerce Connect For <strong>Tally Framework</strong>
 * Plugin URI:  http://tallythemes.com/
 * Description: Add basic woocommercee templating and Style for  <strong> Tally Framework</strong>
 * Author:      TallyThemes
 * Author URI:  http://tallythemes.com/
 * Version:     1.0
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
define('WOOTALLYC_VERSION', 1.0 );


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
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
		return;	
	
	global $woocommerce;
	
	/** Ensure WooCommerce 2.0+ compatibility */
	add_theme_support( 'woocommerce' );
	
	/** Take control of shop template loading */
	remove_filter( 'template_include', array( &$woocommerce, 'template_loader' ) );
	add_filter( 'template_include', 'wootallyc_template_loader', 20 );
	
	
	/** Removing some unwanted element from woocommerce page content */
	remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	add_filter('woocommerce_show_page_title', '__return_false');
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