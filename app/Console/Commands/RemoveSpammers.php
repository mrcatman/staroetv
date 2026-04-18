<?php

namespace App\Console\Commands;

use App\Helpers\GeolocationHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveSpammers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:remove-spammers {--confirm}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove spammers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private GeolocationHelper $geolocation
    )
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
        $forbidden_countries = explode(',',config('site.geoip_forbidden_countries'));
        $confirm = $this->option('confirm');
        User::whereDate('created_at', '>', Carbon::create(2023, 1, 1))->orderBy('id')->chunk(100, function ($users) use ($confirm, $forbidden_countries) {
            $users->each(function ($user) use ($confirm, $forbidden_countries) {
                $country = $this->geolocation->country($user);
                if (in_array($country, $forbidden_countries)) {
                    echo 'User '.$user->username.' is a spammer from '.$country.PHP_EOL;
                    if ($confirm) {
                        $user->delete();
                    }
                }
            });
        });
        User::whereDate('created_at', '>', Carbon::create(2023, 1, 1))->whereNotNull('signature')->where(function ($query) {
            $query->where('username', 'like', '%bet%')
                ->orWhere('username', 'like', '%game%')
                ->orWhere('username', 'like', '%win%');
        })->orderBy('id')->chunk(100, function ($users) use ($confirm) {
            $users->each(function ($user) use ($confirm) {
                echo 'User ' . $user->username . ' has suspicious signature: '.$user->signature . PHP_EOL;
                if ($confirm) {
                    $user->delete();
                }
            });
        });
    }
}
