<?php

namespace App\Mail;

use App\Models\Mesaj;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * İletişim formundan gelen mesajın ilgili birime bildirimi.
 *
 * Gönderen adresi her zaman kendi sunucumuzdaki hesap (MAIL_FROM_ADDRESS);
 * ziyaretçinin adresi Reply-To'ya konur. Ziyaretçinin adresinden gönderiyor
 * gibi yapmak SPF/DKIM'e takılır ve mail spam'e düşer.
 */
class YeniIletisimMesaji extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Mesaj $mesaj)
    {
    }

    public function envelope(): Envelope
    {
        $konu = $this->mesaj->birim
            ? "[{$this->mesaj->birim}] Yeni iletişim mesajı"
            : 'Yeni iletişim mesajı';

        if ($this->mesaj->konu) {
            $konu .= ' — ' . $this->mesaj->konu;
        }

        return new Envelope(
            subject: $konu,
            replyTo: filter_var($this->mesaj->mail, FILTER_VALIDATE_EMAIL)
                ? [new Address($this->mesaj->mail, $this->mesaj->ad)]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.yeni-mesaj');
    }
}
