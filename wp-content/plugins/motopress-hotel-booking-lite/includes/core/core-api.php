<?php

namespace MPHB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Facade of the Hotel Booking core. Any code outside the core must
 * use it instead of inner objects and functions. All object caching
 * must be placed here!
 */
class CoreAPI {

	const WP_CACHE_GROUP = 'MPHB';


	public function __construct() {

		add_action(
			'plugins_loaded',
			function() {
				$this->addClearObjectCacheHooks();
			}
		);
	}

	private function addClearObjectCacheHooks() {

		$hookNamesForClearAllCache = array(
			'mphb_booking_status_changed',
			'save_post_' . MPHB()->postTypes()->room()->getPostType(),
			'save_post_' . MPHB()->postTypes()->roomType()->getPostType(),
			'save_post_' . MPHB()->postTypes()->rate()->getPostType(),
			'update_option_mphb_check_in_days',
			'update_option_mphb_check_out_days',
			'update_option_mphb_min_stay_length',
			'update_option_mphb_max_stay_length',
			'update_option_mphb_booking_rules_custom',
			'update_option_mphb_min_advance_reservation',
			'update_option_mphb_max_advance_reservation',
			'update_option_mphb_buffer_days',
		);

		foreach ( $hookNamesForClearAllCache as $hookName ) {
			add_action(
				$hookName,
				function() {
					$this->deleteCachedData();
				}
			);
		}
	}

	private function getCacheKeysPrefix() {

		$prefix = wp_cache_get( 'cache_keys_prefix', static::WP_CACHE_GROUP );

		if ( ! $prefix ) {

			$prefix = time();
			wp_cache_set( 'cache_keys_prefix', $prefix, static::WP_CACHE_GROUP );
		}

		return $prefix;
	}

	private function deleteCachedData() {

		$prefix = time();
		wp_cache_set( 'cache_keys_prefix', $prefix, static::WP_CACHE_GROUP );
	}

	private function getCachedData( string $cacheDataId, &$isCachedDataWasFound = null ) {

		return wp_cache_get(
			$this->getCacheKeysPrefix() . '_' . $cacheDataId,
			static::WP_CACHE_GROUP,
			false,
			$isCachedDataWasFound
		);
	}

	private function setCachedData( string $cacheDataId, $data ) {

		wp_cache_set( $this->getCacheKeysPrefix() . '_' . $cacheDataId, $data, static::WP_CACHE_GROUP );
	}


	/**
	 * @return Entities\RoomType or null if nothing is found
	 */
	public function getRoomTypeById( int $roomTypeId ) {
		// we already have entities cache by id in repository!
		return MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
	}

	/**
	 * @return array with [
	 *      'booked' => [ 'Y-m-d' => rooms count, ... ],
	 *      'check-ins' => [ 'Y-m-d' => rooms count, ... ],
	 *      'check-outs' => [ 'Y-m-d' => rooms count, ... ],
	 * ]
	 */
	public function getBookedDaysForRoomType( int $roomTypeOriginalId ) {

		$result = $this->getCachedData( 'getBookedDaysForRoomType' . $roomTypeOriginalId );

		if ( ! $result ) {
			$result = MPHB()->getRoomRepository()->getBookedDays( $roomTypeOriginalId );
			$this->setCachedData( 'getBookedDaysForRoomType' . $roomTypeOriginalId, $result );
		}
		return $result;
	}

	public function getActiveRoomsCountForRoomType( int $roomTypeOriginalId ) {

		$result = $this->getCachedData( 'getActiveRoomsCountForRoomType' . $roomTypeOriginalId );

		if ( ! $result ) {
			$result = RoomAvailabilityHelper::getActiveRoomsCountForRoomType( $roomTypeOriginalId );
			$this->setCachedData( 'getActiveRoomsCountForRoomType' . $roomTypeOriginalId, $result );
		}
		return $result;
	}

	public function getBlockedRoomsCountsForRoomType( int $roomTypeOriginalId ) {

		$result = $this->getCachedData( 'getBlockedRoomsCountsForRoomType' . $roomTypeOriginalId );

		if ( ! $result ) {
			$result = MPHB()->getRulesChecker()->customRules()->getBlockedRoomsCounts( $roomTypeOriginalId );
			$this->setCachedData( 'getBlockedRoomsCountsForRoomType' . $roomTypeOriginalId, $result );
		}
		return $result;
	}

