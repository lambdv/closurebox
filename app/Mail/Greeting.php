<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class Greeting extends Mailable { use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected User $user
    ){
        
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Greeting',
            with: [
                'username' => $this->user->name,
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.greeting',
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


    // /**
    //  * Get the message headers.
    //  */
    // public function headers(): Headers
    // {
    //     return new Headers(
    //         messageId: 'custom-message-id@example.com',
    //         references: ['previous-message@example.com'],
    //         text: [
    //             'X-Custom-Header' => 'Custom Value',
    //         ],
    //     );
    // }


}
