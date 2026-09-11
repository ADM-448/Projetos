<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\GenericNotificationMail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $nome;
    public $assunto;
    public $mensagem;
    public $link;
    public $textoBotao;

    /**
     * Create a new job instance.
     */
    public function __construct(string $email, ?string $nome, string $assunto, string $mensagem, ?string $link = null, ?string $textoBotao = null)
    {
        $this->email = $email;
        $this->nome = $nome;
        $this->assunto = $assunto;
        $this->mensagem = $mensagem;
        $this->link = $link;
        $this->textoBotao = $textoBotao;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->email)->send(
                new GenericNotificationMail(
                    $this->assunto, 
                    $this->mensagem, 
                    $this->nome, 
                    $this->link, 
                    $this->textoBotao
                )
            );
        } catch (\Throwable $e) {
            Log::error('SendEmailNotificationJob: Falha ao enviar e-mail via SMTP', [
                'email' => $this->email,
                'assunto' => $this->assunto,
                'erro' => $e->getMessage()
            ]);
            
            // Relança a exceção para que o Job falhe e possa ser tentado novamente (retries)
            throw $e;
        }
    }
}
