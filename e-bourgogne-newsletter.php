<?php

/**
* @package e-bourgogne-newsletter
* @version 1.0.1
*/
/*
Plugin Name: e-bourgogne Newsletter
Plugin URI: https://www.e-bourgogne.fr
Description: Newsletter e-bourgogne
Requires at least: 3.3
Tested up to: 4.2.4
Version: 1.0.1
Author: e-bourgogne
Author URI: http://www.e-bourgogne.fr
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once 'widget.php';

if( !class_exists( 'EbouNewsletterPlugin' ) ) :

/**
* Register the plugin
*
* Display the 'Newsletter' pannel on post/page editing, add the 'newsletter' to the page, etc...
*/
class EbouNewsletterPlugin {

	/**
	* Initialization
	*/
	public static function init() {
		$ebounl = new self();
	}

	/**
	* Add rewrite rules and regenerate rewrite_rules database
	* Called on plugin activation
	*/
	public static function rewrite_and_flush_rules() {
		// Add same rewrite_rule as in rewrite_rules()
		add_rewrite_rule( 'ebounl/unsubscribe/([0-9]+)/from/([0-9]+)/?', 
			'index.php?ebounl=unsubscribe&follower-id=$matches[1]&followerfile-id=$matches[2]', 
			'top' );

		// Force reloading of rewrite rules
		flush_rewrite_rules();
	}

	/**
	* Constructor
	*/
	public function __construct() {
		$this->define_constants();
		$this->setup_actions();
		$this->setup_filters();
		$this->setup_styles();
	}

	/**
	* Define the constants used by the plugin
	*/
	private function define_constants() {
		define( 'EBOU_NL_PLUGIN_BASE_URL', plugins_url( 'e-bourgogne-newsletter' ) . '/' );
		define( 'EBOU_NL_PLUGIN_RESSOURCES_URL', EBOU_NL_PLUGIN_BASE_URL . 'resources/' );

		define( 'EBOU_NL_PROXY', '');

		define( 'EBOU_NL_BO_BASE_URL', 'https://mon-site-internet.e-bourgogne.fr/' );
		define( 'EBOU_NL_BO_API_URL', EBOU_NL_BO_BASE_URL . 'api/newsletter/' );
		define( 'EBOU_NL_BO_API_LIST_URL', EBOU_NL_BO_API_URL );
		define( 'EBOU_NL_BO_API_CHECK_URL', EBOU_NL_BO_API_URL . 'check' );
		define( 'EBOU_NL_BO_API_ADD_URL', EBOU_NL_BO_API_URL . 'add/' );
		define( 'EBOU_NL_BO_API_UNSUBSCRIBE_URL', EBOU_NL_BO_API_URL . 'unsubscribe/' );
		define( 'EBOU_NL_BO_API_UNSUBSCRIBE_URL_FROM_POSTFIX', '/from/' );
		define( 'EBOU_NL_BO_API_APIKEY_REFERER', 'ebou-api-key' );

		define( 'EBOU_NL_APIKEY_OPTION_FIELD', 'ebou_nl_api_group' );
		define( 'EBOU_NL_APIKEY_OPTION_KEY', 'ebou_api_key' );
	}

