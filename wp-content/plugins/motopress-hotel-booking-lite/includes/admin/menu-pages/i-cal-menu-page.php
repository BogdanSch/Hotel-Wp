<?php

namespace MPHB\Admin\MenuPages;

class iCalMenuPage extends AbstractMenuPage {

	public function render(){
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Sync, Import and Export Calendars', 'motopress-hotel-booking' ); ?></h1>

			<hr class="wp-header-end" />

			<p><?php esc_html_e( 'Sync your bookings across all online channels like Booking.com, TripAdvisor, Airbnb etc. via iCalendar file format.', 'motopress-hotel-booking' ); ?></p>
			<?php 
			
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo mphb_upgrade_to_premium_message( 'div' ); 
			
			?>
		</div>
		<?php
	}

	public function onLoad(){
	}

	protected function getMenuTitle(){
		return __( 'Sync Calendars', 'motopress-hotel-booking' );
	}

	protected function getPageTitle(){
		return __( 'Sync Calendars', 'motopress-hotel-booking' );
	}

}
