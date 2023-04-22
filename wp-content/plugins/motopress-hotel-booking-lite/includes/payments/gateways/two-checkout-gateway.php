<?php

namespace MPHB\Payments\Gateways;

class TwoCheckoutGateway implements GatewayInterface
{
	public function __construct(){
		add_action( 'mphb_init_gateways', array( $this, 'register' ) );
	}

	/**
	 *
	 * @param \MPHB\Payments\Gateways\GatewayManager $gatewayManager
	 */
	public function register( GatewayManager $gatewayManager ){
		$gatewayManager->addGateway( $this );
	}

	public function getId(){
		return '2checkout';
	}

	public function getTitle(){
		return __( '2Checkout', 'motopress-hotel-booking' );
	}

	public function getAdminTitle(){
		return __( '2Checkout', 'motopress-hotel-booking' );
	}

	public function getAdminDescription(){
		return sprintf( __( '<a href="%s">Upgrade to Premium</a> version to enable this payment gateway for online bookings.', 'motopress-hotel-booking' ),
			esc_url( admin_url( 'admin.php?page=mphb_premium' ) ) );
	}

	/**
	 *
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 */
	public function registerOptionsFields( &$subTab ){
	}

	public function isEnabled(){
		return false;
	}

	public function isActive(){
		return false;
	}

	public function isShowOptions(){
		return true;
	}

    /**
     * @since 3.7.0
     */
    public function getInstructions()
    {
        return '';
    }
}
