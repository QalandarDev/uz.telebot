<?php

$payload["parse_mode"] = "HTML";
function _make_request($token, $method_name, $params = null)
{
    $request_url = "https://api.telegram.org/bot{$token}/{$method_name}";
    $handle = curl_init($request_url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 90);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($handle, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
    ]);

    return _curl_error($handle);
}
function _curl_error($handle)
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
    } elseif ($http_code != 200) {
        $response = json_decode($response, true);
        error_log(
            "Xatolik xabari {$response["error_code"]}: {$response["description"]}\n"
        );
        if ($http_code == 401) {
            throw new Exception("Bot tokeni xato");
        }
        return false;
    } else {
        $response = json_decode($response, true);
        /*if (isset($response['description'])) {
        error_log("Murojaat bajarildi: {$response['description']}\n");
        }*/
        $responses = $response["result"];
    }
    return $responses;
}
function get_me($token)
{
    $method_url = "getMe";
    return _make_request($token, $method_url);
}

function get_file($token, $file_id)
{
    $method_url = "getFile";
    return _make_request(
        $token,
        $method_url,
        $params = ["file_id" => $file_id]
    );
}
function get_file_url($token, $file_id)
{
    return get_file($token, $file_id)["file_path"];
}
function download_file($token, $file_path)
{
    $url = "https://api.telegram.org/file/bot{$token}/{$file_path}";
    return file_get_contents($url);
}
function send_message_api(
    $token,
    $chat_id,
    $text = "hello world",
    $disable_web_page_preview = false,
    $reply_to_message_id = null,
    $reply_markup = null,
    $parse_mode = "HTML",
    $disable_notification = null
) {
    $method_url = "sendMessage";
    $payload = ["chat_id" => $chat_id, "text" => $text];
    if (!is_null($disable_web_page_preview)) {
        $payload["disable_web_page_preview"] = $disable_web_page_preview;
    }
    if (!is_null($reply_to_message_id)) {
        $payload["reply_to_message_id"] = $reply_to_message_id;
    }
    if (!is_null($reply_markup)) {
        $payload["reply_markup"] = $reply_markup;
    }
    if (!is_null($parse_mode)) {
        $payload["parse_mode"] = $parse_mode;
    }
    if (!is_null($disable_notification)) {
        $payload["disable_notification"] = $disable_notification;
    }
    return _make_request($token, $method_url, $payload);
}
function send_document($token, $chat_id, $filename, $caption = "@AQTGDEV")
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . $token . "/sendDocument?chat_id=" . $chat_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    // Create CURLFile
    $finfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filename);
    $cFile = new CURLFile($filename, $finfo);

    // Add CURLFile to CURL request
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        "document" => $cFile,
        "caption" => $caption,
    ]);

    // Call
    $result = curl_exec($ch);

}
function sendmessagekey($token, $chat_id, $text, $key)
{
    $method_url = "sendMessage";
    $payload = [
        "chat_id" => $chat_id,
        "text" => $text,
        "parse_mode" => "HTML",
        "reply_markup" => json_encode($key),
    ];
    return _make_request($token, $method_url, $payload);
}
function send_audio($token, $chat_id, $audio, $text)
{
    $method_url = "sendAudio";
    $payload = [
        "chat_id" => $chat_id,
        "audio" => $audio,
        "caption" => $text,
        "parse_mode" => "HTML",
    ];
    return _make_request($token, $method_url, $payload);
}
function editmessageapi($token, $chat_id, $message_id, $text)
{
    $method_url = "editMessageText";
    $payload = [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "text" => $text,
        "disable_web_page_preview"=>true,
        "parse_mode" => "HTML",
    ];
    return _make_request($token, $method_url, $payload);
}
function editmessagekey($token, $chat_id, $message_id, $text, $key)
{
    $method_url = "editMessageText";
    $payload = [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "text" => $text,
        "reply_markup" => json_encode($key),
        "disable_web_page_preview"=>true,
        "parse_mode" => "HTML",
    ];
    return _make_request($token, $method_url, $payload);
}
function editMessageReplyMarkupapi($token, $chat_id, $msg_id, $key)
{
    $method_url = "editMessageReplyMarkup";
    $payload = [
        'chat_id' => $chat_id,
        'message_id' => $msg_id,
        'reply_markup' => $key,
    ];
    return _make_request($token, $method_url, $payload);
}
function answerCallback($token, $cq_id, $text, $alert)
{
    $method_url = "answerCallbackQuery";
    $payload = [
        "callback_query_id" => $cq_id,
        "text" => $text,
        "show_alert" => $alert,
    ];
    return _make_request($token, $method_url, $payload);
}
function deletemessageapi($token, $chat_id, $message_id)
{
    $method_url = "deleteMessage";
    $payload = ["chat_id" => $chat_id, "message_id" => $message_id];
    return _make_request($token, $method_url, $payload);
}
function set_webhook($token, $url)
{
    $method_url = "setWebhook";
    $payload = ["url" => $url];
    return _make_request($token, $method_url);
}
function delete_webhook($token)
{
    $method_url = "deleteWebhook";
    return _make_request($token, $method_url);
}
function get_webhook_info($token)
{
    $method_url = "getWebhookInfo";
    return _make_request($token, $method_url);
}
function get_Updates($token)
{
    $method_url = "getUpdates";
    return _make_request($token, $method_url);
}
function get_user_profile_photos($token, $user_id)
{
    $method_url = "getUserProfilePhotos";
    $payload = ["user_id" => $user_id];
    return _make_request($token, $method_url, $payload);
}
function get_chat($token, $chat_id)
{
    $method_url = "getChat";
    $payload = ["chat_id" => $chat_id];
    return _make_request($token, $method_url, $payload);
}
function leave_chat($token, $chat_id)
{
    $method_url = "leaveChat";
    $payload = ["chat_id" => $chat_id];
    return _make_request($token, $method_url, $payload);
}
function get_chat_administrators($token, $chat_id)
{
    $method_url = "getChatAdministrators";
    $payload = ["chat_id" => $chat_id];
    return _make_request($token, $method_url, $payload);
}
function get_chat_members_count($token, $chat_id)
{
    $method_url = "getChatMembersCount";
    $payload = ["chat_id" => $chat_id];
    return _make_request($token, $method_url, $payload);
}
function set_chat_sticker_set($token, $chat_id, $stiker_set_name)
{
    $method_url = "setChatStikerSet";
    $payload = ["chat_id" => $chat_id, "sticker_set_name" => $stiker_set_name];
    return _make_request($token, $method_url, $payload);
}
function delete_chat_stiker_set($token, $chat_id)
{
    $method_url = "deleteChatStickerSet";
    $payload = ["chat_id" => $chat_id];
    return _make_request($token, $method_url, $payload);
}
function get_chat_member($token, $chat_id, $user_id)
{
    $method_url = "getChatMember";
    $payload = ["chat_id" => $chat_id, "user_id" => $user_id];
    return _make_request($token, $method_url, $payload);
}
function buttonCallback($text, $data)
{
    $data = [
        "text" => $text,
        "callback_data" => $data,
    ];
    return $data;
}
function buttonText($text)
{
    $data = [
        "text" => $text,
    ];
    return $data;
}
function buttonUrl($text, $url)
{
    $data = [
        "text" => $text,
        "url" => $url,
    ];
    return $data;
}
function buttonPhone($text)
{
    $data = [
        "text" => $text,
        "request_contact" => true,
    ];
    return $data;
}
function buttonGeo($text)
{
    $data = [
        "text" => $text,
        "request_location" => true,
    ];
    return $data;
}
function buttonRow($column)
{
    $data = [$column];
    return $data;
}
function buttonRows(array $columns)
{
    return $columns;
}
function inlineButton(array $row)
{
    $data["inline_keyboard"] = $row;
    return $data;
}
function smartButton(array $row)
{
    $data = [
        "keyboard" => $row,
        "resize_keyboard" => true,
    ];
    return $data;
}
function phone($phone1, $phone2)
{
    if (strpos($phone1, "+") !== false) {
        $phone1 = str_replace("+", null, $phone1);
    }
    if (strpos($phone2, "+") !== false) {
        $phone2 = str_replace("+", null, $phone2);
    }
    if (strcmp($phone1, $phone2) == 0) {
        return true;
    } else {
        return false;
    }
}
function forward_message(
    $token,
    $chat_id,
    $from_chat_id,
    $message_id,
    $disable_notification = null
) {
    $method_url = "forwardMessage";
    $payload = [
        "chat_id" => $chat_id,
        "from_chat_id" => $from_chat_id,
        "message_id" => $message_id,
    ];
    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }
    return _make_request($token, $method_url, $payload);
}
function copy_message(
    $token,
    $from_chat_id,
    $chat_id,
    $message_id,
    $caption = null
) {
    $method_url = "copyMessage";
    $payload = [
        "chat_id" => $chat_id,
        "from_chat_id" => $from_chat_id,
        "message_id" => $message_id,
    ];
    if ($caption) {
        $payload['reply_markup'] = $caption;
    }
    return _make_request($token, $method_url, $payload);
}
function editMessageCaptionapi($token, $chat_id, $message_id, $caption)
{
    $payload = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        "caption" => $caption,
        'parse_mode'=>'HTML'
    ];
    $method_url = "editMessageCaption";
    return _make_request($token, $method_url, $payload);
}
function editMessageCaptionapikey($token, $chat_id, $message_id, $caption,$key)
{
    $payload = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        "caption" => $caption,
        'parse_mode'=>'HTML',
        'reply_markup'=>$key
    ];
    $method_url = "editMessageCaption";
    return _make_request($token, $method_url, $payload);
}
function send_dice(
    $token,
    $chat_id,
    $emoji = null,
    $disable_notification = null,
    $reply_to_message_id = null,
    $reply_markup = null
) {
    $method_url = "sendDice";
    $payload = ["chat_id" => $chat_id];
    if ($emoji) {
        $payload["emoji"] = $emoji;
    }

    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }

    if ($reply_to_message_id) {
        $payload["reply_to_message_id"] = $reply_to_message_id;
    }

    if ($reply_markup) {
        $payload["rely_markup"] = $reply_markup;
    }

    return _make_request($token, $method_url, $payload);
}
function send_photo(
    $token,
    $chat_id,
    $photo,
    $caption = null,
    $reply_to_message_id = null,
    $reply_markup = null,
    $parse_mode = null,
    $disable_notification = null
) {
    $method_url = "sendPhoto";
    $payload = ["chat_id" => $chat_id];
    if (is_string($photo)) {
        $payload["photo"] = $photo;
    }

    if ($caption) {
        $payload["caption"] = $caption;
    }

    if ($reply_to_message_id) {
        $payload["reply_to_message_id"] = $reply_to_message_id;
    }

    if ($reply_markup) {
        $payload["reply_markup"] = $reply_markup;
    }

    if ($parse_mode) {
        $payload["parse_mode"] = $parse_mode;
    }

    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }

    return _make_request($token, $method_url, $payload);
}
function send_media_group(
    $token,
    $chat_id,
    $media,
    $disable_notification = null,
    $reply_to_message_id = null
) {
    $method_url = "sendMediaGroup";
    $media_json = json_encode($media);
    $payload = ["chat_id" => $chat_id, "media" => $media_json];
    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }

    if ($reply_to_message_id) {
        $payload["reply_to_message_id"] = $reply_to_message_id;
    }

    return _make_request($token, $method_url, $payload);
}
function send_location(
    $token,
    $chat_id,
    $latitude,
    $longitude,
    $live_period = null,
    $reply_to_message_id = null,
    $reply_markup = null,
    $disable_notification = null
) {
    $method_url = "sendLocation";
    $payload = [
        "chat_id" => $chat_id,
        "latitude" => $latitude,
        "longitude" => $longitude,
    ];
    if ($live_period) {
        $payload["live_period"] = $live_period;
    }

    if ($reply_to_message_id) {
        $payload["reply_to_message_id"] = $reply_to_message_id;
    }

    if ($reply_markup) {
        $payload["reply_markup"] = $reply_markup;
    }

    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }

    return _make_request($token, $method_url, $payload);
}
function edit_message_live_location(
    $token,
    $latitude,
    $longitude,
    $chat_id = null,
    $message_id = null,
    $inline_message_id = null,
    $reply_markup = null
) {
    $method_url = "editMessageLiveLocation";
    $payload = ["longitude" => $longitude, "latitude" => $latitude];
    if ($chat_id) {
        $payload["chat_id"] = $chat_id;
    }

    if ($message_id) {
        $payload["message_id"] = $message_id;
    }

    if ($inline_message_id) {
        $payload["inline_message_id"] = $inline_message_id;
    }

    if ($reply_markup) {
        $payload["reply_markup"] = $reply_markup;
    }

    return _make_request($token, $method_url, $payload);
}
function stop_message_live_location(
    $token,
    $chat_id = null,
    $message_id = null,
    $inline_message_id = null,
    $reply_markup = null
) {
    $method_url = "stopMessageLiveLocation";
    $payload = [];
    if ($chat_id) {
        $payload["chat_id"] = $chat_id;
    }

    if ($message_id) {
        $payload["message_id"] = $message_id;
    }

    if ($inline_message_id) {
        $payload["inline_message_id"] = $inline_message_id;
    }

    if ($reply_markup) {
        $payload["reply_markup"] = $reply_markup;
    }

    return _make_request($token, $method_url, $payload);
}
function send_venue(
    $token,
    $chat_id,
    $latitude,
    $longitude,
    $title,
    $address,
    $foursquare_id = null,
    $foursquare_type = null,
    $disable_notification = null,
    $reply_to_message_id = null,
    $reply_markup = null
) {
    $method_url = "sendVenue";
    $payload = [
        "chat_id" => $chat_id,
        "latitude" => $latitude,
        "longitude" => $longitude,
        "title" => $title,
        "address" => $address,
    ];
    if ($foursquare_id) {
        $payload["foursquare_id"] = $foursquare_id;
    }
    if ($foursquare_type) {
        $payload["foursquare_type"] = $foursquare_type;
    }
    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }

    if ($reply_to_message_id) {
        $payload["reply_to_message_id"] = $reply_to_message_id;
    }

    if ($reply_markup) {
        $payload["reply_markup"] = $reply_markup;
    }

    return _make_request($token, $method_url, $payload);
}
function send_contact(
    $token,
    $chat_id,
    $phone_number,
    $first_name,
    $last_name = null,
    $vcard = null,
    $disable_notification = null,
    $reply_to_message_id = null,
    $reply_markup = null
) {
    $method_url = "sendContact";
    $payload = [
        "chat_id" => $chat_id,
        "phone_number" => $phone_number,
        "first_name" => $first_name,
    ];
    if ($last_name) {
        $payload["last_name"] = $last_name;
    }

    if ($vcard) {
        $payload["vcard"] = $vcard;
    }

    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }

    if ($reply_markup) {
        $payload["reply_markup"] = $reply_markup;
    }

    return _make_request($token, $method_url, $payload);
}
function send_chat_action($token, $chat_id, $action)
{
    $method_url = "sendChatAction";
    $payload = ["chat_id" => $chat_id, "action" => $action];
    return _make_request($token, $method_url, $payload);
}
function send_video(
    $token,
    $chat_id,
    $data,
    $duration = null,
    $caption = null,
    $reply_to_message_id = null,
    $reply_markup = null,
    $parse_mode = null,
    $supports_streaming = null,
    $disable_notification = null
) {
    $method_url = "sendVideo";
    $payload = ["chat_id" => $chat_id];
    if (is_string($data)) {
        $payload["data"] = $data;
    }

    if ($duration) {
        $payload["duration"] = $duration;
    }

    if ($caption) {
        $payload["caption"] = $caption;
    }

    if ($reply_to_message_id) {
        $payload["reply_to_message_id"] = $reply_to_message_id;
    }

    if ($reply_markup) {
        $payload["reply_markup"] = $reply_markup;
    }

    if ($supports_streaming) {
        $payload["supports_streaming"] = $supports_streaming;
    }

    if ($disable_notification) {
        $payload["disable_notification"] = $disable_notification;
    }

    return _make_request($token, $method_url, $payload);
}
function _convert_list_json_serializable($result)
{
    return json_encode($result, JSON_PRETTY_PRINT);
}
function _convert_markup($markup)
{
    return json_encode($markup);
}
function _code(string $text)
{
    return "<code>" . $text . "</code>";
}
function _bold(string $text)
{
    return "<b>" . $text . "</b>";
}
function _italic(string $text)
{
    return "<i>" . $text . "</i>";
}
function _a($text, $url)
{
    return '<a href="' . $url . '">' . $text . '</a>';
}
function _json($text)
{
    return json_encode($text, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
function obj2arr($text)
{
    return json_decode($text, true);
}
