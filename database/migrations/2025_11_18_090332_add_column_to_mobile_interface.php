<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('mail_templates', function (Blueprint $table) {

      DB::table('mail_templates')->insert([
        'mail_type' => 'verify_email_app',
        'mail_subject' => 'Verify Your Email Address',
        'mail_body' => "<p>Dear <strong>{username}</strong>,</p>
                        <p>We just need to verify your email address before you can access to your dashboard.</p>
                        <p>
                        Verification Code:
                         <strong>
                            {verification_code}
                          </strong>
                        </p>
                        <p>Thank you.<br>{website_title}</p>",
      ]);

      DB::table('mail_templates')->insert([
        'mail_type' => 'reset_password_app',
        'mail_subject' => 'Recover Password of Your Account',
        'mail_body' => "
          <p>Hi {username},</p>
          <p>We have received a request to reset your password. If you did not make the request, ignore this email. Otherwise, you can reset your password using the below link.</p>
           <p>
           Verification Code:
            <strong>
            {verification_code}
            </strong>
           </p>
            <p>
            Thanks,<br />{website_title}
          </p>",
      ]);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('mail_templates', function (Blueprint $table) {
      //
    });
  }
};
