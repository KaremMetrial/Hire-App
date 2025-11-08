<?php


    return [
        // Common
        'page_not_found' => 'الصفحة غير موجودة.',
        'record_not_found' => 'السجل غير موجود.',
        'method_not_allowed' => 'طريقة الطلب غير مسموحة.',
        'validation_failed' => 'فشل التحقق من البيانات.',
        'unauthorized_access' => 'غير مصرح لك بالدخول.',
        'access_forbidden' => 'الوصول مرفوض.',
        'access_denied' => 'تم رفض الوصول.',
        'rate_limit_exceeded' => 'عدد كبير من الطلبات، يرجى المحاولة لاحقاً.',
        'database_error' => 'حدث خطأ في قاعدة البيانات.',
        'unexpected_error' => 'حدث خطأ غير متوقع، يرجى المحاولة لاحقاً.',
        'success' => 'تمت العملية بنجاح.',


        // OTP
        'otp.not_found' => 'OTP غير موجود.',
        'otp.expired' => 'OTP انتهت.',
        'otp.invalid' => 'OTP غير صحيح.',
        'otp.sent' => 'OTP تم ارساله بنجاح.',
        'otp.verified' => 'تم التحقق من OTP بنجاح.',

        // Auth
        'auth' => [
            'register' => 'تم التسجيل بنجاح.',
            'login' => 'تم تسجيل الدخول بنجاح.',
            'logout' => 'تم تسجيل الخروج بنجاح.',
            'password_reset' => 'تم تغيير كلمة المرور بنجاح.',
            'pre_register_success' => 'تم التسجيل المسبق بنجاح.',
            'invalid_credentials' => 'بيانات الدخول غير صحيحة.',
            'user_not_found' => 'المستخدم غير موجود.',
        ],

        // Working Day
        'working_day.created' => 'تم إنشاء يوم العمل بنجاح.',
        'working_day.updated' => 'تم تحديث يوم العمل بنجاح.',
        'working_day.index' => 'تم استرجاع أيام العمل بنجاح.',

        // Registration
        'registration.pre_registration_not_found' => 'سجل التسجيل المسبق غير موجود.',
        'registration.pre_registration_expired' => 'انتهت صلاحية التسجيل المسبق.',
        'registration.session_security_validation_failed' => 'فشل التحقق من أمان الجلسة. يرجى المحاولة مرة أخرى.',
        'registration.phone_already_in_pre_registration' => 'رقم الهاتف هذا موجود بالفعل في التسجيل المسبق.',
        'registration.email_already_in_pre_registration' => 'عنوان البريد الإلكتروني هذا موجود بالفعل في التسجيل المسبق.',

        // Password Reset
        'password_reset' => 'تم تغيير كلمة المرور بنجاح.',

        // Booking Status Notifications
        'booking_status_notification' => [
            'subject_user' => 'تم تحديث حالة الحجز #:booking_number الخاص بك',
            'subject_vendor' => 'تم تحديث حالة الحجز #:booking_number',
            'greeting' => 'مرحباً :name!',
            'status_changed' => 'تم تحديث حالة الحجز الخاص بك من :old_status إلى :new_status.',
            'vendor_status_changed' => 'تم تغيير حالة الحجز من :old_status إلى :new_status.',
            'booking_details' => 'تفاصيل الحجز:',
            'car' => 'السيارة: :car_name',
            'pickup_date' => 'تاريخ الاستلام: :pickup_date',
            'return_date' => 'تاريخ الإرجاع: :return_date',
            'view_booking' => 'عرض الحجز',
            'view_booking_details' => 'عرض تفاصيل الحجز',
            'thank_you' => 'شكراً لاستخدامك خدماتنا!',
        ],

        // Notifications
        'notifications' => [
            'marked_all_as_read' => 'تم تحديد جميع الإشعارات كمقروءة',
            'deleted' => 'تم حذف الإشعار بنجاح',
            'default_message' => 'لديك إشعار جديد',
            'booking_status' => [
                'user_updated' => 'تم تحديث حالة الحجز #:booking_number من :old_status إلى :new_status',
                'vendor_updated' => 'تم تغيير حالة الحجز #:booking_number من :old_status إلى :new_status',
            ],
            'booking_overdue' => 'حجزك #:booking_number متأخر',
            'review_request' => 'يرجى ترك تقييم لحجزك #:booking_number',
            'otp_sent' => 'تم إرسال رمز OTP إلى هاتفك',
            'titles' => [
                'booking_status_updated' => 'بانتظار مراجعة المكتب لتأكيد حالة السيارة. ستنتهي المراجعة خلال 24 ساعة تلقائيًا إذا لم يتم اتخاذ إجراء.',
                'booking_overdue' => 'حجزك متأخر. يرجى إرجاع المركبة فوراً.',
                'review_request' => 'يرجى مشاركة تجربتك من خلال ترك تقييم لحجزك الأخير.',
                'otp_sent' => 'تم إرسال رمز OTP إلى رقم هاتفك.',
                'default' => 'لديك إشعار جديد.',
            ],
            'descriptions' => [
                'default' => 'لديك إشعار جديد.',
                'booking_status' => [
                    'user_updated' => 'تم تغيير حالة حجزك #:booking_number من :old_status إلى :new_status. يرجى التحقق من تفاصيل الحجز للمزيد من المعلومات.',
                    'vendor_updated' => 'تم تحديث حالة الحجز #:booking_number من :old_status إلى :new_status. يرجى مراجعة تفاصيل الحجز.',
                ],
                'booking_overdue' => 'حجزك #:booking_number متأخر الآن. يرجى إرجاع المركبة في أقرب وقت ممكن لتجنب الرسوم الإضافية.',
                'review_request' => 'نود أن نشكرك إذا كنت تستطيع مشاركة تجربتك من خلال ترك تقييم لحجزك الأخير #:booking_number.',
            ],
        ],
    ];
