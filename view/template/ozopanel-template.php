<?php
/*
 * Template Name: OzoPanel
 * Description: Template for OzoPanel
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php
		if ( is_user_logged_in() && apply_filters( 'ozopanel_template', current_user_can( 'administrator' ) ) ) {
			if ( ozopanel()->gate() ) {
				echo '<div id="ozopanel-dashboard"></div>';
			} else {
				ozopanel()->render( 'template/partial/403' );
			}
		} else {
			ozopanel()->render( 'template/partial/403' );
		}
    ?>
    <?php wp_footer(); ?>
</body>
</html>