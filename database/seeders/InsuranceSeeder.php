<?php

namespace Database\Seeders;

use App\Models\Insurance;
use Illuminate\Database\Seeder;

class InsuranceSeeder extends Seeder
{
    public function run(): void
    {
        $insurances = [
            [
                'period' => 'day',
                'price' => 15.00,
                'deposit_price' => 500.00,
                'is_required' => false,
                'is_active' => true,
                'en' => [
                    'title' => 'Basic Daily Insurance',
                    'description' => 'Basic coverage including collision damage waiver and third-party liability for day rentals.'
                ],
                'ar' => [
                    'title' => 'تأمين أساسي يومي',
                    'description' => 'تغطية أساسية تشمل إعفاء أضرار التصادم والمسؤولية تجاه الغرف للإيجارات اليومية.'
                ],
            ],
            [
                'period' => 'day',
                'price' => 25.00,
                'deposit_price' => 300.00,
                'is_required' => false,
                'is_active' => true,
                'en' => [
                    'title' => 'Premium Daily Insurance',
                    'description' => 'Comprehensive coverage with zero deductible, including theft protection and personal accident coverage.'
                ],
                'ar' => [
                    'title' => 'تأمين مميز يومي',
                    'description' => 'تغطية شاملة مع خصم صفري، بما في ذلك الحماية من السرقة وتغطية الحوادث الشخصية.'
                ],
            ],
            [
                'period' => 'week',
                'price' => 85.00,
                'deposit_price' => 500.00,
                'is_required' => false,
                'is_active' => true,
                'en' => [
                    'title' => 'Weekly Coverage Plan',
                    'description' => 'Extended coverage for weekly rentals with reduced day rates and enhanced protection.'
                ],
                'ar' => [
                    'title' => 'خطة التغطية الأسبوعية',
                    'description' => 'تغطية موسعة للإيجارات الأسبوعية بأسعار يومية مخفضة وحماية محسنة.'
                ],
            ],
            [
                'period' => 'month',
                'price' => 280.00,
                'deposit_price' => 300.00,
                'is_required' => false,
                'is_active' => true,
                'en' => [
                    'title' => 'Monthly Comprehensive Plan',
                    'description' => 'Complete protection package for long-term rentals with maximum coverage and minimum deposit.'
                ],
                'ar' => [
                    'title' => 'خطة شاملة شهرية',
                    'description' => 'حزمة حماية كاملة للإيجارات طويلة الأجل بأقصى تغطية وأقل وديعة.'
                ],
            ],
            [
                'period' => 'day',
                'price' => 8.00,
                'deposit_price' => 1000.00,
                'is_required' => true,
                'is_active' => true,
                'en' => [
                    'title' => 'Mandatory Third-Party Insurance',
                    'description' => 'Required by law - covers third-party property damage and bodily injury.'
                ],
                'ar' => [
                    'title' => 'تأمين الطرف الثالث الإلزامي',
                    'description' => 'مطلوب بموجب القانون - يغطي أضرار ممتلكات الغرف والإصابات الجسدية.'
                ],
            ],
            [
                'period' => 'day',
                'price' => 12.00,
                'deposit_price' => 750.00,
                'is_required' => false,
                'is_active' => true,
                'en' => [
                    'title' => 'Personal Accident Insurance',
                    'description' => 'Covers medical expenses and accidental death for driver and passengers.'
                ],
                'ar' => [
                    'title' => 'تأمين الحوادث الشخصية',
                    'description' => 'يغطي النفقات الطبية والوفاة العرضية للسائق والركاب.'
                ],
            ],
            [
                'period' => 'day',
                'price' => 5.00,
                'deposit_price' => 500.00,
                'is_required' => false,
                'is_active' => true,
                'en' => [
                    'title' => 'Theft Protection',
                    'description' => 'Reduces liability in case of vehicle theft or attempted theft.'
                ],
                'ar' => [
                    'title' => 'الحماية من السرقة',
                    'description' => 'يقلل من المسؤولية في حالة سرقة المركبة أو محاولة السرقة.'
                ],
            ],
            [
                'period' => 'day',
                'price' => 18.00,
                'deposit_price' => 200.00,
                'is_required' => false,
                'is_active' => true,
                'en' => [
                    'title' => 'Super Coverage',
                    'description' => 'All-inclusive package with zero deductible, GPS tracking, and 24/7 roadside assistance.'
                ],
                'ar' => [
                    'title' => 'تغطية فائقة',
                    'description' => 'حزمة شاملة بخصم صفري وتتبع GPS ومساعدة على الطريق على مدار الساعة طوال أيام الأسبوع.'
                ],
            ],
        ];

        foreach ($insurances as $insurance) {
            $insuranceModel = Insurance::create([
                'period' => $insurance['period'],
                'price' => $insurance['price'],
                'deposit_price' => $insurance['deposit_price'],
                'is_required' => $insurance['is_required'],
                'is_active' => $insurance['is_active'],
            ]);

            $insuranceModel->translateOrNew('en')->title = $insurance['en']['title'];
            $insuranceModel->translateOrNew('en')->description = $insurance['en']['description'];
            $insuranceModel->translateOrNew('ar')->title = $insurance['ar']['title'];
            $insuranceModel->translateOrNew('ar')->description = $insurance['ar']['description'];
            $insuranceModel->save();
        }
    }
}