	/**
	* Hook the plugin into Wordpress
	*/
	private function setup_actions() {
		add_action( 'admin_menu', array( $this, 'create_option_menus' ) );
		add_action( 'admin_init', array( $this, 'register_eb_options' ) );
		add_action( 'admin_notices', array( $this, 'display_warning_external_content' ) );
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		add_action( 'init', array( $this, 'rewrite_rules' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'template_redirect', array( $this, 'url_rewrite_templates' ) );
	}

	/**
	* Setup the plugin's filters
	*/
	private function setup_filters() {
		add_filter( 'query_vars', array( $this, 'register_query_var' ) );
	}

	/**
	* Setup the style sheets
	*/
	private function setup_styles() {
		wp_enqueue_style(
			'ebou-nl-style',
			EBOU_NL_PLUGIN_RESSOURCES_URL . 'css/main.css',
			null
		);
	}

	/**
	* Create the top option menu (e-bourgogne)
	*/
	private function create_top_option_menu() {
		add_menu_page( 
			__( "Réglages e-bourgogne - Newsletter", 'e-bourgogne-newsletter' ), 
			'e-bourgogne', 
			'manage_options', 
			'e-bourgogne-options', 
			array( $this, 'display_settings' ), 
			EBOU_NL_PLUGIN_RESSOURCES_URL . 'images/logo.png',
			'menu-bottom' );
	}

	/**
	* Create an stream context with http request method set at 'GET' and referer 'ebou-api-key'
	* set in the header
	*/
	private function create_apikey_header_context() {
		$opts = array(
			'http' => array(
				'method' => 'GET',
				'header' => EBOU_NL_BO_API_APIKEY_REFERER . ': ' . $this->api_key
			)
		);
		return stream_context_create( $opts );
	}

	/**
	* Check if the API answers and if the API key is correct
	* Set both $this->is_tf_available and $this->is_api_key_ok booleans
	*/
	private function check_service_availability_and_key_validity() {
		$headers = array(
			EBOU_NL_BO_API_APIKEY_REFERER . ': ' . $this->api_key
		);

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, EBOU_NL_BO_API_CHECK_URL );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_PROXY, EBOU_NL_PROXY );

		$return  = curl_exec( $curl );

		if( $return === "OK" ) {
			$this->is_nl_available = true;
			$this->is_api_key_ok = true;
		} elseif( strpos( $return, "Invalid API" ) !== false ) {
			$this->is_nl_available = true;
			$this->is_api_key_ok = false;
		} else {
			$this->is_nl_available = false;
		}

