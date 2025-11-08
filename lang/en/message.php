<?php


    return [
        // Common
        'page_not_found' => 'Page not found.',
        'record_not_found' => 'Record not found.',
        'method_not_allowed' => 'Method not allowed.',
        'validation_failed' => 'Validation failed.',
        'unauthorized_access' => 'Unauthorized access.',
        'access_forbidden' => 'Access forbidden.',
        'access_denied' => 'Access denied.',
        'rate_limit_exceeded' => 'Too many requests, please try again later.',
        'database_error' => 'A database error occurred.',
        'unexpected_error' => 'An unexpected error occurred. Please try again later.',
        'success' => 'Success.',

        // OTP
        'otp.not_found' => 'OTP not found.',
        'otp.expired' => 'OTP expired.',
        'otp.invalid' => 'OTP invalid.',
        'otp.sent' => 'OTP sent successfully.',
        'otp.verified' => 'OTP verified successfully.',

        // Auth
        'auth' => [
            'register' => 'Register successfully.',
            'login' => 'Login successfully.',
            'logout' => 'Logout successfully.',
            'password_reset' => 'Password reset successfully.',
            'pre_register_success' => 'Pre-registration successful.',
            'invalid_credentials' => 'Invalid credentials.',
            'user_not_found' => 'User not found.',
        ],

        // Working Day
        'working_day.created' => 'Working day has been created successfully.',
        'working_day.updated' => 'Working day has been updated successfully.',
        'working_day.index' => 'Working days retrieved successfully.',

        // Registration
        'registration.pre_registration_not_found' => 'Pre-registration record not found.',
        'registration.pre_registration_expired' => 'Pre-registration has expired.',
        'registration.session_security_validation_failed' => 'Session security validation failed. Please try again.',
        'registration.phone_already_in_pre_registration' => 'This phone number is already in pre-registration.',
        'registration.email_already_in_pre_registration' => 'This email address is already in pre-registration.',

        // Password Reset
        'password_reset' => 'Password reset successfully.',

        // Booking Status Notifications
        'booking_status_notification' => [
            'subject_user' => 'Your Booking #:booking_number Status Updated',
            'subject_vendor' => 'Booking #:booking_number Status Updated',
            'greeting' => 'Hello :name!',
            'status_changed' => 'Your booking status has been updated from :old_status to :new_status.',
            'vendor_status_changed' => 'Booking status changed from :old_status to :new_status.',
            'booking_details' => 'Booking Details:',
            'car' => 'Car: :car_name',
            'pickup_date' => 'Pickup Date: :pickup_date',
            'return_date' => 'Return Date: :return_date',
            'view_booking' => 'View Booking',
            'view_booking_details' => 'View Booking Details',
            'thank_you' => 'Thank you for using our service!',
        ],

        // Notifications
        'notifications' => [
            'marked_all_as_read' => 'All notifications marked as read',
            'deleted' => 'Notification deleted successfully',
            'default_message' => 'You have a new notification',
            'booking_status' => [
                'user_updated' => 'Your booking #:booking_number status has been updated from :old_status to :new_status',
                'vendor_updated' => 'Booking #:booking_number status changed from :old_status to :new_status',
            ],
            'booking_overdue' => 'Your booking #:booking_number is overdue',
            'review_request' => 'Please leave a review for your booking #:booking_number',
            'otp_sent' => 'OTP code has been sent to your phone',
            'titles' => [
                'booking_status_updated' => 'Waiting for office review to confirm car status. Review will end automatically within 24 hours if no action is taken.',
                'booking_overdue' => 'Your booking is overdue. Please return the vehicle immediately.',
                'review_request' => 'Please share your experience by leaving a review for your recent booking.',
                'otp_sent' => 'OTP code has been sent to your phone number.',
                'default' => 'You have a new notification.',
            ],
            'descriptions' => [
                'default' => 'You have a new notification.',
                'booking_status' => [
                    'user_updated' => 'Your booking #:booking_number has changed status from :old_status to :new_status. Please check your booking details for more information.',
                    'vendor_updated' => 'Booking #:booking_number status has been updated from :old_status to :new_status. Please review the booking details.',
                ],
                'booking_overdue' => 'Your booking #:booking_number is now overdue. Please return the vehicle as soon as possible to avoid additional charges.',
                'review_request' => 'We would appreciate it if you could share your experience by leaving a review for your recent booking #:booking_number.',
            ],
        ],
];
