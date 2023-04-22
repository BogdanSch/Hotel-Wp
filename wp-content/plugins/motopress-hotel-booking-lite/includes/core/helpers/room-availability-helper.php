<?php

namespace MPHB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class RoomAvailabilityHelper {

	private function __construct() {}

	public static function getActiveRoomsCountForRoomType( int $roomTypeOriginalId ) {

		return MPHB()->getRoomPersistence()->getCount(
			array(
				'room_type_id' => $roomTypeOriginalId,
				'post_status'  => 'publish',
			)
		);
	}

	public static function getAvailableRoomsCountForRoomType( int $roomTypeOriginalId, \DateTime $date ) {

		$availableRoomsCount = MPHB()->getCoreAPI()->getActiveRoomsCountForRoomType( $roomTypeOriginalId );
		$formattedDate       = $date->format( 'Y-m-d' );

		$bookedDays = MPHB()->getCoreAPI()->getBookedDaysForRoomType( $roomTypeOriginalId );

		if ( ! empty( $bookedDays['booked'][ $formattedDate ] ) ) {
			$availableRoomsCount = $availableRoomsCount - $bookedDays['booked'][ $formattedDate ];
		}

		$blokedRoomsCount = MPHB()->getCoreAPI()->getBlockedRoomsCountsForRoomType( $roomTypeOriginalId );

		if ( ! empty( $blokedRoomsCount[ $formattedDate ] ) ) {
			$availableRoomsCount = $availableRoomsCount - $blokedRoomsCount[ $formattedDate ];
		}
		return $availableRoomsCount;
	}

