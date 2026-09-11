<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assunto;
    public $mensagem;
    public $nome;
    public $link;
    public $textoBotao;

    /**
     * Create a new message instance.
     */
    public function __construct(string $assunto, string $mensagem, ?string $nome = null, ?string $link = null, ?string $textoBotao = null)
    {
        $this->assunto = $assunto;
        $this->mensagem = $mensagem;
        $this->nome = $nome;
        $this->link = $link;
        $this->textoBotao = $textoBotao;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->assunto,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.generic',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
