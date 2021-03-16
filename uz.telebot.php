<?php
include_once 'api.php';

class uz_telebot
{

    public $token;
    public $parse_mode;
    public $updates;

    public $message = [];
    public $edited_message = [];
    public $channel_post = [];
    public $edited_channel_post = [];
    public $inline_query = [];
    public $shipping_query = [];
    public $pre_checkout_query = [];
    public $poll = [];
    public function __construct($token, $updates, $parse_mode = null)
    {
        $this->token = $token;
        self::__handle($updates);
        $this->parse_mode = $parse_mode;
        $this->message_handlers = [];
    }
    public function __handle($update)
    {
        $this->updates = json_decode($update, true);
        if (array_key_exists('message', $this->updates)) {
            $this->message = $this->updates['message'];
        }

        if (array_key_exists('edited_message', $this->updates)) {
            $this->edited_message = $this->updates['edited_message'];
        }

        if (array_key_exists('channel_post', $this->updates)) {
            $this->channel_post = $this->updates['channel_post'];
        }

        if (array_key_exists('edited_channel_post', $this->updates)) {
            $this->edited_channel_post = $this->updates['edited_channel_post'];
        }

        if (array_key_exists('inline_query', $this->updates)) {
            $this->inline_query = $this->updates['inline_query'];
        }

        if (array_key_exists('shipping_query', $this->updates)) {
            $this->shipping_query = $this->updates['shipping_query'];
        }

        if (array_key_exists('pre_checkout_query', $this->updates)) {
            $this->pre_checkout_query = $this->updates['pre_checkout_query'];
        }

        if (array_key_exists('poll', $this->updates)) {
            $this->poll = $this->updates['poll'];
        }

    }
    public function send_message($chat_id, $text)
    {
        return send_message_api($this->token, $chat_id, $text);
    }
}
