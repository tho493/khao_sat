<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteKhaoSat extends Mailable
{
    use Queueable, SerializesModels;

    public $dotKhaoSat;
    public $recipient;

    public function __construct(DotKhaoSat $dotKhaoSat, $recipient)
    {
        $this->dotKhaoSat = $dotKhaoSat;
        $this->recipient = $recipient;
    }

    public function build()
    {
        return $this->subject('Thông báo khảo sát')
            ->view('emails.invite-khao-sat');
    }
}