<?php

namespace Database\Seeders;

use App\Models\TermsAndConditions;
use Illuminate\Database\Seeder;

class TermsAndConditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TermsAndConditions::create([
            'version' => '1.0',
            'is_active' => true,
            'is_required_agreement' => true,
            'effective_date' => now(),
            'en' => [
                'title' => 'Terms and Conditions',
                'content' => '<h1>Terms and Conditions</h1>
<p>Welcome to Hire-App. These terms and conditions outline the rules and regulations for the use of our car rental platform.</p>

<h2>1. Acceptance of Terms</h2>
<p>By accessing and using Hire-App, you accept and agree to be bound by the terms and conditions of this agreement.</p>

<h2>2. User Responsibilities</h2>
<p>Users must provide accurate information and comply with all applicable laws and regulations when using our service.</p>

<h2>3. Booking and Payment</h2>
<p>All bookings are subject to availability and confirmation. Payment must be made in full at the time of booking.</p>

<h2>4. Cancellation Policy</h2>
<p>Cancellation policies vary by rental shop. Please review specific terms before booking.</p>

<h2>5. Vehicle Usage</h2>
<p>Vehicles must be used responsibly and in accordance with local traffic laws. Any damage or violations may result in additional charges.</p>

<h2>6. Liability</h2>
<p>Hire-App acts as a platform connecting users with rental shops. We are not liable for actions of rental shops or users.</p>

<h2>7. Privacy Policy</h2>
<p>Your privacy is important to us. Please review our Privacy Policy for details on how we collect and use your information.</p>

<h2>8. Changes to Terms</h2>
<p>We reserve the right to modify these terms at any time. Users will be notified of significant changes.</p>

<p>Last updated: ' . now()->format('F j, Y') . '</p>',
            ],
            'ar' => [
                'title' => 'الشروط والأحكام',
                'content' => '<h1>الشروط والأحكام</h1>
<p>مرحباً بك في تطبيق Hire-App. تحدد هذه الشروط والأحكام القواعد واللوائح لاستخدام منصة تأجير السيارات الخاصة بنا.</p>

<h2>1. قبول الشروط</h2>
<p>من خلال الوصول إلى Hire-App واستخدامه، فإنك توافق على الالتزام بشروط وأحكام هذه الاتفاقية.</p>

<h2>2. مسؤوليات المستخدم</h2>
<p>يجب على المستخدمين تقديم معلومات دقيقة والامتثال لجميع القوانين واللوائح المعمول بها عند استخدام خدمتنا.</p>

<h2>3. الحجز والدفع</h2>
<p>جميع الحجوزات خاضعة للتوفر والتأكيد. يجب دفع المبلغ بالكامل وقت الحجز.</p>

<h2>4. سياسة الإلغاء</h2>
<p>تختلف سياسات الإلغاء حسب محل التأجير. يرجى مراجعة الشروط المحددة قبل الحجز.</p>

<h2>5. استخدام المركبة</h2>
<p>يجب استخدام المركبات بمسؤولية ووفقاً لقوانين المرور المحلية. قد يؤدي أي ضرر أو مخالفات إلى رسوم إضافية.</p>

<h2>6. المسؤولية</h2>
<p>يعمل Hire-App كمنصة تربط المستخدمين بمحلات التأجير. نحن غير مسؤولين عن أفعال محلات التأجير أو المستخدمين.</p>

<h2>7. سياسة الخصوصية</h2>
<p>خصوصيتك مهمة بالنسبة لنا. يرجى مراجعة سياسة الخصوصية للحصول على تفاصيل حول كيفية جمع واستخدام معلوماتك.</p>

<h2>8. التغييرات على الشروط</h2>
<p>نحتفظ بالحق في تعديل هذه الشروط في أي وقت. سيتم إخطار المستخدمين بالتغييرات المهمة.</p>

<p>آخر تحديث: ' . now()->format('j F Y') . '</p>',
            ],
        ]);
    }
}
