<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this->subject('[Auction - GHTK] Verify Mail')
        ->view('mail', [
          'full_name' => $this->data['full_name'],
          'email' => $this->data['email'],
          'token' => $this->data['token'],
        ]);
    }
}