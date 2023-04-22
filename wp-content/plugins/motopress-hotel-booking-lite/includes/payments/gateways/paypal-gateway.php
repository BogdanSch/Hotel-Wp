<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;

class PaypalGateway extends Gateway {

	/**
     * @var Paypal\IpnListener
     */
	protected $ipnListener;

	private $supportedCurrencies;

	/**
	 * @var string
	 */
	protected $businessEmail;

	public function __construct(){
        add_filter( 'mphb_gateway_has_instructions', array( $this, 'hideInstructions' ), 10, 2 );

		$this->setupSupportedCurrencies();

		parent::__construct();

		$this->setupNotificationListener();
	}

    /**
     * @param bool $show
     * @param string $gatewayId
     * @return bool
     *
     * @since 3.6.1
     */
    public function hideInstructions( $show, $gatewayId ){
        if ( $gatewayId == $this->id ) {
            $show = false;
        }
        return $show;
    }

	protected function setupNotificationListener(){
		$ipnListnerArgs		 = array(
			'gatewayId'				 => $this->getId(),
			'sandbox'				 => $this->isSandbox,
			'verificationDisabled'	 => (bool) $this->getOption( 'disable_ipn_verification' ),
			'businessEmail'			 => $this->businessEmail
		);
		$this->ipnListener	 = new Paypal\IpnListener( $ipnListnerArgs );
	}

	protected function setupProperties(){
		parent::setupProperties();
		$this->adminTitle	 = __( 'PayPal', 'motopress-hotel-booking' );
		$this->businessEmail = sanitize_email( $this->getOption( 'business_email' ) );

		if ( $this->isSandbox ) {
			$this->description .= ' ' . sprintf( __( 'Use the card number %1$s with CVC %2$s and a valid expiration date to test a payment.', 'motopress-hotel-booking' ), '5555555555554444', '123' );
			$this->description = trim( $this->description );
		}
	}

	protected function initDefaultOptions(){
		$defaults = array(
			'title'						 => __( 'PayPal', 'motopress-hotel-booking' ),
			'description'				 => __( 'Pay via PayPal', 'motopress-hotel-booking' ),
			'enabled'					 => false,
			'is_sandbox'				 => false,
			'business_email'			 => '',
			'disable_ipn_verification'	 => false
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId(){
		return 'paypal';
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive(){
		return parent::isActive() && $this->isSupportCurrency();
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){

		$url = $this->getPaymentUrl( $booking, $payment );

		// Redirect to paypal checkout
		wp_redirect( $url );
		exit;
	}

	/**
	 * Get the PayPal request URL for an booking.
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return string
	 */
	public function getPaymentUrl( $booking, $payment ){
		$paypalArgs = http_build_query( $this->getRequestArgs( $booking, $payment ), '', '&' );

		if ( $this->isSandbox ) {
			$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?test_ipn=1&' . $paypalArgs;
		} else {
			$url = 'https://www.paypal.com/cgi-bin/webscr?' . $paypalArgs;
		}

		return $url;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return string
	 */
	public function getRequestArgs( $booking, $payment ){

		$args = array(
			'cmd'			 => '_xclick',
			'business'		 => $this->businessEmail,
			'currency_code'	 => $payment->getCurrency(),
			'charset'		 => 'utf-8',
			'rm'			 => 2, // Return method 1 - GET, 2 - POST
			'notify_url'	 => $this->ipnListener->getNotifyUrl(),
			'return'		 => esc_url_raw( MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment, array( 'mphb_payment_status' => 'auto' ) ) ),
			'cancel_return'	 => esc_url_raw( MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment ) ),
			'bn'			 => 'MPHB_BuyNow', //  build notation
			'invoice'		 => $payment->getKey(),
			'custom'		 => $payment->getId(),
			'cbt'			 => get_bloginfo( 'name' ), // Return to Merchant button text
			'no_shipping'	 => '1', // Do not prompt buyers for a shipping address.
			'no_note'		 => '1', // Do not prompt buyers to include a note // Deprecated
		);

		$args = array_merge( $args, $this->getBillingInfoArgs( $booking ), $this->getItemArgs( $booking, $payment ) );

		return $args;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @return array
	 */
	private function getBillingInfoArgs( $booking ){

		$fields = array(
			'country'	 => $booking->getCustomer()->getCountry(), // needs 2-character IS0-3166-1 country codes not free field
//			'state'		 => $booking->getCustomer()->getState(), // needs 2-character state codes
			'city'		 => $booking->getCustomer()->getCity(),
			'address1'	 => $booking->getCustomer()->getAddress1(),
			'zip'		 => $booking->getCustomer()->getZip(),
			'email'		 => $booking->getCustomer()->getEmail(),
			'first_name' => $booking->getCustomer()->getFirstName(),
			'last_name'	 => $booking->getCustomer()->getLastName(),
		);

		// remove empty fields
		$fields = array_filter( $fields );

		return $fields;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return array
     *
     * @since 3.6.1 added new parameter - $payment.
	 */
	public function getItemArgs( $booking, $payment ){
		$itemName = $this->generateItemName( $booking );

		return array(
			'item_name'	 => $itemName,
			'amount'	 => $payment->getAmount()
		);
	}

	/**
	 *
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 */
	public function registerOptionsFields( &$subTab ){
		parent::registerOptionsFields( $subTab );
		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group2", '', $subTab->getOptionGroupName() );

		$groupFields = array(
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_business_email", array(
				'type'		 => 'email',
				'label'		 => __( 'Paypal Business Email', 'motopress-hotel-booking' ),
				'default'	 => $this->getDefaultOption( 'business_email' )
			) ),
			Fields\FieldFactory::create( "mphb_payment_gateway_{$this->id}_disable_ipn_verification", array(
				'type'			 => 'checkbox',
				'inner_label'	 => __( 'Disable IPN Verification', 'motopress-hotel-booking' ),
				'default'		 => $this->getDefaultOption( 'disable_ipn_verification' ),
				'description'	 => __( 'Specify an IPN listener for a specific payment instead of the listeners specified in your PayPal Profile.', 'motopress-hotel-booking' ),
			) ),
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );
	}

	/**
	 *
	 * @return string
	 */
	public function getBusinessEmail(){
		return $this->businessEmail;
	}

	private function setupSupportedCurrencies() {
		$supportedCurrencies		 = include('paypal/supported-currencies.php');
		$supportedCurrencies		 = apply_filters( 'mphb_paypal_supported_currencies', $supportedCurrencies );
		$this->supportedCurrencies	 = $supportedCurrencies;
	}

	public function isSupportCurrency(){
		return in_array( MPHB()->settings()->currency()->getCurrencyCode(), $this->supportedCurrencies );
	}

}
