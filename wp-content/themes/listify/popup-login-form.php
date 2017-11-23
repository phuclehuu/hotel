<?php
/**
 * The template for displaying a login popup form.
 * This form is loaded in footer for non-logged-in user for easy access.
 *
 * @package Listify
 * @since 2.3.0
 * @version 2.3.0
 */
?>

<div id="listify-login-popup" class="popup">

	<h2 class="popup-title"><?php echo esc_html( get_theme_mod( 'content-login-title', __( 'Login', 'listify' ) ) ); ?></h2>

	<?php listify_login_form(); ?>

</div>
