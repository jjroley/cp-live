<?php
use CP_Live\Admin\Settings;
use CP_Live\Templates;

$is_live          = cp_live()->is_live();
$embed            = cp_live()->get_live_embed();
$not_live_display = Settings::get( 'not_live_display', 'most_recent' );
?>
<div class="cp-live-container">
	<?php
	if ( $is_live ) {
		echo $embed;
	} else {
		switch( Settings::get( 'not_live_display', 'most_recent' ) ) {
			case 'most_recent' :
				echo $embed;
				break;
			case 'countdown' :
				Templates::get_template_part( 'elements/countdown' );
				break;
		}
	}
	?>
	<?php if ( $is_live ) : ?>
		<?php echo $embed; ?>
	<?php endif; ?>
</div>