	/**
	 * @return \MPHB\Core\RoomTypeAvailabilityStatus constant
	 */
	public function getRoomTypeAvailabilityStatus( int $roomTypeOriginalId, \DateTime $date ) {

		$cacheDataId = 'getRoomTypeAvailabilityStatus' . $roomTypeOriginalId . '_' .
			$date->format( 'Y-m-d' );

		$result = $this->getCachedData( $cacheDataId );

		if ( ! $result ) {
			$result = RoomAvailabilityHelper::getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date );
			$this->setCachedData( $cacheDataId, $result );
		}
		return $result;
	}

	/**
	 * @param $considerCheckIn - if true then check-in date considered as booked if there is no any available room
	 * @param $considerCheckOut - if true then check-out date considered as booked if there is no any available room
	 * @return true if given date is booked (there is no any available room)
	 */
	public function isBookedDate( int $roomTypeOriginalId, \DateTime $date, $considerCheckIn = true, $considerCheckOut = false ) {

		$cacheDataId = 'isBookedDate' . $roomTypeOriginalId . '_' . $date->format( 'Y-m-d' ) . '_' .
			( $considerCheckIn ? '1' : '0' ) . '_' . ( $considerCheckOut ? '1' : '0' );

		$isFoundInCache = false;
		$result = $this->getCachedData( $cacheDataId, $isFoundInCache );

		if ( ! $isFoundInCache ) {
			$result = RoomAvailabilityHelper::isBookedDate( $roomTypeOriginalId, $date, $considerCheckIn, $considerCheckOut );
			$this->setCachedData( $cacheDataId, $result );
		}
		return $result;
	}

	/**
	 * @return bool - true if stay-in is not allowed in the given dates period
	 */
	public function isStayInNotAllowed( int $roomTypeOriginalId, \DateTime $checkInDate, \DateTime $checkOutDate ) {

		$cacheDataId = 'isStayInNotAllowed' . $roomTypeOriginalId . '_' . $checkInDate->format( 'Y-m-d' ) . '_' .
			$checkOutDate->format( 'Y-m-d' );

		$isFoundInCache = false;
		$result = $this->getCachedData( $cacheDataId, $isFoundInCache );

		if ( ! $isFoundInCache ) {
			$result = ! MPHB()->getRulesChecker()->customRules()->
				verifyNotStayInRestriction( $checkInDate, $checkOutDate, $roomTypeOriginalId );
				$this->setCachedData( $cacheDataId, $result );
		}
		return $result;
	}

	/**
	 * @return bool - true if check-in is not allowed in the given date
	 */
	public function isCheckInNotAllowed( int $roomTypeOriginalId, \DateTime $date ) {

		$cacheDataId = 'isCheckInNotAllowed' . $roomTypeOriginalId . '_' . $date->format( 'Y-m-d' );

		$isFoundInCache = false;
		$result = $this->getCachedData( $cacheDataId, $isFoundInCache );

		if ( ! $isFoundInCache ) {
			$result = RoomAvailabilityHelper::isCheckInNotAllowed( $roomTypeOriginalId, $date );
			$this->setCachedData( $cacheDataId, $result );
		}
		return $result;
	}

	/**
	 * @return bool - true if check-out is not allowed in the given date
	 */
	public function isCheckOutNotAllowed( int $roomTypeOriginalId, \DateTime $date ) {

		$cacheDataId = 'isCheckOutNotAllowed' . $roomTypeOriginalId . '_' . $date->format( 'Y-m-d' );

		$isFoundInCache = false;
		$result = $this->getCachedData( $cacheDataId, $isFoundInCache );

		if ( ! $isFoundInCache ) {
			$result = RoomAvailabilityHelper::isCheckOutNotAllowed( $roomTypeOriginalId, $date );
			$this->setCachedData( $cacheDataId, $result );
		}
		return $result;
	}

	/**
	 * @return \MPHB\Core\RoomTypeAvailabilityData
	 */
	public function getRoomTypeAvailabilityData( int $roomTypeOriginalId, \DateTime $date ) {

		return RoomAvailabilityHelper::getRoomTypeAvailabilityData( $roomTypeOriginalId, $date );
	}

	/**
	 * @return array with dates (string in format Y-m-d) which have rate
	 */
	public function getDatesRatesForRoomType( int $roomTypeOriginalId ) {

		$result = $this->getCachedData( 'getDatesRatesForRoomType' . $roomTypeOriginalId );

		if ( ! $result ) {
			$roomType = $this->getRoomTypeById( $roomTypeOriginalId );
			$result   = null != $roomType ? $roomType->getDatesHavePrice() : array();
			$this->setCachedData( 'getDatesRatesForRoomType' . $roomTypeOriginalId, $result );
		}
		return $result;
	}

	/**
	 * @return array with \MPHB\Entities\Rate
	 */
	public function getRoomTypeActiveRates( int $roomTypeOriginalId ) {

		$cacheDataId = 'getRoomTypeActiveRates' . $roomTypeOriginalId;

		$result = $this->getCachedData( $cacheDataId );

		if ( ! $result ) {
			$result = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomTypeOriginalId );
			$this->setCachedData( $cacheDataId, $result );
		}
		return $result;
	}


	/**
	 * @return float room type minimal price for min days stay with taxes and fees
	 * @throws Exception if booking is not allowed for given date
	 */
	public function getMinRoomTypeBasePriceForDate( int $roomTypeOriginalId, \DateTime $startDate ) {

		return mphb_get_room_type_base_price( $roomTypeOriginalId, $startDate, $startDate );
	}

	/**
	 * @param array $atts with:
	 * 'decimal_separator' => string,
	 * 'thousand_separator' => string,
	 * 'decimals' => int, Number of decimals
	 * 'is_truncate_price' => bool, false by default
	 * 'currency_position' => string, Possible values: after, before, after_space, before_space
	 * 'currency_symbol' => string,
	 * 'literal_free' => bool, Use "Free" text instead of 0 price.
	 * 'trim_zeros' => bool, true by default
	 * 'period' => bool,
	 * 'period_title' => '',
	 * 'period_nights' => 1,
	 * 'as_html' => bool, true by default
	 */
	public function formatPrice( float $price, array $atts = array() ) {
		return mphb_format_price( $price, $atts );
	}
}
