<?php

use App\Models\Footer\FooterContent;
use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align basic contact fields and footer copy with the public About Us / IDEAH details.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('basic_settings', 'kvk_number')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->string('kvk_number', 32)->nullable()->after('address');
            });
        }

        DB::table('basic_settings')->orderBy('id')->limit(1)->update([
            'email_address' => 'info@ideahhub.com',
            'contact_number' => '+31 6 50683989',
            'address' => 'KRINGLOOP 167, 1186GW Amstelveen, Netherlands',
            'kvk_number' => '70995265',
            'latitude' => '52.3049',
            'longitude' => '4.8719',
            'contact_title' => 'Get in touch',
            'contact_subtile' => 'We would love to hear from you',
            'contact_details' => 'IDEAH is a global business network connecting Indo-Dutch, Asian, and Surinamese entrepreneurs through partnerships, mentorship, and international trade. Registered in the Netherlands.',
            'updated_at' => now(),
        ]);

        $aboutEn = 'IDEAH is a global business network connecting Indo-Dutch, Asian, and Surinamese entrepreneurs through partnerships, mentorship, and international trade.';

        $aboutAr = 'IDEAH شبكة أعمال تربط رواد الأعمال الهنديين الهولنديين والآسيويين والسوريناميين عبر الشراكات والإرشاد والتجارة الدولية.';

        foreach (FooterContent::query()->cursor() as $row) {
            $lang = Language::query()->find($row->language_id);
            $code = strtolower((string) ($lang->code ?? ''));
            $row->about_company = ($code === 'ar') ? $aboutAr : $aboutEn;
            $row->save();
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('basic_settings', 'kvk_number')) {
            Schema::table('basic_settings', function (Blueprint $table) {
                $table->dropColumn('kvk_number');
            });
        }
    }
};
