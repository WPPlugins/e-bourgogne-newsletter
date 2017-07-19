<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EbouNewsletterPluginWidget extends WP_Widget {

	public function __construct() {
		parent::WP_Widget( false, $name = "e-bourgogne Newsletter" );
		$this->setup_scripts();
	}

	/**
	* Setup the scripts
	*/
	private function setup_scripts() {
		// Load the script only if a EbouNlWidget is set on the current page
		if( !is_admin() && is_active_widget( false, false, $this->id_base, true ) ) {
			wp_register_script( 
				'ebou-nl-ebounlwidget-js', 
				EBOU_NL_PLUGIN_RESSOURCES_URL . 'js/ebounlwidget.js',
				array( 'jquery' )
			);

			wp_localize_script( 'ebou-nl-ebounlwidget-js', 'ebou_nl_api_url', EBOU_NL_BO_API_ADD_URL );
			wp_localize_script( 'ebou-nl-ebounlwidget-js', 'ebou_nl_api_apikey_referer', EBOU_NL_BO_API_APIKEY_REFERER );
			wp_localize_script( 'ebou-nl-ebounlwidget-js', 'ebou_nl_api_key', get_option( EBOU_NL_APIKEY_OPTION_KEY ) );
			wp_localize_script( 'ebou-nl-ebounlwidget-js', 'invalid_mail_msg', __( "Veuillez indiquer une adresse mail valide", 'e-bourgogne-newsletter' ) );
			wp_localize_script( 'ebou-nl-ebounlwidget-js', 'invalid_nl_msg', __( "Veuillez choisir une newsletter valide", 'e-bourgogne-newsletter' ) );
			wp_localize_script( 'ebou-nl-ebounlwidget-js', 'success_subscribe_msg', __( "Votre inscription a bien été enregistrée", 'e-bourgogne-newsletter' ) );
			wp_localize_script( 'ebou-nl-ebounlwidget-js', 'unknow_error_msg', __( "Une erreur est survenue lors de votre inscription, veuillez réessayer ultérieurement", 'e-bourgogne-newsletter' ) );
			wp_enqueue_script( 'ebou-nl-ebounlwidget-js' );
		}
	}

	public function form( $instance ) {
		if( $instance ) {
			// Remove potential escape slashes added by WP when saving in database
			$title = stripslashes_deep( $instance['title'] );
			$text = stripslashes_deep( $instance['text'] );
			$files = $instance['files'];
		} else {
			$title =  "";
			$text = "";
			$files = array();
		}

		$this->api_key = get_option( EBOU_NL_APIKEY_OPTION_KEY );
		$organism_id = explode( ':', base64_decode( $this->api_key ) )[0];
		$url = EBOU_NL_BO_API_LIST_URL . $organism_id;

		$headers = array(
			EBOU_NL_BO_API_APIKEY_REFERER . ': ' . $this->api_key
		);
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_PROXY, EBOU_NL_PROXY );

		$organism_available_files  = curl_exec( $curl );
		curl_close( $curl );

		$is_nl_available = !is_null( $organism_available_files ) && $organism_available_files != "";

		if( $is_nl_available ){
			$organism_files_json = json_decode( $organism_available_files , true );
		} else {
			$organism_files_json = array();
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( "Titre", 'e-bourgogne-newsletter' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( "Texte", 'e-bourgogne-newsletter' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" type="text" value="<?php echo $text; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'files' ); ?>"><?php _e( "Newsletter(s) affichée(s)", 'e-bourgogne-newsletter' ); ?></label>
			<select multiple="multiple" id="<?php echo $this->get_field_id( 'files' ); ?>" name="<?php echo $this->get_field_name( 'files' ); ?>[]" value="<?php echo $files ?>">
				<?php
					foreach ( $organism_files_json as $file ) {
						$file_id = $file['id'];
						$file_display_title = htmlentities( $file['displayTitle'] ); // replacing special characters by corresponding HTML entities
						$file_code = json_encode( array( $file_id, $file_display_title ) ); // encode the array in a string to integrate it in <option>

						echo '<option value="' . htmlentities( $file_code ) . '" ' . ( in_array( $file_code, $files ) ? 'selected="selected"' : '' ) . '>' . $file_display_title . '</option>';
					}
				?>
			</select>
		</p>

		<script type="text/javascript">
			// Hack to allow multiple selection whithout using Ctrl + Click
			jQuery('#<?php echo $this->get_field_id( 'files' ); ?> option').mousedown(function(evt) {
				evt.preventDefault();
				jQuery(this).attr('selected', !jQuery(this).attr('selected'));
				return false;
			});
		</script>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = esc_sql( $new_instance['title'] );
		$instance['text'] = esc_sql( $new_instance['text'] );
		$instance['files'] = $new_instance['files'];

		return $instance;  
	}

	public function widget( $args, $instance ) {
		extract( $args );

		// Remove potential escape slashes added by WP when saving in database
		$title = stripslashes_deep( apply_filters( 'widget_title', $instance['title'] ) );
		$text = stripslashes_deep( $instance['text'] );
		$files = $instance['files'];

		echo $before_widget;
		?>

		<div class="widget-text wp_widget_plugin_box">

			<?php
			if( $title ) {
				echo $before_title . $title . $after_title;
			}

			if( $text ) {
				echo '<p class="wp_widget_plugin_text">' . $text . '</p>';
			}

			if( $files ) {
				echo '<p>';
				echo '<select id="' . $widget_id . '-ebou-nl-file" name="' . $widget_id . '-ebou-nl-file" class="ebou-nl-select' . ( ( count( $files ) == 1 ) ? ' ebou-nl-soloselect' : '' ) . '"">';
				foreach( $files as $file ) {
					$file = json_decode( $file ); // decode the string saved in database in an array; first cell is the newsletter's id, second is its title
					$file_id = $file[0];
					$file_display_title = $file[1];

					echo '<option value="' . $file_id . '">' . $file_display_title . '</option>';
				}
				echo '</select>';
				echo '</p>';
			}	
			?>
			<p>
				<input id="<?php echo $widget_id; ?>-ebou-user-email" type="search" name="ebou-user-email" placeholder="<?php _e( "Entrez votre email", 'e-bourgogne-newsletter' ); ?>" />
			</p>

			<p id="<?php echo $widget_id; ?>-ebou-nl-success" class="ebou-nl-success" style="display: none;">
			</p>

			<p id="<?php echo $widget_id; ?>-ebou-nl-error" class="ebou-nl-error" style="display: none;">
			</p>

			<p>
				<button id="<?php echo $widget_id; ?>-ebou-nl-subscribe" type="button"><?php _e( "S'inscrire", 'e-bourgogne-newsletter' ); ?></button>
				<span id="<?php echo $widget_id; ?>-ebou-nl-spinner" class="ebou-nl-spinner" style="display: none;"><img src="<?php echo includes_url(); ?>images/wpspin-2x.gif" alt="<?php _e( "Inscription en cours...", 'e-bourgogne-newsletter' ); ?>" /></span>
			</p>
		</div>

		<script type="text/javascript">
			// Create a new JS object EbouNlWidget that will handle all the plugin's actions
			var <?php echo str_replace( '-', '_', $widget_id ) ?> = new EbouNlWidget('<?php echo $widget_id; ?>');			
			<?php echo str_replace( '-', '_', $widget_id ) ?>.init();
		</script>

		<?php
		echo $after_widget;
	}
}




?>