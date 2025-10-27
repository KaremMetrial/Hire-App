<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the booking system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | The tax rate applied to bookings. This should be a decimal value.
    | For example: 0.10 for 10% tax, 0.15 for 15% tax, etc.
    |
    */
    'tax_rate' => env('BOOKING_TAX_RATE', 0.10),

    /*
    |--------------------------------------------------------------------------
    | Cancellation Policy
    |--------------------------------------------------------------------------
    |
    | Cancellation fee percentages based on hours before pickup.
    |
    */
    'cancellation_fees' => [
        'less_than_24_hours' => 0.50,  // 50% fee
        'less_than_72_hours' => 0.25,  // 25% fee
        'default' => 0.00,             // No fee
    ],

    /*
    |--------------------------------------------------------------------------
    | Mileage Settings
    |--------------------------------------------------------------------------
    |
    | Default mileage limits and rates.
    |
    */
    'mileage' => [
        'daily_limit' => 200,           // Default daily mileage limit
        'extra_rate' => 0.50,           // Rate per extra mile/km
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Limits
    |--------------------------------------------------------------------------
    |
    | System-wide booking limits and restrictions.
    |
    */
    'limits' => [
        'max_rental_days' => 365,       // Maximum rental period in days
        'max_documents' => 10,          // Maximum documents per booking
        'max_extra_services' => 20,     // Maximum extra services per booking
    ],
];
