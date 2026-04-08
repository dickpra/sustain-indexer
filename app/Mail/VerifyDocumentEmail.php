<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;

class VerifyDocumentEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function build()
    {
        return $this->subject('Action Required: Verify Your Document Submission - Sustaindex')
                    ->from('admin@sustaindex.org', 'Sustaindex')
                    ->to($this->document->email)
                    ->view('emails.verify'); // Mengarah ke file tampilan email
    }
}