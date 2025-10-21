<?php

namespace Database\Seeders;

use App\Models\ExtraService;
use Illuminate\Database\Seeder;

class ExtraServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'is_active' => true,
                'en' => [
                    'name' => 'GPS Navigation System',
                    'description' => 'Advanced GPS navigation system with real-time traffic updates and points of interest.'
                ],
                'ar' => [
                    'name' => 'نظام تحديد المواقع العالمي',
                    'description' => 'نظام تحديد مواقع متقدم مع تحديثات حركة المرور في الوقت الفعلي ونقاط الاهتمام.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Child Safety Seat',
                    'description' => 'Infant or child safety seat suitable for different age groups and weights.'
                ],
                'ar' => [
                    'name' => 'مقعد أمان للأطفال',
                    'description' => 'مقعد أمان للرضع أو الأطفال مناسب لمختلف الفئات العمرية والأوزان.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Additional Driver',
                    'description' => 'Add an additional authorized driver to the rental agreement.'
                ],
                'ar' => [
                    'name' => 'سائق إضافي',
                    'description' => 'إضافة سائق إضافي مصرح له إلى عقد الإيجار.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Wi-Fi Hotspot',
                    'description' => 'Portable Wi-Fi device for internet connectivity during your trip.'
                ],
                'ar' => [
                    'name' => 'نقطة اتصال Wi-Fi',
                    'description' => 'جهاز Wi-Fi محمول للاتصال بالإنترنت خلال رحلتك.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Booster Seat',
                    'description' => 'Booster seat for older children who have outgrown child seats.'
                ],
                'ar' => [
                    'name' => 'مقعد معزز',
                    'description' => 'مقعد معزز للأطفال الأكبر سناً الذين تجاوزوا مقاعد الأطفال.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Snow Chains',
                    'description' => 'Snow chains for enhanced traction in winter conditions.'
                ],
                'ar' => [
                    'name' => 'سلاسل الثلج',
                    'description' => 'سلاسل ثلج لتحسين الجر في الظروف الشتوية.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Roof Rack',
                    'description' => 'Roof rack for additional luggage or sports equipment storage.'
                ],
                'ar' => [
                    'name' => 'حامل سقف',
                    'description' => 'حامل سقف لتخزين الأمتعة الإضافية أو المعدات الرياضية.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Emergency Roadside Kit',
                    'description' => 'Complete emergency kit including first aid, tools, and warning triangle.'
                ],
                'ar' => [
                    'name' => 'طقم الطوارئ على الطريق',
                    'description' => 'طقم طوارئ كامل يشمل الإسعافات الأولية والأدوات ومثلث التحذير.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Premium Sound System',
                    'description' => 'Upgraded audio system with enhanced speakers and subwoofer.'
                ],
                'ar' => [
                    'name' => 'نظام صوتي مميز',
                    'description' => 'نظام صوتي محسن مع مكبرات صوت ووفر صوت محسنة.'
                ],
            ],
            [
                'is_active' => true,
                'en' => [
                    'name' => 'Mobile Phone Holder',
                    'description' => 'Adjustable mobile phone holder for safe navigation and hands-free calls.'
                ],
                'ar' => [
                    'name' => 'حامل الهاتف المحمول',
                    'description' => 'حامل هاتف محمول قابل للتعديل للملاحة الآمنة والمكالمات اليدوية الحرة.'
                ],
            ],
        ];

        foreach ($services as $service) {
            $serviceModel = ExtraService::create([
                'is_active' => $service['is_active'],
            ]);

            $serviceModel->translateOrNew('en')->name = $service['en']['name'];
            $serviceModel->translateOrNew('en')->description = $service['en']['description'];
            $serviceModel->translateOrNew('ar')->name = $service['ar']['name'];
            $serviceModel->translateOrNew('ar')->description = $service['ar']['description'];
            $serviceModel->save();
        }
    }
}
