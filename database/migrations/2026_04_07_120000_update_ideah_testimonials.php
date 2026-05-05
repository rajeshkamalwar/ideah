<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace demo testimonials with IDEAH member stories (per language).
     */
    public function up(): void
    {
        $en = Language::query()->whereRaw('LOWER(code) = ?', ['en'])->first()
            ?? Language::query()->where('is_default', 1)->first();

        if ($en) {
            DB::table('testimonial_sections')->where('language_id', $en->id)->update([
                'title' => 'What Our Members Say About IDEAH',
                'subtitle' => null,
                'clients' => null,
                'updated_at' => now(),
            ]);

            DB::table('testimonials')->where('language_id', $en->id)->delete();

            foreach ($this->englishTestimonials() as $row) {
                DB::table('testimonials')->insert(array_merge($row, [
                    'language_id' => $en->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $ar = Language::query()->whereRaw('LOWER(code) = ?', ['ar'])->first();
        if ($ar) {
            DB::table('testimonial_sections')->where('language_id', $ar->id)->update([
                'title' => 'ماذا يقول أعضاؤنا عن IDEAH',
                'subtitle' => null,
                'clients' => null,
                'updated_at' => now(),
            ]);

            DB::table('testimonials')->where('language_id', $ar->id)->delete();

            foreach ($this->arabicTestimonials() as $row) {
                DB::table('testimonials')->insert(array_merge($row, [
                    'language_id' => $ar->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function englishTestimonials(): array
    {
        return [
            [
                'image' => '663c43857a830.png',
                'name' => 'Rahul Sharma',
                'occupation' => 'Founder, Export Startup, New Delhi',
                'comment' => 'IDEAH helped us understand exactly how to enter the European market from India. The mentorship, the network, and the guidance on company registration in the Netherlands were truly world-class.',
                'rating' => '5',
            ],
            [
                'image' => '663c437f271c3.png',
                'name' => 'Priya Mehta',
                'occupation' => 'Business Consultant, Mumbai',
                'comment' => 'We were struggling with quality lead generation in India for years. After joining IDEAH, our client pipeline completely transformed within just 3 months.',
                'rating' => '5',
            ],
            [
                'image' => '663c437896de6.png',
                'name' => 'Arjun Kapoor',
                'occupation' => 'Co-Founder, Trade Tech Startup, Pune',
                'comment' => 'IDEAH\'s cross-border business solutions and import-export consulting services gave our startup the confidence and tools to go global. Highly recommended!',
                'rating' => '5',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function arabicTestimonials(): array
    {
        return [
            [
                'image' => '663c43a9b4af0.png',
                'name' => 'راهول شارما',
                'occupation' => 'مؤسس، شركة ناشئة للتصدير، نيودلهي',
                'comment' => 'ساعدنا IDEAH على فهم كيفية الدخول إلى السوق الأوروبي من الهند. كانت الإرشاد والشبكة والدعم في تسجيل الشركة في هولندا بمستوى عالمي حقاً.',
                'rating' => '5',
            ],
            [
                'image' => '663c43a137eac.png',
                'name' => 'بريا ميهتا',
                'occupation' => 'مستشارة أعمال، مومباي',
                'comment' => 'عانينا لسنوات من ضعف جودة العملاء المحتملين في الهند. بعد الانضمام إلى IDEAH، تحول مسار عملائنا بالكامل خلال 3 أشهر فقط.',
                'rating' => '5',
            ],
            [
                'image' => '663c439ad8654.png',
                'name' => 'أرجون كابور',
                'occupation' => 'المؤسس المشارك، شركة ناشئة لتكنولوجيا التجارة، بوني',
                'comment' => 'منحتنا حلول IDEAH للأعمال عبر الحدود وخدمات الاستشارات في الاستيراد والتصدير الثقة والأدوات لننطلق عالمياً. أنصح بها بشدة!',
                'rating' => '5',
            ],
        ];
    }

    public function down(): void
    {
        //
    }
};
