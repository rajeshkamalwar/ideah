<?php

namespace App\Jobs;

use App\Http\Helpers\BasicMailer;
use App\Models\BulkEmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $campaignId;
    protected string $recipientEmail;

    public function __construct(int $campaignId, string $recipientEmail)
    {
        $this->campaignId     = $campaignId;
        $this->recipientEmail = $recipientEmail;
    }

    public function handle(): void
    {
        $campaign = BulkEmailCampaign::find($this->campaignId);

        if (!$campaign) {
            return;
        }

        $info = \App\Models\BasicSettings\Basic::select('smtp_status')->first();

        if (!$info || $info->smtp_status != 1) {
            return;
        }

        $mailData = [
            'recipient' => $this->recipientEmail,
            'subject'   => $campaign->subject,
            'body'      => $campaign->body,
        ];

        BasicMailer::sendMail($mailData);

        $campaign->increment('sent_count');

        if ($campaign->fresh()->sent_count >= $campaign->total_recipients) {
            $campaign->update(['status' => 'completed']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $campaign = BulkEmailCampaign::find($this->campaignId);

        if ($campaign && $campaign->status !== 'completed') {
            $campaign->update(['status' => 'failed']);
        }
    }
}
