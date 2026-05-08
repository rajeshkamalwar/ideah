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
        if ($campaign->status === 'queued') {
            $campaign->update(['status' => 'sending']);
        }

        $mailData = [
            'recipient' => $this->recipientEmail,
            'subject'   => $campaign->subject,
            'body'      => $campaign->body,
        ];

        BasicMailer::sendMail($mailData);

        $campaign->increment('sent_count');
        $this->finalizeCampaign();
    }

    public function failed(\Throwable $exception): void
    {
        $campaign = BulkEmailCampaign::find($this->campaignId);

        if (!$campaign) {
            return;
        }

        $campaign->increment('failed_count');
        $this->finalizeCampaign();
    }

    private function finalizeCampaign(): void
    {
        $campaign = BulkEmailCampaign::find($this->campaignId);
        if (!$campaign) {
            return;
        }

        $failedCount = (int) ($campaign->failed_count ?? 0);
        $processed = (int) $campaign->sent_count + $failedCount;

        if ($processed >= (int) $campaign->total_recipients) {
            $campaign->update([
                'status' => $failedCount > 0 ? 'completed_error' : 'completed',
            ]);
        }
    }
}
