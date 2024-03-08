<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\Picture;
use App\User;
use App\UserMeta;
use App\UserWarning;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = CSVHelper::transform(public_path("data_new/users.txt"), [
            'username', 'ucoz_uid', 'password', 'avatar', '', 'name', 'gender', 'email', 'yandex_video', '', 'country', 'youtube', 'city', 'signature', 'user_comment', 'date_reg', 'ip_address_reg', '', '', 'vk', 'facebook', '', 'date_of_birth', '', ''
        ], true);
        $user_data = CSVHelper::transform(public_path("data_new/ugen.txt"), [
            'id', 'username', 'group_id', '', '', '_warnings', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'was_online'
        ], true);
        $user_data_by_username = [];
        foreach ($user_data as $user_data_item) {
            $user_data_by_username[$user_data_item['username']] = $user_data_item;
        }
        foreach ($users as $user) {
            if (isset($user_data_by_username[$user['username']])) {
                $user_data_item = $user_data_by_username[$user['username']];
                $insert_data = [
                    'id' => $user_data_item['id'],
                    'email' => $user['email'],
                    'original_id' => $user_data_item['id'],
                    'username' => $user['username'],
                    'password' => $user['password'],
                    'name' => $user['name'],
                    'ucoz_uid' => $user['ucoz_uid'],
                    'group_id' => $user_data_item['group_id'],
                    'ip_address_reg' => $user['ip_address_reg'],
                    'user_comment' => $user['user_comment'],
                    'signature' => $user['signature'],
                    'was_online' => Carbon::createFromTimestamp($user_data_item['was_online']),
                    'created_at' => Carbon::createFromTimestamp($user['date_reg']),
                ];
                if (!($user_item = User::find($user_data_item['id']))) {
                    $user_item = new User($insert_data);
                    $user_item->save();
                    echo "Added user " . $user['username'] . PHP_EOL;
                } else {
                    $user_item->fill($insert_data);
                    $user_item->save();
                    //   echo "Updated user " . $user['username'] . PHP_EOL;
                }
                $metadata = [
                    'user_id' => $user_data_item['id'],
                    'gender' => $user['gender'],
                    'yandex_video' => $user['yandex_video'],
                    'youtube' => $user['youtube'],
                    'country' => $user['country'],
                    'city' => $user['city'],
                    'vk' => $user['vk'],
                    'facebook' => $user['facebook'],
                    'date_of_birth' => $user['date_of_birth'] ? Carbon::createFromFormat("Y-m-d", $user['date_of_birth']) : null,
                ];
                if (!($user_meta = UserMeta::where(['user_id' => $user_data_item['id']])->first())) {
                    $user_meta = new UserMeta($metadata);
                    $user_meta->save();
                    echo "Added user meta for " . $user_data_item['username'] . PHP_EOL;
                } else {

                    $user_meta->fill($metadata);
                    $user_meta->save();
                    //    echo "Updated user meta for " . $user_data_item['username'] . PHP_EOL;
                }

                if ($user['avatar'] != "") {
                    $picture = Picture::firstOrNew([
                        // 'user_id' => $user_data_item['id'],
                        'url' => $user['avatar']
                    ]);
                    $picture->save();
                    $user_item->avatar_id = $picture->id;
                    $user_item->save();
                    echo "Added avatar for " . $user_item['username'] . ": " . $user['avatar'] . PHP_EOL;
                }
            }
        }

        foreach ($user_data as $user) {
            if (isset($user['_warnings']) && strpos($user['_warnings'], '\\') !== false) {
                $user['_original'] = str_replace('`', '\\|', $user['_original']);
                $warnings = explode('\\|', $user['_original']);
                $start_text = explode("|",$warnings[0]);
                $first_ban_time = $start_text[count($start_text) - 1];
                $warnings_list_end = null;
                $i = 0;
                unset($warnings[0]);
                foreach ($warnings as $warning) {
                    if (!$warnings_list_end && strpos($warning, "|") !== false) {
                        $warnings_list_end = $i;
                    }
                    $i++;
                }
                $warnings = array_slice($warnings, 0, $warnings_list_end);
                array_unshift($warnings, $first_ban_time);
                $warnings = array_values(array_filter($warnings, function($warning) {
                    return strlen($warning) > 0;
                }));

                $warnings_count = count($warnings) / 4;
                for ($i = 0; $i < $warnings_count; $i++) {
                    if (isset($user['username']) && isset($user_data_by_username[$warnings[$i * 4 + 1]])) {
                        $warning = UserWarning::firstOrNew([
                            'created_at' => Carbon::createFromTimestamp($warnings[$i * 4]),
                            'from_id' => $user_data_by_username[$warnings[$i * 4 + 1]]['id'],
                            'to_id' => $user_data_by_username[$user['username']]['id'],
                            'weight' => $warnings[$i * 4 + 2],
                            'comment' => $warnings[$i * 4 + 3]
                        ]);
                        $warning->save();
                        echo "Added a warning from " . $warnings[$i * 4 + 1] . " for " . $user['username'] . PHP_EOL;
                    } else {
                        echo "Found error when adding warning: ".$warnings[$i * 4 + 1].PHP_EOL;
                    }
                }
            }
        }

      //  file_put_contents(public_path("data/users_json.txt"), json_encode($user_data, JSON_UNESCAPED_UNICODE));

    }
}
