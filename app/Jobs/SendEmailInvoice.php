<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\invoice;
use Illuminate\Support\Facades\Mail;

class SendEmailInvoice implements ShouldQueue
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

        Mail::send('emails.invoice', $data, function($message) use ($data) { 
                $to = [$data['email'], 'inversionesdacabeonline@gmail.com'];
                if (@$data['seller'] && $data['seller'] != null) {
                    $to[] = $data['seller'];
                }
                if ($data['estatus'] == 'APROBADO') {
                    $message->to($to)
                        ->from(env('MAIL_USERNAME', 'eliecercedano@gmail.com'), 'Inversiones DACABE')
                        ->bcc('eliecercedano@gmail.com')
                        ->subject( 'Su Pedido DACABE' )
                        ->attach($data['ruta_pdf'], [
                            'as' => 'pedido.pdf',
                            'mime' => 'application/pdf',
                        ]);
                        /*
                        ->attach($image, [
                            'as' => 'image.jpg',
                            'mime' => 'image/jpeg',
                        ]);
                        */

                } else {
                    $message->to($to)
                        ->from(env('MAIL_USERNAME', 'eliecercedano@gmail.com'), 'Inversiones DACABE')
                        ->bcc('eliecercedano@gmail.com')
                        ->subject( 'Su Pedido DACABE' );
                }
        });
        
    }
}
