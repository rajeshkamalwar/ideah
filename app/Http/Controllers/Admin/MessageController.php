<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Http\Controllers\Controller;
use App\Http\Helpers\AdminNotificationEmails;
use App\Models\Language;
use App\Models\Listing\Listing;
use App\Models\Listing\ListingContent;
use App\Models\Listing\ListingMessage;
use App\Models\Listing\ProductMessage;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $information['langs'] = Language::all();

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;
        $information['messages'] = ListingMessage::with([
            'vendor',
            'listing' => fn ($q) => $q->select('id', 'vendor_id'),
        ])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.message.listing', $information);
    }

    public function productIndex(Request $request)
    {
        $information['langs'] = Language::all();

        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;

        $information['messages'] = ProductMessage::with([
            'vendor',
            'product' => fn ($q) => $q->select('id', 'vendor_id', 'listing_id'),
            'product.content' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            },
        ])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.message.product', $information);
    }

    public function notifyListingVendor(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer',
            'language' => 'required|string',
        ]);

        $language = Language::query()->where('code', $request->language)->firstOrFail();

        $listingMessage = ListingMessage::with([
            'listing' => fn ($q) => $q->select('id', 'vendor_id'),
        ])->findOrFail($request->message_id);

        $vendorId = $this->resolvedVendorIdForListingMessage($listingMessage);
        if ($vendorId <= 0) {
            Session::flash('error', __('This enquiry is not linked to a vendor (e.g. admin-owned listing). Nothing was sent.'));

            return redirect()->back();
        }

        $vendor = Vendor::query()->select('id', 'email', 'to_mail', 'username')->find($vendorId);
        if (! $vendor) {
            Session::flash('error', __('Vendor not found.'));

            return redirect()->back();
        }

        $recipients = $this->vendorMailRecipients($vendor);
        if ($recipients === []) {
            Session::flash('error', __('This vendor has no email address on file.'));

            return redirect()->back();
        }

        $subjectLine = $this->listingMessageForwardSubject($listingMessage, $language);

        $html = $this->buildListingForwardHtml($listingMessage, $language);

        return $this->sendVendorNotificationMail($recipients, $subjectLine, $html);
    }

    public function notifyProductVendor(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer',
            'language' => 'required|string',
        ]);

        $language = Language::query()->where('code', $request->language)->firstOrFail();

        $productMessage = ProductMessage::with([
            'product' => fn ($q) => $q->select('id', 'vendor_id', 'listing_id'),
            'product.content' => function ($query) use ($language) {
                $query->where('language_id', $language->id);
            },
        ])->findOrFail($request->message_id);

        $vendorId = $this->resolvedVendorIdForProductMessage($productMessage);
        if ($vendorId <= 0) {
            Session::flash('error', __('This enquiry is not linked to a vendor. Nothing was sent.'));

            return redirect()->back();
        }

        $vendor = Vendor::query()->select('id', 'email', 'to_mail', 'username')->find($vendorId);
        if (! $vendor) {
            Session::flash('error', __('Vendor not found.'));

            return redirect()->back();
        }

        $recipients = $this->vendorMailRecipients($vendor);
        if ($recipients === []) {
            Session::flash('error', __('This vendor has no email address on file.'));

            return redirect()->back();
        }

        $subjectLine = $this->productMessageForwardSubject($productMessage);

        $html = $this->buildProductForwardHtml($productMessage);

        return $this->sendVendorNotificationMail($recipients, $subjectLine, $html);
    }

    /**
     * @return list<string>
     */
    protected function vendorMailRecipients(Vendor $vendor): array
    {
        $raw = $vendor->to_mail ?: $vendor->email;
        $recipients = AdminNotificationEmails::parseList($raw);
        if ($recipients === [] && $vendor->email) {
            return [$vendor->email];
        }

        return $recipients;
    }

    protected function resolvedVendorIdForListingMessage(ListingMessage $m): int
    {
        $vid = (int) ($m->vendor_id ?? 0);
        if ($vid > 0) {
            return $vid;
        }
        if ($m->relationLoaded('listing') && $m->listing) {
            return (int) $m->listing->vendor_id;
        }
        $listing = Listing::query()->select('vendor_id')->find($m->listing_id);

        return $listing ? (int) $listing->vendor_id : 0;
    }

    protected function resolvedVendorIdForProductMessage(ProductMessage $m): int
    {
        $vid = (int) ($m->vendor_id ?? 0);
        if ($vid > 0) {
            return $vid;
        }
        if ($m->relationLoaded('product') && $m->product) {
            return (int) $m->product->vendor_id;
        }
        $product = $m->product()->select('vendor_id')->first();

        return $product ? (int) $product->vendor_id : 0;
    }

    protected function listingMessageForwardSubject(ListingMessage $m, Language $language): string
    {
        $content = ListingContent::query()
            ->where('listing_id', $m->listing_id)
            ->where('language_id', $language->id)
            ->select('title')
            ->first();
        $title = $content->title ?? __('Listing enquiry');

        $site = DB::table('basic_settings')->where('uniqid', 12345)->value('website_title')
            ?: config('app.name');

        return '[' . $site . '] ' . __('Forwarded enquiry: :title', ['title' => $title]);
    }

    protected function productMessageForwardSubject(ProductMessage $m): string
    {
        $title = 'Product';
        if ($m->relationLoaded('product') && $m->product && $m->product->relationLoaded('content')) {
            $c = $m->product->content->first();
            if ($c && ! empty($c->title)) {
                $title = $c->title;
            }
        }
        $site = DB::table('basic_settings')->where('uniqid', 12345)->value('website_title')
            ?: config('app.name');

        return '[' . $site . '] ' . __('Forwarded enquiry: :title', ['title' => $title]);
    }

    protected function buildListingForwardHtml(ListingMessage $m, Language $language): string
    {
        $content = ListingContent::query()
            ->where('listing_id', $m->listing_id)
            ->where('language_id', $language->id)
            ->first();

        $title = $content->title ?? __('Listing');
        $slug = $content->slug ?? '';
        $url = $slug ? route('frontend.listing.details', ['slug' => $slug, 'id' => $m->listing_id]) : '';

        $listingLine = $url !== ''
            ? '<a href="' . e($url) . '">' . e($title) . '</a>'
            : e($title);

        $parts = [];
        $parts[] = '<p><strong>' . e(__('This enquiry was forwarded from the admin panel')) . '</strong></p>';
        $parts[] = '<p><strong>' . e(__('Listing')) . ':</strong> ' . $listingLine . '</p>';
        $parts[] = '<p><strong>' . e(__('Enquirer name')) . ':</strong> ' . e($m->name) . '</p>';
        $parts[] = '<p><strong>' . e(__('Enquirer email')) . ':</strong> ' . e($m->email) . '</p>';
        $parts[] = '<p><strong>' . e(__('Phone')) . ':</strong> ' . e((string) $m->phone) . '</p>';
        $parts[] = '<p><strong>' . e(__('Message')) . ':</strong></p><div>' . nl2br(e((string) $m->message)) . '</div>';

        return implode('', $parts);
    }

    protected function buildProductForwardHtml(ProductMessage $m): string
    {
        $parts = [];
        $parts[] = '<p><strong>' . e(__('This enquiry was forwarded from the admin panel')) . '</strong></p>';
        $parts[] = '<p><strong>' . e(__('Customer name')) . ':</strong> ' . e($m->name) . '</p>';
        $parts[] = '<p><strong>' . e(__('Customer email')) . ':</strong> ' . e((string) $m->email) . '</p>';

        if (! empty($m->message)) {
            $decoded = json_decode($m->message, true);
            if (is_array($decoded) && $decoded !== []) {
                $parts[] = '<p><strong>' . e(__('Details')) . ':</strong></p><table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">';
                foreach ($decoded as $fieldName => $fieldData) {
                    if (! is_array($fieldData)) {
                        continue;
                    }
                    $type = (int) ($fieldData['type'] ?? 0);
                    $label = is_string($fieldName)
                        ? ucwords(str_replace('_', ' ', $fieldName))
                        : __('Field');
                    $cell = $this->formatProductFieldForEmail($fieldData, $type);
                    $parts[] = '<tr><th align="left">' . e($label) . '</th><td>' . $cell . '</td></tr>';
                }
                $parts[] = '</table>';
            }
        }

        return implode('', $parts);
    }

    protected function formatProductFieldForEmail(array $fieldData, int $type): string
    {
        $value = $fieldData['value'] ?? '';

        if ($type === 8) {
            $originalName = $fieldData['originalName'] ?? 'file';
            $fileName = is_string($value) ? $value : '';

            return e(__('A file was attached (:name). Download from the admin product message details if needed.', ['name' => $originalName]));
        }
        if ($type === 4 && is_array($value)) {
            return e(implode(', ', $value));
        }
        if ($type === 6 && $value !== '' && $value !== null) {
            try {
                return e(Carbon::parse($value)->format('d M Y'));
            } catch (\Throwable $e) {
                return e((string) $value);
            }
        }
        if ($type === 7 && $value !== '' && $value !== null) {
            try {
                return e(Carbon::parse($value)->format('h:i A'));
            } catch (\Throwable $e) {
                return e((string) $value);
            }
        }
        if ($type === 5) {
            return nl2br(e((string) $value));
        }

        return e(is_scalar($value) ? (string) $value : json_encode($value));
    }

    /**
     * @param  list<string>  $recipients
     */
    protected function sendVendorNotificationMail(array $recipients, string $subjectLine, string $html): \Illuminate\Http\RedirectResponse
    {
        $info = DB::table('basic_settings')
            ->select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name')
            ->first();

        $fromMail = $info->from_mail ?: config('mail.from.address');
        $fromName = $info->from_name ?: config('mail.from.name');

        if (empty($fromMail)) {
            Session::flash('error', __('The sender email is not configured in Basic Settings.'));

            return redirect()->back();
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

            Mail::send([], [], function (Message $message) use ($recipients, $subjectLine, $html, $fromMail, $fromName) {
                $message->to($recipients)
                    ->subject($subjectLine)
                    ->from($fromMail, $fromName)
                    ->html($html, 'text/html');
            });

            Session::flash('success', __('Email sent to vendor (:emails).', ['emails' => implode(', ', $recipients)]));
        } catch (\Throwable $e) {
            report($e);
            $err = config('app.debug') ? $e->getMessage() : __('Could not send email. Please check mail settings.');
            Session::flash('error', $err);
        }

        return redirect()->back();
    }

    public function delete(Request $request)
    {
        $message = ListingMessage::findOrFail($request->message_id);

        $message->delete();
        Session::flash('success', __('Message deleted successfully') . '!');

        return redirect()->back();
    }

    public function productDelete(Request $request)
    {
        $message = ProductMessage::findOrFail($request->message_id);

        if (! empty($message->message)) {
            $data = json_decode($message->message, true);
            if (! empty($data) && is_array($data)) {
                foreach ($data as $field) {
                    if (is_array($field) && isset($field['type']) && $field['type'] == 8 && isset($field['value'])) {
                        $localPath = public_path('./assets/file/zip-files/' . $field['value']);
                        if (file_exists($localPath)) {
                            @unlink($localPath);
                        }
                    }
                }
            }
        }

        $message->delete();
        Session::flash('success', __('Message deleted successfully') . '!');

        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $message = ListingMessage::findOrFail($id);

            $message->delete();
        }

        Session::flash('success', __('Message deleted successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    public function productBulkDelete(Request $request)
    {
        $ids = (array) $request->ids;
        foreach ($ids as $id) {
            $message = ProductMessage::findOrFail($id);
            if ($message) {
                if (! empty($message->message)) {
                    $data = json_decode($message->message, true);
                    if (! empty($data) && is_array($data)) {
                        foreach ($data as $field) {
                            if (is_array($field) && isset($field['type']) && $field['type'] == 8 && isset($field['value'])) {
                                $localPath = public_path('./assets/file/zip-files/' . $field['value']);
                                if (file_exists($localPath)) {
                                    @unlink($localPath);
                                }
                            }
                        }
                    }
                }
                $message->delete();
            }
        }

        Session::flash('success', __('Message deleted successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    public function showMessageDetails($id)
    {
        $message = ProductMessage::findOrFail($id);
        $applange = App::getLocale();
        $langPart = explode('_', $applange);
        $language = Language::query()->where('code', '=', $langPart[1])->firstOrFail();

        return view('admin.message.details', compact('message', 'language'));
    }
}
