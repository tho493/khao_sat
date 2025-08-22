<?php

namespace App\Jobs;

use App\Models\DotKhaoSat;
use App\Mail\InviteKhaoSat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendKhaoSatEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dotKhaoSat;
    protected $recipients;

    public function __construct(DotKhaoSat $dotKhaoSat, array $recipients)
    {
        $this->dotKhaoSat = $dotKhaoSat;
        $this->recipients = $recipients;
    }

    public function handle()
    {
        foreach ($this->recipients as $recipient) {
            Mail::to($recipient['email'])
                ->send(new InviteKhaoSat($this->dotKhaoSat, $recipient));
        }
    }
}