		curl_close( $curl );
	}

	/**
	* Check if cURL is enabled, and return an error message if not
	*/
	private function is_curl_enabled() {
		if(function_exists('curl_version')) {
			return true;
		} else {
			return "<div class=\"error\"><p>"
				. __( "Extension cURL désactivée. Les modules e-bourgogne nécessitent cURL pour fonctionner.", 'e-bourgogne-tf' )
				. "</p></div>";
		}
	}

	/**
	* Display a message to warn the user that external data will be loaded
	*/
	public function display_warning_external_content() {
		global $pagenow;
		// Only show for 'e-bourgogne Annuaires' settings page
		if ( $pagenow === 'admin.php' && $_GET['page'] === $this->sub_menu_slug ) { 
			?>
			<div class="update-nag">
				<h4><?php echo sprintf( __( "Important : afin d'assurer son fonctionnement, le plugin e-bourgogne Newsletter charge des données extérieures en provenance d'%se-bourgogne%s", 'e-bourgogne-newsletter' ), '<a href="//www.e-bourgogne.fr">', '</a>' ); ?></h4>
			</div>
			<?php
		}
	}

	/**
	* Register the options
	*/
	public function register_eb_options() {
		register_setting( EBOU_NL_APIKEY_OPTION_FIELD, EBOU_NL_APIKEY_OPTION_KEY );
	}

	/**
	* Register the plugin's widget
	*/
	public function register_widget() {
		if($this->is_curl_enabled() === true) {
			register_widget( 'EbouNewsletterPluginWidget' );
		}
	}

	/**
	* Create the option menus (top if needed and sub-menu)
	*/
	public function create_option_menus() {
		/*
		* This function exists in each e-bourgogne plugins
		*/

		// Checking if the top menu for e-bourgogne already exists ;
		// if not, creates it
		if ( empty ( $GLOBALS['admin_page_hooks']['e-bourgogne-options'] ) ) {
			$this->create_top_option_menu();
			$this->sub_menu_slug = 'e-bourgogne-options';
		} else {
			$this->sub_menu_slug = 'e-bourgogne-options-nl';
		}

		add_submenu_page( 
			'e-bourgogne-options', 
			__( "Réglages e-bourgogne - Newsletter", 'e-bourgogne-newsletter' ), 
			__( "Newsletter", 'e-bourgogne-newsletter' ),
			'manage_options', 
			$this->sub_menu_slug, 
			array( $this, 'display_settings' ) );
	}

	/**
	* Display the settings screen
	*/
	public function display_settings() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<h2><?php _e( "Réglages e-bourgogne - Newsletter", 'e-bourgogne-newsletter' ); ?></h2>

			<form method="POST" action="options.php">
				<?php 
				settings_fields( EBOU_NL_APIKEY_OPTION_FIELD );
				do_settings_sections( EBOU_NL_APIKEY_OPTION_FIELD );

				$is_curl_enabled = $this->is_curl_enabled();
				if($is_curl_enabled !== true) {
					echo $is_curl_enabled;
					return;
				}

				$this->api_key = get_option( EBOU_NL_APIKEY_OPTION_KEY );
				$context = $this->create_apikey_header_context();
				$this->check_service_availability_and_key_validity();
				?>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="<?php echo EBOU_NL_APIKEY_OPTION_KEY ?>"><?php _e( "Clé d'API", 'e-bourgogne-newsletter' ); ?></label>
							</th>
							<td>
								<input id="<?php echo EBOU_NL_APIKEY_OPTION_KEY ?>" class="regular-text" type="text" value="<?php echo $this->api_key; ?>" name="<?php echo EBOU_NL_APIKEY_OPTION_KEY ?>"/>
								<?php
								if( $this->is_api_key_ok && $this->is_nl_available ) {
									?>
									<img class="ebou-nl-check" src="<?php echo EBOU_NL_PLUGIN_RESSOURCES_URL . 'images/green_check_circle.png'; ?>" title="<?php _e( "Votre clé est valide", 'e-bourgogne-newsletter' ); ?>" alt="<?php _e( "Clé valide", 'e-bourgogne-newsletter' ); ?>" />
									<?php
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( "Enregistrer la clé", 'e-bourgogne-newsletter' ), "primary", "submit-api-key", true, "id=\"submit-api-key\""); ?>
			</form>
		

			<?php
			if( !$this->is_api_key_ok && $this->is_nl_available ) {
				?>
				<p class="ebou-nl-error">
					<?php echo sprintf( __( "Votre clé d'API n'est pas valide. Veuillez vérifier si elle correspond bien à celle fournie par e-bourgogne. Si le problème persiste, contactez l'%sassistance e-bourgogne%s.", 'e-bourgogne-newsletter' ), '<a href="https://www.e-bourgogne.fr/jsp/site/Portal.jsp?page_id=39" target="_blank">', '</a>' ); ?>
				</p>
				<?php
			} elseif( !$this->is_nl_available ) {
				?>
				<div class="error">
					<p><?php _e( "Le service Newsletter d'e-bourgogne est actuellement indisponible, veuillez réessayer ultérieurement.", 'e-bourgogne-newsletter' ); ?></p>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	* Add rewrite rules for the plugin
	*/ 
	public function rewrite_rules() {
		add_rewrite_rule( 'ebounl/unsubscribe/([0-9]+)/from/([0-9]+)/?', 
			'index.php?ebounl=unsubscribe&follower-id=$matches[1]&followerfile-id=$matches[2]', 
			'top' );
	}

	/**
	* Initialise translations
 	*/
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'e-bourgogne-newsletter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	* Register custom query_vars for the plugin
	*/
	public function register_query_var( $vars ) {
		$vars[] = 'ebounl';
		$vars[] = 'follower-id';
		$vars[] = 'followerfile-id';
		$vars[] = 'token';

		return $vars;
	}

	/**
	* Add the custom templates corresponding to the rewrite rules
	*/
	public function url_rewrite_templates() {
		if( get_query_var( 'ebounl' ) == "unsubscribe" && get_query_var( 'follower-id' ) && get_query_var( 'followerfile-id' ) && get_query_var( 'token' ) ) {
			add_filter( 'template_include', array( $this, 'template_include_ebounl_unsubscribe' ) );
		}
	}

	/**
	* Return the template for newsletter unsubscription
	*/
	public function template_include_ebounl_unsubscribe() {
		return plugin_dir_path( __FILE__ ) . 'ebou-unsubscribe.php';
	}

}
endif;

add_action( 'plugins_loaded', array( 'EbouNewsletterPlugin', 'init' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, array( 'EbouNewsletterPlugin', 'rewrite_and_flush_rules' ) );

?>