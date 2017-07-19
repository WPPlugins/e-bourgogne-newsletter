<!DOCTYPE html>
<html lang="<?php echo get_bloginfo( 'language' ); ?>">
	<head>
		<meta charset="<?php echo get_bloginfo( 'charset' ); ?>">
		<title><?php echo get_bloginfo( 'name'); ?><?php _e( " - Désinscription newsletter", 'e-bourgogne-newsletter' ); ?></title>
	</head>

	<body>
		<div style="margin: auto; text-align: center;">
		<?php
		$api_key = get_option( EBOU_NL_APIKEY_OPTION_KEY );

		$follower_id = get_query_var( 'follower-id' );
		$followerfile_id = get_query_var( 'followerfile-id' );
		$token = get_query_var( 'token' );

		$opts = array(
			'headers' => array(
				EBOU_NL_BO_API_APIKEY_REFERER => $api_key
			)
		);

		$response = wp_remote_get( EBOU_NL_BO_API_UNSUBSCRIBE_URL . $follower_id . EBOU_NL_BO_API_UNSUBSCRIBE_URL_FROM_POSTFIX . $followerfile_id . '?token=' . $token, $opts );

		if( !is_wp_error( $response ) && $response['response']['code'] && $response['response']['code'] == 200 ) {
			?>
			
			<h3><?php _e( "Votre désinscription a bien été prise en compte", 'e-bourgogne-newsletter' ); ?></h3>
			<h4><?php _e( "Vous allez être redirigé vers l'accueil dans quelques secondes...", 'e-bourgogne-newsletter' ); ?></h4>
			<br />
			<p><?php echo sprintf( __( "Si la redirection ne fonctionne pas, cliquez %sici%s", 'e-bourgogne-newsletter' ), '<a href="' . get_home_url() . '"">', '</a>' ); ?></p>

			<script type="text/JavaScript">
				setTimeout("location.href = '<?php echo get_home_url(); ?>';", 4000);
			</script>
			<?php
		} else {
			?>
			<h3><?php _e( "Une erreur est survenue lors de votre désinscription, veuillez réessayer ultérieurement.", 'e-bourgogne-newsletter' ); ?></h3>
			<br />
			<p><a href="<?php echo get_home_url(); ?>"><?php _e( "Accueil", 'e-bourgogne-newsletter' ); ?></a></p>
			<?php
		}
		?>
		</div>
	</body>
</html>