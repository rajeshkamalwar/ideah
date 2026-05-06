<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Http\Helpers\AdminNotificationEmails;
use App\Http\Requests\MailFromUserRequest;
use App\Models\BasicSettings\Basic;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class ContactController extends Controller
{
	public function contact()
	{
		$misc = new MiscellaneousController();

		$language = $misc->getLanguage();

		$information['seoInfo'] = $language->seoInfo()->select('meta_keyword_contact', 'meta_description_contact')->first();

		$information['pageHeading'] = $misc->getPageHeading($language);

		$information['bgImg'] = $misc->getBreadcrumb();

		$information['info'] = Basic::select(
			'email_address',
			'contact_number',
			'address',
			'kvk_number',
			'google_recaptcha_status',
			'latitude',
			'longitude',
			'contact_title',
			'contact_subtile',
			'contact_details'
		)->firstOrFail();

		return view('frontend.contact', $information);
	}

	public function sendMail(MailFromUserRequest $request)
	{
		$info = DB::table('basic_settings')
			->select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name', 'to_mail')
			->first();

		$recipients = AdminNotificationEmails::parseList($info->to_mail);
		if ($recipients === []) {
			Session::flash('error', __('The admin notification email is not set. Please configure “Mail To Admin” in the admin panel.'));

			return redirect()->back()->withInput();
		}

		$name = $request->name;
		$subject = $request->subject;

		$messageBody = '<p>A new quote request has been sent.<br/><strong>Client Name: </strong>' . e($name)
			. '<br/><strong>Client Mail: </strong>' . e($request->email) . '</p><p>Message: ' . nl2br(e($request->message)) . '</p>';

		$fromMail = $info->from_mail ?: config('mail.from.address');
		$fromName = $info->from_name ?: config('mail.from.name');

		if (empty($fromMail)) {
			Session::flash('error', __('The sender email is not configured. Set “Mail From Admin” in Basic Settings or MAIL_FROM_ADDRESS in .env.'));

			return redirect()->back()->withInput();
		}

		try {
			if ((int) $info->smtp_status === 1) {
				Config::set('mail.mailers.smtp', [
					'transport' => 'smtp',
					'host' => $info->smtp_host,
					'port' => $info->smtp_port,
					'encryption' => $info->encryption,
					'username' => $info->smtp_username,
					'password' => $info->smtp_password,
					'timeout' => null,
					'auth_mode' => null,
				]);
			}

			Mail::send([], [], function (Message $message) use ($recipients, $subject, $messageBody, $fromMail, $fromName) {
				$message->to($recipients)
					->subject($subject)
					->from($fromMail, $fromName)
					->html($messageBody, 'text/html');
			});

			Session::flash('success', __('A contact request was sent successfully') . '!');
		} catch (\Throwable $e) {
			report($e);
			$message = config('app.debug')
				? $e->getMessage()
				: __('Something went wrong while sending your message. Please try again later.');
			Session::flash('error', $message);

			return redirect()->back()->withInput();
		}

		return redirect()->back();
	}
}
