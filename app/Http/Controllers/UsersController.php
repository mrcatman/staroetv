<?php

namespace App\Http\Controllers;

use App\Helpers\DatesHelper;
use App\Helpers\GeolocationHelper;
use App\Helpers\PermissionsHelper;
use App\Models\Record;
use App\Models\User;

class UsersController extends Controller {

    private function profile($user)
    {
        $videos_query = Record::approved()->where(['author_id' => $user->id, 'is_radio' => false])->orderBy('id', 'desc');
        $videos_count = $videos_query->count();
        $videos = $videos_query->limit(24)->get();

        $radio_recordings_query = Record::approved()->where(['author_id' => $user->id, 'is_radio' => true])->orderBy('id', 'desc');
        $radio_recordings_count = $radio_recordings_query->count();
        $radio_recordings = $radio_recordings_query->limit(24)->get();

        $banned_till = null;
        $is_banned_forever = $user->group_id == 255;
        if ($user->warnings->count() > 0) {
            $last_ban = $user->warnings->first();
            if ($last_ban->weight == 1 && $last_ban->time_expires > time()) {
                $banned_till = DatesHelper::formatTS($last_ban->time_expires);
            }
        }
        return view("pages.users.show", [
            'user' => $user,

            'banned_till' => $banned_till,
            'is_banned_forever' => $is_banned_forever,

            'videos' => $videos,
            'videos_count' => $videos_count,

            'radio_recordings' => $radio_recordings,
            'radio_recordings_count' => $radio_recordings_count,
        ]);
    }

    public function showMe()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect(route('index'));
        }
        return $this->profile($user);
    }

    public function show($conditions) {
        $user = User::where($conditions)->first();

        if (!$user) {
            return redirect('/');
        }
        return $this->profile($user);
    }

    public function index(
        GeolocationHelper $geolocation
    ) {
        $on_page = request()->input('on_page', 50);
        if ($on_page <= 10 || $on_page >= 101) {
            $on_page = 10;
        }

        $search = "";
        $group_id = 0;
        $sort_by = "username";
        $sort_dir = "ASC";
        $available_sortings = ["username", "group_id", "created_at", "was_online"];
        if (PermissionsHelper::allows('usearch')) {
            $is_moderator = PermissionsHelper::allows('usedita');
            if (request()->has('sort_by') && in_array(request()->input('sort_by'), $available_sortings)) {
                $sort_by = request()->input('sort_by');
            }
            if ($sort_by == "was_online") {
                $sort_dir = "DESC";
            }
            $users = User::orderBy($sort_by, $sort_dir);
            if (request()->has('search')) {
                $search = request()->input('search');
                $field = "username";
                if (request()->has('search_field') && $is_moderator) {
                    $field = request()->input('search_field');
                }
                $users = $users->where($field, 'LIKE', '%'.$search.'%');
            }
            if (request()->has('group_id')) {
                $group_id = request()->input('group_id');
                if ($group_id > 0) {
                    $users = $users->where(['group_id' => $group_id]);
                }
            }

            $total = $users->count();

            $users = $users->paginate($on_page)->appends(request()->except('page'));

            if ($is_moderator) {
                $users->getCollection()->each(function($user) use ($geolocation) {
                    $user->country = $geolocation->country($user);
                });
            }

            return view("pages.users.list", [
                'is_moderator' => $is_moderator,
                'sort_by' => $sort_by,
                'on_page' => $on_page,
                'group_id' => $group_id,
                'search' => $search,
                'total' => $total,
                'users' => $users
            ]);
        } else {
            return redirect(route('index'));
        }
    }



    public function autocomplete() {
        $count = 30;
        $users = User::select('id', 'username')->orderBy('was_online', 'desc');
        if (request()->has('term')) {
            $users = $users->where('username', 'LIKE', '%'.request()->input('term').'%');
        }
        $total = $users->count();
        $page = request()->input('page', 1);
        $users = $users->limit($count)->offset($count * ($page - 1))->get();
        return [
            'status' => 1,
            'data' => [
                'total' => $total,
                'users' => $users
            ]
        ];
    }



    public function videos($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect(route('index'));
        }
        $records = Record::where(['is_radio' => false, 'author_id' => $id])->orderBy('id', 'desc')->paginate(30);
        return view("pages.users.records", [
            'page_title' => 'Видеозаписи',
            'records' => $records,
            'user' => $user,
        ]);
    }

    public function radioRecordings($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect(route('index'));
        }
        $records = Record::where(['is_radio' => true, 'author_id' => $id])->orderBy('id', 'desc')->paginate(30);
        return view("pages.users.records", [
            'page_title' => 'Радиозаписи',
            'records' => $records,
            'user' => $user,
        ]);
    }


}
