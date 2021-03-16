<?php
class type
{
    public $user_id;
    public $is_bot;
    public $first_name;
    public $last_name;
    public $username;

    public function to_user($user)
    {
        foreach ($user as $key => $value) {
            if ($key == 'id') {
                $this->user_id = $value;
            }

            if ($key == 'is_bot') {
                $this->is_bot = $value;
            }

            if ($key == 'first_name') {
                $this->first_name = $value;
            }

            if ($key == 'last_name') {
                $this->last_name = $value;
            }

            if ($key == 'username') {
                $this->username = $value;
            }

        }
    }

    public $message_id;
    public $date;
    public $text;

}
