<?php
namespace App\Helpers;
use App\Comment;
use App\UserGroupConfig;
use Illuminate\Support\Facades\Cache;

class PermissionsHelper {

    public static function allows($option_name, $user = null) {
        if (!$user) {
            $user = auth()->user();
        }
        $user_group_id = $user ? $user->group_id : 999;
        $key = 'permissions_'.$option_name.'_'.$user_group_id;
        if (Cache::has($key)) {
            return Cache::get($key);
        }
        if (isset($GLOBALS['permissions_'.$option_name."_".$user_group_id])) {
            return $GLOBALS['permissions_'.$option_name."_".$user_group_id];
        }
        $config = UserGroupConfig::where(['group_id' => $user_group_id, 'option_name' => $option_name])->first();
        if ($config) {
            $value =  $config->option_value;
        } else {
            $value = false;
        }
        $GLOBALS['permissions_'.$option_name] = $value;
        Cache::put($key, $value, 30);
        return $value;
    }

    public static function isBanned($user = null) {
        if (!$user) {
            $user = auth()->user();
        }
        if (!$user) {
            return false;
        }
        $warnings = $user->warnings;
        if (count($warnings) === 0) {
            return false;
        }
        $warning = $warnings->first();
        if ($warning->weight == 1 && ($warning->time_expires > time() || $warning->is_forever)) {
            return true;
        }
        return false;
    }

    public static function checkGroupAccess($type, $entity) {
        $data = $entity->{$type};
        if (!$data || $data == "0" || $data == "") {
            return true;
        }
        $user_group_id = auth()->user() ? auth()->user()->group_id : 999;
        $groups_with_access = explode(",", $data);
        return in_array($user_group_id, $groups_with_access);
    }

}
