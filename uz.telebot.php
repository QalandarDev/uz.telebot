<?php
include_once "api.php";

class uz_telebot
{
    public $token;
    public $parse_mode;
    public $updates;
    public $updates7;
    public $PDO;
    public $message = null;
    public $edited_message = null;
    public $channel_post = null;
    public $edited_channel_post = null;
    public $inline_query = null;
    public $callback_query = null;
    public $shipping_query = null;
    public $pre_checkout_query = null;
    public $poll = null;
    public $result = 0;

    public function __construct(
        $token,
        $updates,
        $db = null,
        $parse_mode = null
    ) {
        $this->token = $token;
        $this->updates = json_decode($updates, true);
        $this->updates7 = json_decode($updates);
        try {
            if (!is_null($db)) {
                if ($db['host']) {$host = $db['host'];} else {
                    $host = 'localhost';
                }
                $this->PDO = new PDO(
                    "mysql:host=" . $host . ";port=3306;dbname=" . $db["name"],
                    $db["user"],
                    $db["pass"]
                );
            }
        } catch (PDOException $e) {
            error_log("ERROR:PDO: " . $e->getMessage());
        }
        if (array_key_exists("message", $this->updates)) {
            $this->message = $this->updates7->message;
        }

        if (array_key_exists("edited_message", $this->updates)) {
            $this->edited_message = $this->updates7->edited_message;
        }

        if (array_key_exists("channel_post", $this->updates)) {
            $this->channel_post = $this->updates7->channel_post;
        }

        if (array_key_exists("edited_channel_post", $this->updates)) {
            $this->edited_channel_post = $this->updates7->edited_channel_post;
        }

        if (array_key_exists("inline_query", $this->updates)) {
            $this->inline_query = $this->updates7->inline_query;
        }
        if (array_key_exists("callback_query", $this->updates)) {
            $this->callback_query = $this->updates7->callback_query;
        }

        if (array_key_exists("shipping_query", $this->updates)) {
            $this->shipping_query = $this->updates7->shipping_query;
        }

        if (array_key_exists("pre_checkout_query", $this->updates)) {
            $this->pre_checkout_query = $this->updates7->pre_checkout_query;
        }

        if (array_key_exists("poll", $this->updates)) {
            $this->poll = $this->updates7->poll;
        }
        $this->parse_mode = $parse_mode;
    }
    
    public function get_var(&$var, $name)
    {
        $var = get_class_vars(__CLASS__)[$name];
    }
    public function send_message($chat_id, $text, $key = "12")
    {
        if ($key == "12") {
            return send_message_api($this->token, $chat_id, $text);
        } else {
            return sendmessagekey($this->token, $chat_id, $text, $key);
        }
    }
    public function copymessage($from_chat, $chat, $msg_id, $caption = null)
    {
        return copy_message($this->token, $from_chat, $chat, $msg_id, $caption);
    }
    public function forwardmessage($chat_id, $from_id, $msg_id)
    {
        return forward_message($this->token, $chat_id, $from_id, $msg_id);
    }
    public function is_member($chat_id, $user_id)
    {
        $data = get_chat_member($this->token, $chat_id, $user_id);
        if ($data) {
            $status = ["creator", "administrator", "member"];
            if (in_array($data["status"], $status)) {
                return true;
            }
            return false;
        }
    }
    public function is_admin($chat_id, $user_id)
    {
        $data = get_chat_member($this->token, $chat_id, $user_id);
        if ($data) {
            $status = ["creator", "administrator"];
            if (in_array($data["status"], $status)) {
                return true;
            }
            return false;
        }
    }
    public function editmessage($chat_id, $message_id, $text, $key = "12")
    {
        if ($key == "12") {
            return editmessageapi($this->token, $chat_id, $message_id, $text);
        } else {
            return editmessagekey(
                $this->token,
                $chat_id,
                $message_id,
                $text,
                $key
            );
        }
    }
    public function editMessageCaption($chat_id, $message_id, $caption,$key=12)
    {
        if($key==12){
            return editMessageCaptionapi($this->token, $chat_id, $message_id, $caption);
        } else{
            return editMessageCaptionapikey($this->token, $chat_id, $message_id, $caption,$key);
        }
        
    }
    public function editMessageReplyMarkup($chat_id, $message_id, $key)
    {
        return editMessageReplyMarkupapi($this->token, $chat_id, $message_id, $key);
    }
    public function deletemessage($chat_id, $message_id)
    {
        return deletemessageapi($this->token, $chat_id, $message_id);
    }
    public function answerCallbackQuery($id, $text, $show = false)
    {
        return answerCallback($this->token, $id, $text, $show);
    }
    public function sendAudio($chat_id, $audio, $text)
    {
        return send_audio($this->token, $chat_id, $audio, $text);
    }
    public function sendDocument($chat_id, $file, $text = null)
    {
        return send_document($this->token, $chat_id, $file, $text);
    }
    public function QUERY(string $query, array $values)
    {
        try {
            $data = $this->PDO->prepare($query);
            // $values = array_map('strval', $values);
            $data->execute($values);
        } catch (\Exception $e) {
            error_log($e);
        }
        $result = $data->fetch(PDO::FETCH_ASSOC);
        // $result = array_map("utf8_encode", $result);
        $this->result = $result;
    }
    public function FETCH_ALL(string $query, array $values)
    {
        try {
            $data = $this->PDO->prepare($query);
            $data->execute($values);
        } catch (\Exception $e) {
            error_log($e);
        }
        $rows = [];
        while ($result = $data->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $result;
        }
        $this->result = $rows;
    }
    public function GET_ROW(string $query, array $values = null)
    {
        try {
            $data = $this->PDO->prepare($query);
            $data->execute($values);
        } catch (\Exception $e) {
            $this->PDO->rollback();
            error_log($e);
            throw $e;
        }
        if ($data) {
            // $result = $data->fetchAll(PDO::FETCH_COLUMN);
            $result = $data->fetchColumn();
            $this->result = $result;
        }
    }
}
