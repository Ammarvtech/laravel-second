<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
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
        //from($this->name)->replyTo($this->email)
        return $this->from('tasali@tasali.media')->subject($this->data['subject'])->view($this->data['template'])->with('data', $this->data);
    }
}
