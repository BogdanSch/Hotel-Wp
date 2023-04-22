<?php

namespace MPHB\Payments\Gateways;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class TestGateway extends Gateway {

    public function __construct(){
        add_filter( 'mphb_gateway_has_instructions', array( $this, 'hideInstructions' ), 10, 2 );
        parent::__construct();
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

	protected function setupProperties(){
		parent::setupProperties();
		$this->adminTitle = __( 'Test Payment', 'motopress-hotel-booking' );
	}

	protected function initDefaultOptions(){
		$defaults = array(
			'title'			 => __( 'Test Payment', 'motopress-hotel-booking' ),
			'description'	 => '',
			'enabled'		 => false,
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId(){
		return 'test';
	}

	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ){
		$isComplete	 = $this->paymentCompleted( $payment );
		$redirectUrl = $isComplete ? MPHB()->settings()->pages()->getReservationReceivedPageUrl($payment) : MPHB()->settings()->pages()->getPaymentFailedPageUrl($payment);
		wp_redirect( $redirectUrl );
		exit;
	}

}
