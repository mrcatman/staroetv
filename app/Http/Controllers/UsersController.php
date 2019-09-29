<?php

namespace App\Http\Controllers;


use App\User;

class UsersController extends Controller {

    public function show($conditions) {
        $user = User::where($conditions)->first();
        if (!$user) {
            return view("pages.errors.404");
        }
        return view("pages.user", [
            'user' => $user
        ]);
    }


}
