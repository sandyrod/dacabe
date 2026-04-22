<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\invoice;
use Illuminate\Support\Facades\Mail;

class SendEmailPromotions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $data;
    protected $mode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $mode='')
    {
        $this->data = $data;
        $this->mode = $mode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new invoice();
        $data = $this->data;

        Mail::send('emails.'.$data['tipo'], $data, function($message) use ($data) {            
            $message->to($data['email'])
                ->from(env('MAIL_USERNAME', 'inversionesdacabeonline@gmail.com'), 'Inversiones DACABE')
                ->bcc('eliecercedano@gmail.com')
                ->subject( $data['title'] ); 
        });
        
    }
}
