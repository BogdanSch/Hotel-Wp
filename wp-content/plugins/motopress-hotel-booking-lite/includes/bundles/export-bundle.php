<?php

namespace MPHB\Bundles;

/**
 * @since 3.5.0
 */
class ExportBundle
{
    /**
     * @return array
     *
     * @since 3.7.0 added new filter - "mphb_export_bookings_columns".
     */
    public function getBookingsExportColumns()
    {
        return apply_filters('mphb_export_bookings_columns', array(
            'booking-id'     => __('ID', 'motopress-hotel-booking'),
            'booking-status' => __('Status', 'motopress-hotel-booking'),
            'check-in'       => __('Check-in', 'motopress-hotel-booking'),
            'check-out'      => __('Check-out', 'motopress-hotel-booking'),
            'room-type'      => __('Accommodation Type', 'motopress-hotel-booking'),
            'room-type-id'   => __('Accommodation Type ID', 'motopress-hotel-booking'),
            'room'           => __('Accommodation', 'motopress-hotel-booking'),
            'rate'           => __('Rate', 'motopress-hotel-booking'),
            'adults'         => __('Adults/Guests', 'motopress-hotel-booking'),
            'children'       => __('Children', 'motopress-hotel-booking'),
            'services'       => __('Services', 'motopress-hotel-booking'),
            'first-name'     => __('First Name', 'motopress-hotel-booking'),
            'last-name'      => __('Last Name', 'motopress-hotel-booking'),
            'email'          => __('Email', 'motopress-hotel-booking'),
            'phone'          => __('Phone', 'motopress-hotel-booking'),
            'country'        => __('Country', 'motopress-hotel-booking'),
            'address'        => __('Address', 'motopress-hotel-booking'),
            'city'           => __('City', 'motopress-hotel-booking'),
            'state'          => __('State / County', 'motopress-hotel-booking'),
            'postcode'       => __('Postcode', 'motopress-hotel-booking'),
            'customer-note'  => __('Customer Note', 'motopress-hotel-booking'),
            'guest-name'     => __('Full Guest Name', 'motopress-hotel-booking'),
            'subtotal'       => __('Accommodation Subtotal', 'motopress-hotel-booking'),
            'taxes'          => __('Accommodation Taxes', 'motopress-hotel-booking'),
            'total-taxes'    => __('Total Accommodation Taxes', 'motopress-hotel-booking'),
            'fees'           => __('Fees', 'motopress-hotel-booking'),
            'total-fees'     => __('Total Fees', 'motopress-hotel-booking'),
            'subtotal-services' => __('Services Subtotal', 'motopress-hotel-booking'),
            'total-service-taxes' => __('Total Service Taxes', 'motopress-hotel-booking'),
            'total-fee-taxes' => __('Total Fee Taxes', 'motopress-hotel-booking'),
            'coupon'         => __('Coupon', 'motopress-hotel-booking'),
            'discount'       => __('Discount', 'motopress-hotel-booking'),
            'price'          => __('Total', 'motopress-hotel-booking'),
            'paid'           => __('Paid', 'motopress-hotel-booking'),
            'payments'       => __('Payment Details', 'motopress-hotel-booking'),
            'date'           => __('Date', 'motopress-hotel-booking')
        ));
    }
}
