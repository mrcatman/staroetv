<?php
namespace App\Helpers;
use App\Comment;
use App\UserGroupConfig;

class PermissionsHelper {

    public static function allows($option_name) {
        $user_group_id = auth()->user() ? auth()->user()->group_id : 999;
        $config = UserGroupConfig::where(['group_id' => $user_group_id, 'option_name' => $option_name])->first();
        if ($config) {
            return $config->option_value;
        } else {
            return false;
        }
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