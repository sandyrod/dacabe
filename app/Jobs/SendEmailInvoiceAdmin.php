<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\invoice;
use Illuminate\Support\Facades\Mail;

class SendEmailInvoiceAdmin implements ShouldQueue
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
        $image = '';
        if ($data['rif_foto']) {
            $image = asset('storage/products/') . '/' . $data['rif_foto'];
        }

        Mail::send('emails.invoice_admin', $data, function($message) use ($data, $image) { 
            if ($image) {
                $to = [$data['email']];
                    $message->to($to)
                        ->from(env('MAIL_USERNAME', 'eliecercedano@gmail.com'), 'Inversiones DACABE')
                        ->bcc('eliecercedano@gmail.com')
                        ->subject( 'Pedido DACABE' )
                        ->attach($image, [
                            'as' => 'image.jpg',
                            'mime' => 'image/jpeg',
                        ])
                        ->attach($data['ruta_pdf'], [
                            'as' => 'pedido.pdf',
                            'mime' => 'application/pdf',
                        ]);
            } else {
                $to = [$data['email']];
                    $message->to($to)
                        ->from(env('MAIL_USERNAME', 'eliecercedano@gmail.com'), 'Inversiones DACABE')
                        ->bcc('eliecercedano@gmail.com')
                        ->subject( 'Pedido DACABE' )
                        ->attach($data['ruta_pdf'], [
                            'as' => 'pedido.pdf',
                            'mime' => 'application/pdf',
                        ]);                        
            }
        });
        
    }
}
