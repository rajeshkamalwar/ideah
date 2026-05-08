<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Jobs\SendBulkEmailJob;
use App\Models\BulkEmailCampaign;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BulkEmailController extends Controller
{
    public function index()
    {
        $campaigns = BulkEmailCampaign::orderByDesc('id')->paginate(15);

        return view('admin.bulk-email.index', compact('campaigns'));
    }

    public function compose()
    {
        $counts = [
            'subscribers'  => Subscriber::count(),
            'users'        => User::count(),
            'vendors'      => Vendor::count(),
        ];

        $allEmails = $this->collectRecipients(['subscribers', 'users', 'vendors']);
        $counts['total_unique'] = count($allEmails);

        return view('admin.bulk-email.compose', compact('counts'));
    }

    /**
     * AJAX — return the email list for the selected audience groups.
     */
    public function recipients(Request $request)
    {
        $audiences = $request->input('audience', []);

        if (empty($audiences)) {
            return response()->json([]);
        }

        $list = [];

        if (in_array('subscribers', $audiences)) {
            foreach (Subscriber::select('id', 'email_id')->get() as $s) {
                $list[$s->email_id] = ['email' => $s->email_id, 'name' => '', 'group' => 'Subscriber'];
            }
        }

        if (in_array('users', $audiences)) {
            foreach (User::select('id', 'email', 'name')->get() as $u) {
                $list[$u->email] = [
                    'email' => $u->email,
                    'name'  => $u->name ?? '',
                    'group' => 'User',
                ];
            }
        }

        if (in_array('vendors', $audiences)) {
            foreach (Vendor::select('id', 'email', 'username')->get() as $v) {
                $list[$v->email] = [
                    'email' => $v->email,
                    'name'  => $v->username ?? '',
                    'group' => 'Vendor',
                ];
            }
        }

        return response()->json(array_values($list));
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('image'),
            ], 422);
        }

        $imageName = UploadFile::store(public_path('assets/img/bulk-email/'), $request->file('image'));

        return response()->json([
            'location' => asset('assets/img/bulk-email/' . $imageName),
        ]);
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject'        => 'required|string|max:255',
            'body'           => 'required|string',
            'audience'       => 'required|array|min:1',
            'audience.*'     => 'in:subscribers,users,vendors',
            'custom_recipients' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $recipients = $this->resolveRecipients($request);

        if (empty($recipients)) {
            return redirect()->back()
                ->with('warning', __('No recipients found. All were removed or none matched.'))
                ->withInput();
        }

        $campaign = BulkEmailCampaign::create([
            'subject'          => $request->subject,
            'body'             => $request->body,
            'audience'         => $request->audience,
            'total_recipients' => count($recipients),
            'sent_count'       => 0,
            'failed_count'     => 0,
            'status'           => 'queued',
        ]);

        foreach ($recipients as $email) {
            SendBulkEmailJob::dispatch($campaign->id, $email);
        }

        return redirect()->route('admin.bulk_email.index')
            ->with('success', __('Campaign queued for :count recipients. Emails will be sent in the background.', ['count' => count($recipients)]));
    }

    public function schedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject'           => 'required|string|max:255',
            'body'              => 'required|string',
            'audience'          => 'required|array|min:1',
            'audience.*'        => 'in:subscribers,users,vendors',
            'scheduled_at'      => 'required|date|after:now',
            'custom_recipients' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $recipients = $this->resolveRecipients($request);

        if (empty($recipients)) {
            return redirect()->back()
                ->with('warning', __('No recipients found. All were removed or none matched.'))
                ->withInput();
        }

        $scheduledAt = Carbon::parse($request->scheduled_at);
        $delay       = now()->diffInSeconds($scheduledAt, false);

        $campaign = BulkEmailCampaign::create([
            'subject'          => $request->subject,
            'body'             => $request->body,
            'audience'         => $request->audience,
            'total_recipients' => count($recipients),
            'sent_count'       => 0,
            'failed_count'     => 0,
            'status'           => 'queued',
            'scheduled_at'     => $scheduledAt,
        ]);

        foreach ($recipients as $email) {
            SendBulkEmailJob::dispatch($campaign->id, $email)->delay($delay > 0 ? $delay : 0);
        }

        return redirect()->route('admin.bulk_email.index')
            ->with('success', __('Campaign scheduled for :time (:count recipients).', [
                'time'  => $scheduledAt->format('M d, Y H:i'),
                'count' => count($recipients),
            ]));
    }

    public function destroy(int $id)
    {
        BulkEmailCampaign::findOrFail($id)->delete();

        return redirect()->route('admin.bulk_email.index')
            ->with('success', __('Campaign deleted successfully.'));
    }

    /**
     * Use custom_recipients JSON if provided (user may have removed some),
     * otherwise fall back to full audience collection.
     */
    private function resolveRecipients(Request $request): array
    {
        if ($request->filled('custom_recipients')) {
            $decoded = json_decode($request->custom_recipients, true);

            // If the field was explicitly submitted (even as empty array),
            // honour it — don't silently fall back to the full audience.
            if (is_array($decoded)) {
                return $this->sanitizeEmails($decoded);
            }
        }

        return $this->sanitizeEmails($this->collectRecipients($request->audience));
    }

    /**
     * Collect unique email addresses from selected audience groups.
     */
    private function collectRecipients(array $audiences): array
    {
        $emails = [];

        if (in_array('subscribers', $audiences)) {
            $emails = array_merge($emails, Subscriber::pluck('email_id')->toArray());
        }

        if (in_array('users', $audiences)) {
            $emails = array_merge($emails, User::pluck('email')->toArray());
        }

        if (in_array('vendors', $audiences)) {
            $emails = array_merge($emails, Vendor::pluck('email')->toArray());
        }

        return array_values(array_unique(array_filter($emails)));
    }

    private function sanitizeEmails(array $emails): array
    {
        $clean = [];

        foreach ($emails as $email) {
            if (!is_string($email)) {
                continue;
            }

            $email = trim($email);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $clean[] = strtolower($email);
        }

        return array_values(array_unique($clean));
    }
}