	/**
	 * @return string status
	 */
	public static function getRoomTypeAvailabilityStatus( int $roomTypeOriginalId, \DateTime $date ) {

		if ( $date < ( new \DateTime() )->setTime( 0, 0, 0 ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST;
		}

		$formattedDate = $date->format( 'Y-m-d' );

		if ( MPHB()->getCoreAPI()->isBookedDate( $roomTypeOriginalId, $date ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_BOOKED;
		}

		if ( ! MPHB()->getRulesChecker()->reservationRules()->verifyMinAdvanceReservationRule( $date, $date, $roomTypeOriginalId ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_EARLIER_MIN_ADVANCE;
		}

		if ( ! MPHB()->getRulesChecker()->reservationRules()->verifyMaxAdvanceReservationRule( $date, $date, $roomTypeOriginalId ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_LATER_MAX_ADVANCE;
		}

		$datesRates = MPHB()->getCoreAPI()->getDatesRatesForRoomType( $roomTypeOriginalId );

		if ( ! in_array( $formattedDate, $datesRates ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE;
		}

		if ( 0 >= static::getAvailableRoomsCountForRoomType( $roomTypeOriginalId, $date ) ) {
			return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE;
		}

		return RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_AVAILABLE;
	}


	/**
	 * @param $considerCheckIn - if true then check-in date considered as booked if there is no any available room
	 * @param $considerCheckOut - if true then check-out date considered as booked if there is no any available room
	 * @return true if given date is booked (there is no any available room)
	 */
	public static function isBookedDate( int $roomTypeOriginalId, \DateTime $date, $considerCheckIn = true, $considerCheckOut = false ) {

		$bookedDays       = MPHB()->getCoreAPI()->getBookedDaysForRoomType( $roomTypeOriginalId );
		$activeRoomsCount = MPHB()->getCoreAPI()->getActiveRoomsCountForRoomType( $roomTypeOriginalId );

		$formattedDate = $date->format( 'Y-m-d' );

		$isBookedDate = ( ! empty( $bookedDays['booked'][ $formattedDate ] ) &&
			$bookedDays['booked'][ $formattedDate ] >= $activeRoomsCount );

		if ( ! $considerCheckIn && ! empty( $bookedDays['check-ins'][ $formattedDate ] ) ) {
			$isBookedDate = false;
		}

		if ( $considerCheckOut && ! $isBookedDate ) {

			$dateBefore = clone $date;
			$dateBefore->modify( '-1 day' );
			$formattedDateBefore = $dateBefore->format( 'Y-m-d' );

			$isBookedDate = ( ! empty( $bookedDays['booked'][ $formattedDateBefore ] ) &&
				$bookedDays['booked'][ $formattedDateBefore ] >= $activeRoomsCount ) &&
				! empty( $bookedDays['check-outs'][ $formattedDate ] );
		}

		return $isBookedDate;
	}


	/**
	 * @return bool - true if check-in is not allowed in the given date
	 */
	public static function isCheckInNotAllowed( int $roomTypeOriginalId, \DateTime $date ) {

		$availabilityStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date );

		if ( RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_EARLIER_MIN_ADVANCE == $availabilityStatus ||
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_LATER_MAX_ADVANCE == $availabilityStatus ||
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE == $availabilityStatus ||
			RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST == $availabilityStatus ||
			MPHB()->getCoreAPI()->isBookedDate( $roomTypeOriginalId, $date ) ) {
			return false;
		}

		$reservationRules = MPHB()->getRulesChecker()->reservationRules();
		$customRules      = MPHB()->getRulesChecker()->customRules();

		$isCheckInNotAllowed = ! $customRules->verifyNotCheckInRestriction( $date, $date, $roomTypeOriginalId ) ||
			! $reservationRules->verifyCheckInDaysReservationRule( $date, $date, $roomTypeOriginalId );

		// check Not CheckIn before Not Stay In or Booked days
		if ( ! $isCheckInNotAllowed ) {

			$minStayNights = $reservationRules->getMinStayLengthReservationDaysCount( $date, $roomTypeOriginalId );

			if ( empty( $minStayNights ) ) {

				$minStayNights = 1;
			}

			$checkingDate    = clone $date;
			$nightsAfterDate = 0;

			do {

				$checkingDate->modify( '+1 day' );
				$nightsAfterDate++;

				$isCheckinDateNotAvailable = RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE ==
					MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $checkingDate );

				$isCheckingDateNotForCheckOut = ! $customRules->verifyNotCheckOutRestriction( $checkingDate, $checkingDate, $roomTypeOriginalId ) ||
					! $reservationRules->verifyCheckOutDaysReservationRule( $checkingDate, $checkingDate, $roomTypeOriginalId );

				$isCheckinDateNotForStayIn = MPHB()->getCoreAPI()->isStayInNotAllowed( $roomTypeOriginalId, $checkingDate, $checkingDate );

				$isCheckingDateBooked = MPHB()->getCoreAPI()->isBookedDate( $roomTypeOriginalId, $checkingDate );

				$isBookingNotAllowedInMinStayPeriod = $nightsAfterDate < $minStayNights &&
					( $isCheckinDateNotAvailable ||	$isCheckinDateNotForStayIn || $isCheckingDateBooked );

				$isCheckOutNotAllowedOnLastDayOfMinStayPeriod = $nightsAfterDate == $minStayNights &&
					$isCheckingDateNotForCheckOut &&
					( $isCheckinDateNotAvailable ||	$isCheckinDateNotForStayIn || $isCheckingDateBooked );

				if ( $isBookingNotAllowedInMinStayPeriod || $isCheckOutNotAllowedOnLastDayOfMinStayPeriod ) {

					$isCheckInNotAllowed = true;
					break;
				}
			} while ( $nightsAfterDate < $minStayNights );
		}

		return $isCheckInNotAllowed;
	}


	/**
	 * @return bool - true if check-out is not allowed in the given date
	 */
	public static function isCheckOutNotAllowed( int $roomTypeOriginalId, \DateTime $date ) {

		$availabilityStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date );

		if ( RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST == $availabilityStatus ||
			MPHB()->getCoreAPI()->isBookedDate( $roomTypeOriginalId, $date, false, true ) ) {
			return false;
		}

		$reservationRules = MPHB()->getRulesChecker()->reservationRules();
		$customRules      = MPHB()->getRulesChecker()->customRules();

		$isCheckOutNotAllowed = ! $customRules->verifyNotCheckOutRestriction( $date, $date, $roomTypeOriginalId ) ||
				! $reservationRules->verifyCheckOutDaysReservationRule( $date, $date, $roomTypeOriginalId );

		// check Not Check-out after Not Stay-in, Booked or Not Available days
		if ( ! $isCheckOutNotAllowed ) {

			$checkingDate     = clone $date;
			$nightsBeforeDate = 0;

			do {

				$checkingDate->modify( '-1 day' );
				$nightsBeforeDate++;

				$checkingDateStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $checkingDate );

				if ( MPHB()->getCoreAPI()->isStayInNotAllowed( $roomTypeOriginalId, $checkingDate, $checkingDate ) ||
					MPHB()->getCoreAPI()->isBookedDate( $roomTypeOriginalId, $checkingDate ) ||
					RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE == $checkingDateStatus ||
					RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST == $checkingDateStatus ) {

					$isCheckOutNotAllowed = true;
					break;
				}

				$minStayNights = $reservationRules->getMinStayLengthReservationDaysCount( $checkingDate, $roomTypeOriginalId );
				if ( empty( $minStayNights ) ) {

					$minStayNights = 1;
				}
			} while ( $nightsBeforeDate < $minStayNights );
		}

		return $isCheckOutNotAllowed;
	}


	public static function getRoomTypeAvailabilityData( int $roomTypeOriginalId, \DateTime $date ) {

		$availabilityStatus = MPHB()->getCoreAPI()->getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date );

		$result = null;

		if ( RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_PAST == $availabilityStatus ) {

			$result = new RoomTypeAvailabilityData( $availabilityStatus );

		} else {

			$availableRoomsCount = self::getAvailableRoomsCountForRoomType( $roomTypeOriginalId, $date );

			$bookedDays     = MPHB()->getCoreAPI()->getBookedDaysForRoomType( $roomTypeOriginalId );
			$formattedDate  = $date->format( 'Y-m-d' );
			$isCheckInDate  = ! empty( $bookedDays['check-ins'][ $formattedDate ] );
			$isСheckOutDate = ! empty( $bookedDays['check-outs'][ $formattedDate ] );

			$reservationRules = MPHB()->getRulesChecker()->reservationRules();

			$isStayInNotAllowed = MPHB()->getCoreAPI()->isStayInNotAllowed( $roomTypeOriginalId, $date, $date );

			$isEarlierThanMinAdvanceDate = ! $reservationRules->verifyMinAdvanceReservationRule( $date, $date, $roomTypeOriginalId );

			$isLaterThanMaxAdvanceDate = ! $reservationRules->verifyMaxAdvanceReservationRule( $date, $date, $roomTypeOriginalId );


			$minStayNights = $reservationRules->getMinStayLengthReservationDaysCount( $date, $roomTypeOriginalId );

			if ( empty($minStayNights) ) {
				$minStayNights = 1;
			}

			$maxStayNights = $reservationRules->getMaxStayLengthReservationDaysCount( $date, $roomTypeOriginalId );

			$result = new RoomTypeAvailabilityData(
				$availabilityStatus,
				$availableRoomsCount,
				$isCheckInDate,
				$isСheckOutDate,
				$isStayInNotAllowed,
				MPHB()->getCoreAPI()->isCheckInNotAllowed( $roomTypeOriginalId, $date ),
				MPHB()->getCoreAPI()->isCheckOutNotAllowed( $roomTypeOriginalId, $date ),
				$isEarlierThanMinAdvanceDate,
				$isLaterThanMaxAdvanceDate,
				$minStayNights,
				$maxStayNights
			);
		}

		return $result;
	}
}
