<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Driver License'],
                'ar' => ['name' => 'رخصة القيادة'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'National ID'],
                'ar' => ['name' => 'الهوية الوطنية'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Passport'],
                'ar' => ['name' => 'جواز السفر'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Iqama (Residence Permit)'],
                'ar' => ['name' => 'الإقامة'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Credit Card'],
                'ar' => ['name' => 'بطاقة الائتمان'],
            ],
            [
                'input_type' => 'text',
                'is_active' => true,
                'en' => ['name' => 'Company Registration Number'],
                'ar' => ['name' => 'رقم السجل التجاري'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Commercial Registration'],
                'ar' => ['name' => 'السجل التجاري'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Student ID Card'],
                'ar' => ['name' => 'بطاقة الطالب'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Employee ID Card'],
                'ar' => ['name' => 'بطاقة الموظف'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'International Driving Permit'],
                'ar' => ['name' => 'رخصة القيادة الدولية'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Visa'],
                'ar' => ['name' => 'التأشيرة'],
            ],
            [
                'input_type' => 'text',
                'is_active' => true,
                'en' => ['name' => 'Hotel Booking Confirmation'],
                'ar' => ['name' => 'تأكيد حجز الفندق'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Return Flight Ticket'],
                'ar' => ['name' => 'تذكرة الطيران العودة'],
            ],
            [
                'input_type' => 'file',
                'is_active' => true,
                'en' => ['name' => 'Bank Statement'],
                'ar' => ['name' => 'كشف حساب بنكي'],
            ],
            [
                'input_type' => 'text',
                'is_active' => true,
                'en' => ['name' => 'Local Reference Contact'],
                'ar' => ['name' => 'رقم مرجع محلي'],
            ],
        ];

        foreach ($documents as $document) {
            $documentModel = Document::create([
                'input_type' => $document['input_type'],
                'is_active' => $document['is_active'],
            ]);

            $documentModel->translateOrNew('en')->name = $document['en']['name'];
            $documentModel->translateOrNew('ar')->name = $document['ar']['name'];
            $documentModel->save();
        }
    }
}
