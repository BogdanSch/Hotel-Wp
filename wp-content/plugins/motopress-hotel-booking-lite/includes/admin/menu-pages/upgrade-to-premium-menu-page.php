<?php

namespace MPHB\Admin\MenuPages;

class UpgradeToPremiumMenuPage extends AbstractMenuPage {

	public function render(){
		?>
		<div class="wrap">
			<h1 class="wp-title-inline"><?php esc_html_e( 'Upgrade to Hotel Booking Premium', 'motopress-hotel-booking' ); ?></h1>

			<hr class="wp-header-end" />

			<div class="mphb-upgrade-to-premium">
				<p>
					<a class="button button-primary"
					   href="https://motopress.com/products/hotel-booking/?utm_source=hotel-booking-lite&utm_medium=button-in-dashboard"
					   target="_blank"><?php
						esc_html_e( 'Go Premium', 'motopress-hotel-booking' ); ?></a>
				</p>
				<div class="notice notice-info inline">
					<p><?php
						
						printf(
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							__( 'Want to see and test the PRO features first-hand on the backend? %3$s %1$s Start a free trial %2$s with the PRO plugin version. %4$s', 'motopress-hotel-booking' ),
							sprintf(
								'<a href="%s" target="blank">',
								'https://hbdemo.getmotopress.com/?utm_source=hotel-booking-lite&utm_medium=button-in-dashboard'
							),
							'</a>',
							'<strong>',
							'</strong>'
						);
					?></p>
				</div>
				<p>
					<?php esc_html_e( 'Take full advantage of Hotel Booking with the premium plugin version. Compare the features:', 'motopress-hotel-booking' ); ?>
				</p>

				<table class="widefat striped mphb-comparison-table">
					<thead>
					<tr>
						<th class="mphb-column-title"></th>
						<th class="mphb-column-lite"><strong><?php esc_html_e( 'Lite Version', 'motopress-hotel-booking' ); ?></strong></th>
						<th class="mphb-column-pro"><strong><?php esc_html_e( 'Pro Version', 'motopress-hotel-booking' ); ?></strong></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Technical support', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'Priority support via live chat, email, and ticket system.', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><?php esc_html_e( 'FAQ', 'motopress-hotel-booking' ); ?></td>
						<td class="mphb-column-pro"><?php esc_html_e( 'Email, tickets system (we even login to your WordPress to help)', 'motopress-hotel-booking' ); ?></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Built-in payment gateways', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'Already integrated payment gateways for secure online payments and bookings.', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><?php esc_html_e( 'PayPal', 'motopress-hotel-booking' ); ?></td>
						<td class="mphb-column-pro"><?php esc_html_e( 'PayPal, 2Checkout, Braintree, Stripe, Beanstream/Bambora, more on the way', 'motopress-hotel-booking' ); ?></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Extendable with Hotel Booking addons', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'The facility to connect any extra free or premium Hotel Booking extension.', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
						<td class="mphb-column-pro"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Rates based on the number of guests', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'Increase or decrease property price based on how many guests stay in.', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><span class="mphb-icon-no dashicons dashicons-no-alt"></span></td>
						<td class="mphb-column-pro"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Rates based on the length of stay', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'Increase or decrease property price based on the period guests stay in (weekly, monthly or any custom period).', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><span class="mphb-icon-no dashicons dashicons-no-alt"></span></td>
						<td class="mphb-column-pro"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e('Bookings synchronization to OTAs (e.g., Airbnb)', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'Automatically or manually sync bookings to OTAs like Booking.com or Airbnb to have up-to-date property availability across all channels.', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><span class="mphb-icon-no dashicons dashicons-no-alt"></span></td>
						<td class="mphb-column-pro"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Adding bookings manually by site admins', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'Register new guests manually via the site admin dashboard.', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><span class="mphb-icon-no dashicons dashicons-no-alt"></span></td>
						<td class="mphb-column-pro"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Editable original booking details', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e( 'Manually update original booking information (e.g., arrival/departure dates, booked accommodations/services).', 'motopress-hotel-booking' ); ?></p>
						</td>
						<td class="mphb-column-lite"><span class="mphb-icon-no dashicons dashicons-no-alt"></span></td>
						<td class="mphb-column-pro"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td class="mphb-column-title">
							<span class="mphb-title"><?php esc_html_e( 'Custom filters for the property search form', 'motopress-hotel-booking' ); ?></span>
							<p class="description"><?php esc_html_e('Add any custom search filters (e.g., location, room type) to improve the property search for guests.', 'motopress-hotel-booking'); ?></p>
						</td>
						<td class="mphb-column-lite"><span class="mphb-icon-no dashicons dashicons-no-alt"></span></td>
						<td class="mphb-column-pro"><span class="mphb-icon-yes dashicons dashicons-yes"></span></td>
					</tr>
					</tbody>
				</table>

				<p>
					<a class="button button-primary"
					   	href="https://motopress.com/products/hotel-booking/?utm_source=hotel-booking-lite&utm_medium=button-in-dashboard"
					   	target="_blank"><?php
						esc_html_e('Go Premium', 'motopress-hotel-booking'); ?></a>
				</p>

			</div>

		</div>
		<?php
	}

	public function onLoad(){
	}

	protected function getMenuTitle(){
		return '<span class="dashicons dashicons-superhero-alt" style="font-size:17px;vertical-align:middle;"></span> ' .
			__( 'Go Premium', 'motopress-hotel-booking' );
	}

	protected function getPageTitle(){
		return __( 'Go Premium', 'motopress-hotel-booking' );
	}

	public function addActions(){
		parent::addActions();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		add_action( 'admin_footer', array( $this, 'adminFooter' ) );
	}

	public function enqueueScripts(){
		if ($this->isCurrentPage()) {
			wp_enqueue_style( 'mphb-admin-css' );
		}
	}

	public function adminFooter() {
	?>
		<style>#adminmenu #toplevel_page_mphb_booking_menu li:not(.current) a[href="admin.php?page=mphb_premium"] {color: #F8C130;}</style>
	<?php
	}

}
