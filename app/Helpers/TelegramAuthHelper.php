<?php
namespace App\Helpers;

class TelegramAuthHelper {

    public static function verify($auth_data)
    {
        $token = config('tokens.telegram');

        $hash = $auth_data['hash'];
        unset($auth_data['hash']);
        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);

        $secret_key = hash('sha256', $token, true);
        $generated_hash = hash_hmac('sha256', $data_check_string, $secret_key);
        if (strcmp($generated_hash, $hash) !== 0) {
            throw new \Exception('Ошибка проверки данных');
        }
        if ((time() - $auth_data['auth_date']) > 86400) {
            throw new \Exception('Ошибка: Данные устарели, авторизуйтесь ещё раз');
        }

        return $auth_data;
    }
}
