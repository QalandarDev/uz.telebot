<?php
include_once 'api.php';

class uz_telebot
{
    public $token;
    public $parse_mode;
    public $updates;

    public $message;
    public $edited_message;
    public $channel_post;
    public $edited_channel_post;
    public $inline_query;
    public $shipping_query;
    public $pre_checkout_query;
    public $poll;
    public function __construct($token, $updates, $parse_mode = null)
    {
        $this->token = $token;
        __handle($updates);
        $this->parse_mode = $parse_mode;
        $this->message_handlers = [];
    }
    public function __handle($updates)
    {
        $this->updates = json_decode($updates, true);
        foreach ($this->updates as $key => $value) {
            if ($key == 'message') {
                $this->message = $value;
            }

            if ($key == 'edited_message') {
                $this->edited_message = $value;
            }

            if ($key == 'channel_post') {
                $this->channel_post = $value;
            }

            if ($key == 'inline_query') {
                $this->inline_query = $value;
            }

            if ($key == 'shipping_query') {
                $this->shipping_query = $value;
            }

            if ($key == 'pre_checkout_query') {
                $this->pre_checkout_query = $value;
            }

            if ($key == 'poll') {
                $this->poll = $value;
            }

        }
    }
}
