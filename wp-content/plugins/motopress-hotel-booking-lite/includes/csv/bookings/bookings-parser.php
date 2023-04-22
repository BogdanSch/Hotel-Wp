<?php

namespace MPHB\CSV\Bookings;

use MPHB\PostTypes\PaymentCPT\Statuses as PaymentStatuses;

/**
 * @since 3.5.0
 */
class BookingsParser {

	/**
	 * @var int
	 * @since 3.9.8
	 */
	public $count;

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @param array                       $columns Column names.
	 * @param int                         $count Count interations of rooms.
	 *
	 * @return array [Column name => Column value]
	 *
	 * @since 3.7.0 removed the filter "mphb_export_bookings_parse_column".
	 * @since 3.7.0 added new filter "mphb_export_bookings_parse_columns".
	 */
	public function parseColumns( $booking, $room, $columns, $count = 0 ) {

		$this->count = $count;

		// Generage empty values for each column first
		$values = array_fill( 0, count( $columns ), '' );
		$values = array_combine( $columns, $values );

		// Parse values
		foreach ( $columns as $column ) {            // "room-type-id"

			$parts = explode( '-', $column );        // ["room", "type", "id"]
			$parts = array_map( 'ucfirst', $parts ); // ["Room", "Type", "Id"]

			$method = 'parse' . implode( '', $parts ); // "parseRoomTypeId"

			if ( method_exists( $this, $method ) ) {

				$values[ $column ] = $this->$method( $booking, $room );
                
			} else {

				$values[ $column ] = '';
			}
		}

		return apply_filters( 'mphb_export_bookings_parse_columns', $values, $booking, $room );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return int
	 */
	protected function parseBookingId( $booking, $room ) {

		return $booking->getId();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseBookingStatus( $booking, $room ) {

		$status = $booking->getStatus();
		return mphb_get_status_label( $status );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string Check-in date in current format.
	 */
	protected function parseCheckIn( $booking, $room ) {

		return $booking->getCheckInDate()->format( MPHB()->settings()->dateTime()->getDateFormat() );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string Check-out date in current format.
	 */
	protected function parseCheckOut( $booking, $room ) {

		return $booking->getCheckOutDate()->format( MPHB()->settings()->dateTime()->getDateFormat() );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string Room type name.
	 */
	protected function parseRoomType( $booking, $room ) {

		$roomTypeId = $this->parseRoomTypeId( $booking, $room );
		$roomType   = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

		return ! is_null( $roomType ) ? $roomType->getTitle() : '';
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return int Room type ID.
	 */
	protected function parseRoomTypeId( $booking, $room ) {

		$roomTypeId = $room->getRoomTypeId();
		$roomTypeId = MPHB()->translation()->getOriginalId( $roomTypeId, MPHB()->postTypes()->roomType()->getPostType() );

		return $roomTypeId;
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string Reserved room name (physical accommodation).
	 */
	protected function parseRoom( $booking, $room ) {

		$roomId = $room->getRoomId();
		$roomId = MPHB()->translation()->getOriginalId( $roomId, MPHB()->postTypes()->room()->getPostType() );

		$accommodation = MPHB()->getRoomRepository()->findById( $roomId );

		return ! is_null( $accommodation ) ? $accommodation->getTitle() : '';
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string Rate name.
	 */
	protected function parseRate( $booking, $room ) {

		$rateId = $room->getRateId();
		$rateId = MPHB()->translation()->getOriginalId( $rateId, MPHB()->postTypes()->rate()->getPostType() );

		$rate = MPHB()->getRateRepository()->findById( $rateId );

		return ! is_null( $rate ) ? $rate->getTitle() : '';
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return int
	 */
	protected function parseAdults( $booking, $room ) {

		return $room->getAdults();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return int
	 */
	protected function parseChildren( $booking, $room ) {

		return $room->getChildren();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseServices( $booking, $room ) {

		$reservedServices = $room->getReservedServices();

		if ( empty( $reservedServices ) ) {
			return '';
		}

		$services = array();

		foreach ( $reservedServices as $reservedService ) {
			$reservedService = MPHB()->translation()->translateReservedService( $reservedService );

			$service = html_entity_decode( $reservedService->getTitle() );

			if ( $reservedService->isPayPerAdult() ) {
				$service .= ' ' . sprintf( _n( 'x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking' ), $reservedService->getAdults() );
			}

			if ( $reservedService->isFlexiblePay() ) {
				$service .= ' ' . sprintf( _n( 'x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking' ), $reservedService->getQuantity() );
			}

			$services[] = $service;
		}

		return implode( ', ', $services );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseFirstName( $booking, $room ) {

		return $booking->getCustomer()->getFirstName();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseLastName( $booking, $room ) {

		return $booking->getCustomer()->getLastName();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseEmail( $booking, $room ) {

		return $booking->getCustomer()->getEmail();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parsePhone( $booking, $room ) {

		return $booking->getCustomer()->getPhone();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseCountry( $booking, $room ) {

		return $booking->getCustomer()->getCountry();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseAddress( $booking, $room ) {

		return $booking->getCustomer()->getAddress1();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseCity( $booking, $room ) {

		return $booking->getCustomer()->getCity();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseState( $booking, $room ) {

		return $booking->getCustomer()->getState();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parsePostcode( $booking, $room ) {

		return $booking->getCustomer()->getZip();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseCustomerNote( $booking, $room ) {

		return $booking->getNote();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseGuestName( $booking, $room ) {

		return $room->getGuestName();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseCoupon( $booking, $room ) {

		return $booking->getCouponCode();
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseDiscount( $booking, $room ) {

		$roomPriceBreakdown = $this->getRoomPriceBreakdown( $booking, $room );

		$discount = $roomPriceBreakdown['total'] - $roomPriceBreakdown['discount_total'];

		$price = mphb_format_price(
			$discount,
			array(
				'as_html'            => false,
				'thousand_separator' => '',
			)
		);
		return html_entity_decode( $price ); // Decode #&36; into $
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseSubtotal( $booking, $room ) {

		$priceBreakdown = $booking->getLastPriceBreakdown();
		$subtotal       = 0;

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {
			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {
				if ( isset( $roomBreakdown['room']['total'] ) && ! empty( $roomBreakdown['room']['total'] ) ) {
					$subtotal += $roomBreakdown['room']['total'];
				}
			}
		}

		return html_entity_decode(
			mphb_format_price(
				$subtotal,
				array(
					'as_html'            => false,
					'thousand_separator' => '',
				)
			)
		);
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parsePrice( $booking, $room ) {

		$roomPriceBreakdown = $this->getRoomPriceBreakdown( $booking, $room );

		$price = mphb_format_price(
			$roomPriceBreakdown['discount_total'],
			array(
				'as_html'            => false,
				'thousand_separator' => '',
			)
		);
		return html_entity_decode( $price ); // Decode #&36; into $
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return array
	 */
	private function getRoomPriceBreakdown( $booking, $room ) {

		$coupon = null;

		if ( MPHB()->settings()->main()->isCouponsEnabled() && $booking->getCouponId() ) {

			$coupon = MPHB()->getCouponRepository()->findById( $booking->getCouponId() );

			if ( ! $coupon || ! $coupon->validate( $booking ) ) {

				$coupon = null;
			}
		}

		return $room->getPriceBreakdown( $booking->getCheckInDate(), $booking->getCheckOutDate(), $coupon, $booking->getLanguage() );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 *
	 * @since 3.9.8
	 */
	protected function parseTaxes( $booking, $room ) {

		$taxText        = array();
		$priceBreakdown = $booking->getLastPriceBreakdown();

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {

			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {

				if ( $key == $this->count ) {

					if ( isset( $roomBreakdown['taxes']['room']['list'] ) && ! empty( $roomBreakdown['taxes']['room']['list'] ) ) {

						foreach ( $roomBreakdown['taxes']['room']['list'] as $roomTax ) {

							$tax       = html_entity_decode(
								mphb_format_price(
									$roomTax['price'],
									array(
										'as_html' => false,
										'thousand_separator' => '',
									)
								)
							);

							$taxLabel  = $roomTax['label'];
							$taxText[] = "{$tax},{$taxLabel}";
						}
					}
					break;
				}
			}
		}

		return ! empty( $taxText ) ? implode( ';', $taxText ) : '';
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 *
	 * @since 3.9.8
	 */
	protected function parseTotalTaxes( $booking, $room ) {

		$taxText        = '';
		$priceBreakdown = $booking->getLastPriceBreakdown();

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {

			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {

				if ( $key == $this->count ) {

					if ( isset( $roomBreakdown['taxes']['room']['total'] ) && $roomBreakdown['taxes']['room']['total'] > 0 ) {

						$taxText = html_entity_decode(
							mphb_format_price(
								$roomBreakdown['taxes']['room']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;
				}
			}
		}

		return $taxText;
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 *
	 * @since 3.9.8
	 */
	protected function parseFees( $booking, $room ) {

		$taxText        = array();
		$priceBreakdown = $booking->getLastPriceBreakdown();

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {

			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {

				if ( $key == $this->count ) {

					if ( isset( $roomBreakdown['fees']['list'] ) && ! empty( $roomBreakdown['fees']['list'] ) ) {

						foreach ( $roomBreakdown['fees']['list'] as $roomTax ) {
							$tax       = html_entity_decode(
								mphb_format_price(
									$roomTax['price'],
									array(
										'as_html' => false,
										'thousand_separator' => '',
									)
								)
							);

							$taxLabel  = $roomTax['label'];
							$taxText[] = "{$tax},{$taxLabel}";
						}
					}
					break;
				}
			}
		}

		return implode( ';', $taxText );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 *
	 * @since 3.9.8
	 */
	protected function parseTotalFees( $booking, $room ) {

		$taxText        = '';
		$priceBreakdown = $booking->getLastPriceBreakdown();

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {

			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {

				if ( $key == $this->count ) {

					if ( isset( $roomBreakdown['fees']['total'] ) && $roomBreakdown['fees']['total'] > 0 ) {

						$taxText = html_entity_decode(
							mphb_format_price(
								$roomBreakdown['fees']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;
				}
			}
		}

		return $taxText;
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseSubtotalServices( $booking, $room ) {

		$priceBreakdown = $booking->getLastPriceBreakdown();
		$totalServices  = 0;

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {

			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {

				if ( isset( $roomBreakdown['services']['total'] ) && $roomBreakdown['services']['total'] > 0 ) {

					$totalServices += $roomBreakdown['services']['total'];
				}
			}
		}

		return html_entity_decode(
			mphb_format_price(
				$totalServices,
				array(
					'as_html'            => false,
					'thousand_separator' => '',
				)
			)
		);
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 *
	 * @since 3.9.8
	 */
	protected function parseTotalServiceTaxes( $booking, $room ) {

		$taxText = '';

		$priceBreakdown = $booking->getLastPriceBreakdown();

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {

			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {

				if ( $key == $this->count ) {

					if ( isset( $roomBreakdown['taxes']['services']['total'] ) && $roomBreakdown['taxes']['services']['total'] > 0 ) {

						$taxText = html_entity_decode(
							mphb_format_price(
								$roomBreakdown['taxes']['services']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;
				}
			}
		}

		return $taxText;
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 *
	 * @since 3.9.8
	 */
	protected function parseTotalFeeTaxes( $booking, $room ) {

		$taxText = '';

		$priceBreakdown = $booking->getLastPriceBreakdown();

		if ( isset( $priceBreakdown['rooms'] ) && ! empty( $priceBreakdown['rooms'] ) ) {

			foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) {

				if ( $key == $this->count ) {

					if ( isset( $roomBreakdown['taxes']['fees']['total'] ) && $roomBreakdown['taxes']['fees']['total'] > 0 ) {

						$taxText = html_entity_decode(
							mphb_format_price(
								$roomBreakdown['taxes']['fees']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;
				}
			}
		}

		return $taxText;
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parsePaid( $booking, $room ) {

		$payments    = MPHB()->getPaymentRepository()->findAll( array( 'booking_id' => $booking->getId() ) );
		$bookingPaid = 0.0;

		foreach ( $payments as $payment ) {
			if ( PaymentStatuses::STATUS_COMPLETED == $payment->getStatus() ) {

				$bookingPaid += $payment->getAmount();
			}
		}

		$roomPriceBreakdown    = $this->getRoomPriceBreakdown( $booking, $room );
		$bookingPriceBreakdown = $booking->getLastPriceBreakdown();

		if ( 0 < $bookingPriceBreakdown['total'] ) {

			$roomPaid = $roomPriceBreakdown['discount_total'] / $bookingPriceBreakdown['total'] * $bookingPaid;

		} else {

			$roomPaid = 0;
		}

		return html_entity_decode(
			mphb_format_price(
				$roomPaid,
				array(
					'as_html'            => false,
					'thousand_separator' => '',
				)
			)
		);
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parsePayments( $booking, $room ) {

		$payments       = MPHB()->getPaymentRepository()->findAll( array( 'booking_id' => $booking->getId() ) );
		$paymentStrings = array();

		foreach ( $payments as $payment ) {

			$id      = $payment->getId();
			$status  = mphb_get_status_label( $payment->getStatus() );
			$amount  = html_entity_decode(
				mphb_format_price(
					$payment->getAmount(),
					array(
						'as_html'            => false,
						'thousand_separator' => '',
					)
				)
			);

			$gateway = MPHB()->gatewayManager()->getGateway( $payment->getGatewayId() );
			$method  = ! is_null( $gateway ) ? $gateway->getAdminTitle() : $payment->getGatewayId();

			$paymentStrings[] = "#{$id},{$status},{$amount},{$method}";
		}

		return implode( ';', $paymentStrings );
	}

	/**
	 * @param \MPHB\Entities\Booking      $booking
	 * @param \MPHB\Entities\ReservedRoom $room
	 * @return string
	 */
	protected function parseDate( $booking, $room ) {

		return get_the_date( MPHB()->settings()->dateTime()->getDateFormat() . ' H:i:s', $booking->getId() );
	}
}
