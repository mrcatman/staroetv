<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    public $redirectTo = '/index/8';

    use ResetsPasswords;

    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );

        return $response == Password::PASSWORD_RESET
            ? [
                'status' => 1,
                'text' => 'Пароль обновлен',
                'redirect_to' => '/index/8'
            ]
            : $this->sendResetFailedResponse($request, $response);
    }


    protected function sendResetResponse(Request $request, $response) {
        return $response->json([
            'status' => 1,
            'text' => 'Пароль обновлен',
            'redirect_to' => '/index/8'
        ]);
    }


    public function __construct()
    {
        $this->middleware('guest');
    }
}
