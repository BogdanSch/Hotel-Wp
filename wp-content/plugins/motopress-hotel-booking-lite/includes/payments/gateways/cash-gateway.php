<?php

namespace MPHB\Payments\Gateways;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class CashGateway extends Gateway {

	public function __construct(){
		add_filter( 'mphb_gateway_has_sandbox', array( $this, 'hideSandbox' ), 10, 2 );
		parent::__construct();
	}

	/**
	 * @param boolean $isShow
	 * @param string $gatewayId
	 * @return boolean
	 */
	public function hideSandbox( $isShow, $gatewayId ){
		if ( $gatewayId == $this->id ) {
			$isShow = false;
		}
		return $isShow;
	}

	protected function setupProperties(){
		parent::setupProperties();
		$this->adminTitle = __( 'Pay on Arrival', 'motopress-hotel-booking' );
	}

	protected function initDefaultOptions(){
		$defaults = array(
			'title'			 => __( 'Pay on Arrival', 'motopress-hotel-booking' ),
			'description'	 => __('Pay with cash on arrival.', 'motopress-hotel-booking'),
			'enabled'		 => false,
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId(){
		return 'cash';
	}

	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){
		$isHolded	 = $this->paymentOnHold( $payment );
		$redirectUrl = $isHolded ? MPHB()->settings()->pages()->getReservationReceivedPageUrl($payment) : MPHB()->settings()->pages()->getPaymentFailedPageUrl($payment);
		wp_redirect( $redirectUrl );
		exit;
	}

}
