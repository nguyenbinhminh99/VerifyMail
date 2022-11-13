<?php

namespace App\Jobs;

use App\Mail\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendQueueVerifyEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $from = 'noreply.auction.ghtk@gmail.com';
    private $fromName = 'Auction GHTK';
    private $subject = 'Verify Email';
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $email = new VerifyEmail($this->data);
      Mail::to($this->data['email'])->send($email);
    }
}