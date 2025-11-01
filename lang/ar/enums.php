<?php

return [
    'vendor_status' => [
        'pending' => 'قيد الانتظار',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض',
    ],
    'rental_shop_status' => [
        'pending' => 'قيد الانتظار',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض',
    ],
    'insurance_period' => [
        'day' => 'يوم',
        'week' => 'أسبوع',
        'month' => 'شهر',
    ],
    'delivery_option_type' => [
        'office' => 'استلام فى المكتب',
        'custom' => 'توصيل من العميل',
    ],
    'required_document' => [
        'text' => 'نص',
        'file' => 'ملف',
    ],
    'car_image' => [
        'front' => 'الأمامية',
        'back' => 'الخلفية',
        'left' => 'اليسرى',
        'right' => 'اليمنى',
        'inside' => 'الداخلية',
        'other' => 'أخرى',
    ],
    'input_type' => [
        'text' => 'نص',
        'file' => 'ملف',
    ],
    'booking_status' => [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'انتظار الاستلام',
        'active' => 'قيد الايجار',
        'under_delivery' => 'قيد التسليم',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        'rejected' => 'مرفوض',
        'info_requested' => 'طلب معلومات',
        'accident_reported' => 'بلاغ عن حادث',
    'extension_requested' => 'طلب تمديد',
    'unreasonable_delay' => 'تأخير غير مبرر',
    'under_dispute' => 'تحت النزاع',
    ],
    'payment_status' => [
        'unpaid' => 'غير مدفوع',
        'partially_paid' => 'مدفوع جزئياً',
        'paid' => 'مدفوع',
        'refunded' => 'مسترد',
    ],
    'duration' => [
        'days_and_hours' => ':days يوم(أيام) و :hours ساعة(ساعات)',
        'days_only' => ':days يوم(أيام)',
        'hours_only' => ':hours ساعة(ساعات)',
        'less_than_hour' => 'أقل من ساعة',
    ],
    'payment_method' => [
        'cash' => 'نقدي',
        'card' => 'بطاقة',
        'bank_transfer' => 'تحويل بنكي',
        'online' => 'أونلاين',
    ],
    'information_request_fields' => [
        'license_number' => 'رقم الرخصة',
        'license_expiry_date' => 'تاريخ انتهاء الرخصة',
        'face_license_id_photo' => 'صورة الرخصة (الوجه)',
        'back_license_id_photo' => 'صورة الرخصة (الخلف)',
        'nationality' => 'الجنسية',
        'emergency_contact_name' => 'اسم جهة الاتصال في حالات الطوارئ',
        'emergency_contact_phone' => 'رقم هاتف جهة الاتصال في حالات الطوارئ',
        'driving_experience_years' => 'سنوات الخبرة في القيادة',
    ],
    'procedure_types' => [
        'pickup' => 'إجراء الاستلام',
        'return' => 'إجراء الإرجاع',
    ],
];
