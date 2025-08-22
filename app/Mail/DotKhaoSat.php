<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DotKhaoSat extends Mailable
{
    use Queueable, SerializesModels;

    public $dotKhaoSat;

    public function __construct(DotKhaoSat $dotKhaoSat)
    {
        $this->dotKhaoSat = $dotKhaoSat;
    }
}