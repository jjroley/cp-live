<?php
if ( ! $next_time = cp_live()->get_next_schedule() ) {
	return;
}

$start_value = $next_time - current_time( 'timestamp' ); 
?>
<div class="cp-live-countdown-container">
	<div class="cp-live-countdown" data-start-time="<?php echo $start_value; ?>">
		<div class="cp-live-countdown--element"><span class="days"></span><span class="cp-live-countdown--element--label"><?php _e( 'Days', 'cp-live' ); ?></span></div>
		<div class="cp-live-countdown--element"><span class="hours"></span><span class="cp-live-countdown--element--label"><?php _e( 'Hours', 'cp-live' ); ?></span></div>
		<div class="cp-live-countdown--element"><span class="minutes"></span><span class="cp-live-countdown--element--label"><?php _e( 'Minutes', 'cp-live' ); ?></span></div>
		<div class="cp-live-countdown--element"><span class="seconds"></span><span class="cp-live-countdown--element--label"><?php _e( 'Seconds', 'cp-live' ); ?></span></div>
	</div>
</div>
