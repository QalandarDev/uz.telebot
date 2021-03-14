<?php

class api
{
    public function _make_request($token, $method_name, $method = 'get', $params = null)
    {
        $request_url = "https://api.telegram.org/bot{$token}/{$method_name}";
        $handle = curl_init($request_url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        if (curl_error) {
            return self::_curl_error($handle);
        }

    }
    public function _curl_error($handle)
    {

        $response = curl_exec($handle);
        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl da xatolik bor $errno: $error\n");
            curl_close($handle);
            return false;
        }
        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);
        if ($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);
            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Xatolik xabari {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Bot tokeni xato');
            }
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Murojaat bajarildi: {$response['description']}\n");
            }
            $response = $response['result'];
        }
        return $response;
    }
    public function get_me($token)
    {
        $method_url = 'getMe';
        return _make_request($token, $method_url);
    }
    public function get_file($token, $file_id)
    {
        $method_url = 'getFile';
        return _make_request($token, $method_url, $params = ['file_id' => $file_id]);
    }
    public function get_file_url($token, $file_id)
    {
        return self::get_file($token, $file_id)['file_path'];
    }
    public function download_file($token, $file_path)
    {
        $url = "https://api.telegram.org/file/bot{$token}/{$file_path}";
        return file_get_contents($url);
    }
    public function send_message($token, $chat_id, $text,
        $disable_web_page_preview = null, $reply_to_message_id = null,
        $reply_markup = null, $parse_mode = null, $disable_notification = null) {

        $method_url = 'sendMessage';
        $payload = ['chat_id' => $chat_id, 'text' => $text];
        if (!is_null($disable_web_page_preview)) {
            $payload['disable_web_page_preview'] = $disable_web_page_preview;
        }
        if (!is_null($reply_to_message_id)) {
            $payload['reply_to_message_id'] = $reply_to_message_id;
        }
        if (!is_null($reply_markup)) {
            $payload['reply_markup'] = $reply_markup;
        }
        if (!is_null($parse_mode)) {
            $payload['parse_mode'] = $parse_mode;
        }
        if (!is_null($disable_notification)) {
            $payload['disable_notification'] = $disable_notification;
        }
        return self::_make_request($token, $method_url, $params = $payload);
    }
    public function set_webhook($token, $url)
    {
        $method_url = 'setWebhook';
        $payload = ['url' => $url];
        return self::_make_request($token, $method_url);
    }
    public function delete_webhook($token)
    {
        $method_url = 'deleteWebhook';
        return self::_make_request($token, $method_url);
    }
    public function get_webhook_info($token)
    {
        $method_url = 'getWebhookInfo';
        return $self::_make_request($token, $method_url);
    }
    public function get_Updates($token)
    {
        $method_url = 'getUpdates';
        return $self::_make_request($token, $method_url);
    }
    public function get_user_profile_photos($token, $user_id)
    {
        $method_url = 'getUserProfilePhotos';
        $payload = ['user_id' => $user_id];
        return $self::_make_request($token, $method_url, $payload);
    }
    public function get_chat($token, $chat_id)
    {
        $method_url = 'getChat';
        $payload = ['chat_id' => $chat_id];
        return $self::make_request($token, $method_url, $payload);
    }
    public function leave_chat($token, $chat_id)
    {
        $method_url = 'leaveChat';
        $payload = ['chat_id' => $chat_id];
        return $self::_make_request($token, $method_url, $payload);
    }
    public function get_chat_administrators($token, $chat_id)
    {
        $method_url = 'getChatAdministrators';
        $payload = ['chat_id' => $chat_id];
        return $self::_make_request($token, $method_url, $payload);
    }
    public function get_chat_members_count($token, $chat_id)
    {
        $method_url = 'getChatMembersCount';
        $payload = ['chat_id' => $chat_id];
        return $self::_make_request($token, $method_url, $payload);
    }
    public function set_chat_sticker_set($token, $chat_id, $stiker_set_name)
    {
        $method_url = 'setChatStikerSet';
        $payload = ['chat_id' => $chat_id, 'sticker_set_name' => $stiker_set_name];
        return $self::_make_request($token, $method_url, $payload);
    }
    public function delete_chat_stiker_set($token, $chat_id)
    {
        $method_url = 'deleteChatStickerSet';
        $payload = ['chat_id' => $chat_id];
        return $self::_make_request($token, $method_url, $payload);
    }
    public function get_chat_member($token, $chat_id, $user_id)
    {
        $method_url = 'getChatMember';
        $payload = ['chat_id' => $chat_id, 'user_id' => $user_id];
        return $self::_make_request($token, $method_url, $payload);
    }
    public function forward_message($token, $chat_id,
        $from_chat_id, $message_id, $disable_notification = null) {
        $method_url = 'forwardMessage';
        $payload = ['chat_id' => $chat_id, 'from_chat_id' => $from_chat_id, 'message_id' => $message_id];
        if ($disable_notification) {
            $payload['disable_notification'] = $disable_notification;
        }
        return $self::_make_request($token, $method_url, $payload);
    }
    public function copy_message($token, $chat_id, $message_id, $caption = null,
        $parse_mode = null, $caption_entities = null, $disable_notification = null,
        $reply_to_message_id = null, $reply_markup = null, $allow_sending_without_reply = null) {
        $method_url = 'copyMessage';
        $payload = ['chat_id' => $chat_id, 'from_chat_id' => $from_chat_id, 'message_id' => $message_id];
        if ($caption) {
            $payload['caption'] = $caption;
        }

        if ($parse_mode) {
            $payload['parse_mode'] = $parse_mode;
        }

        if ($caption_entities) {
            $payload['caption_entities'] = $caption_entities;
        }

        if ($disable_notification) {
            $payload['disable_notification'] = $disable_notification;
        }

        if ($reply_to_message_id) {
            $payload['reply_to_message_id'] = $reply_to_message_id;
        }

        if ($reply_markup) {
            $payload['reply_markup'] = $reply_markup;
        }

        if ($allow_sending_without_reply) {
            $payload['allow_sending_without_reply'] = $allow_sending_without_reply;
        }
        return $self::_make_request($token, $method_url, $payload);
    }
    public function send_dice($token, $chat_id, $emoji = null, $disable_notification = null, $reply_to_message_id = null, $reply_markup = null)
    {
        $method_url = 'sendDice';
        $payload = ['chat_id' => $chat_id];
        if ($emoji) {
            $payload['emoji'] = $emoji;
        }

        if ($disable_notification) {
            $payload['disable_notification'] = $disable_notification;
        }

        if ($reply_to_message_id) {
            $payload['reply_to_message_id'] = $reply_to_message_id;
        }

        if ($reply_markup) {
            $payload['rely_markup'] = $reply_markup;
        }

        return $self::_make_request($token, $method_url, $payload);
    }
    public function send_photo($token, $chat_id, $photo, $caption = null, $reply_to_message_id = null, $reply_markup = null, $parse_mode = null, $disable_notification = null)
    {
        $method_url = 'sendPhoto';
        $payload = ['chat_id' => $chat_id];
        if (is_string($photo)) {
            $payload['photo'] = $photo;
        }

        if ($caption) {
            $payload['caption'] = $caption;
        }

        if ($reply_to_message_id) {
            $payload['reply_to_message_id'] = $reply_to_message_id;
        }

        if ($reply_markup) {
            $payload['reply_markup'] = $reply_markup;
        }

        if ($parse_mode) {
            $payload['parse_mode'] = $parse_mode;
        }

        if ($disable_notification) {
            $payload['disable_notification'] = $disable_notification;
        }

        return $self::_make_request($token, $method_url, $payload);
    }
    public function send_media_group($token, $chat_id, $media, $disable_notification = null, $reply_to_message_id = null)
    {
        $method_url = 'sendMediaGroup';
        $media_json = json_encode($media);
        $payload = ['chat_id' => $chat_id, 'media' => $media_json];
        if ($disable_notification) {
            $payload['disable_notification'] = $disable_notification;
        }

        if ($reply_to_message_id) {
            $payload['reply_to_message_id'] = $reply_to_message_id;
        }

        return $self::_make_request($token, $method_url, $payload);
    }
    public function send_location($token, $chat_id, $latitude, $longitude, $live_period = null, $reply_to_message_id = null, $reply_markup = null, $disable_notification = null)
    {
        $method_url = 'sendLocation';
        $payload = ['chat_id' => $chat_id, 'latitude' => $latitude, 'longitude' => $longitude];
        if ($live_period) {
            $payload['live_period'] = $live_period;
        }

        if ($reply_to_message_id) {
            $payload['reply_to_message_id'] = $reply_to_message_id;
        }

        if ($reply_markup) {
            $payload['reply_markup'] = $reply_markup;
        }

        if ($disable_notification) {
            $payload['disable_notification'] = $disable_notification;
        }

        return $self::_make_request($token, $method_url, $payload);
    }
    public function edit_message_live_location($token, $latitude, $longitude, $chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null)
    {
        $method_url = 'editMessageLiveLocation';
        $payload = ['longitude' => $longitude, 'latitude' => $latitude];
        if ($chat_id) {
            $payload['chat_id'] = $chat_id;
        }

        if ($message_id) {
            $payload['message_id'] = $message_id;
        }

        if ($inline_message_id) {
            $payload['inline_message_id'] = $inline_message_id;
        }

        if ($reply_markup) {
            $payload['reply_markup'] = $reply_markup;
        }

        return $self::_make_request($token, $method_url, $payload);
    }
    public function stop_message_live_location($token, $chat_id = null, $message_id = null, $inline_message_id = null, $reply_markup = null)
    {
        $method_url = 'stopMessageLiveLocation';
        $payload = array();
        if ($chat_id) {
            $payload['chat_id'] = $chat_id;
        }

        if ($message_id) {
            $payload['message_id'] = $message_id;
        }

        if ($inline_message_id) {
            $payload['inline_message_id'] = $inline_message_id;
        }

        if ($reply_markup) {
            $payload['reply_markup'] = $reply_markup;
        }

        return $self::_make_request($token, $method_url, $payload);
    }
    public function send_venue($token, $chat_id, $latitude, $longitude, $title, $address, $foursquare_id = null, $foursquare_type = null, $disable_notification = null, $reply_to_message_id = null, $reply_markup = null)
    {
        $method_url = 'sendVenue';
        $payload = ['chat_id' => $chat_id, 'latitude' => $latitude, 'longitude' => $longitude, 'title' => $title, 'address' => $address];
        if ($foursquare_id) {
            $payload['foursquare_id'] = $foursquare_id;
        }
        if ($foursquare_type) {
            $payload['foursquare_type'] = $foursquare_type;
        }
        if ($disable_notification) {
            $payload['disable_notification'] = $disable_notification;
        }

        if ($reply_to_message_id) {
            $payload['reply_to_message_id'] = $reply_to_message_id;
        }

        if ($reply_markup) {
            $payload['reply_markup'] = $reply_markup;
        }

        return $self::_make_request($token, $method_url, $payload);

    }
    public function _convert_list_json_serializable($result)
    {
        return json_encode($result, JSON_PRETTY_PRINT);
    }
    public function _convert_markup($markup)
    {
        return json_encode($markup);
    }

}
