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

    }
}
