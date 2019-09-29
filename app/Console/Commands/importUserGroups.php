<?php

namespace App\Console\Commands;

use App\Helpers\CSVHelper;
use App\UserAward;
use App\UserGroup;
use App\UserGroupConfig;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class importUserGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usergroups:import';

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

    public function handle()
    {
        $files = Storage::disk('public_data')->files("data/usergroups");
        foreach ($files as $file) {
            $contents = Storage::disk('public_data')->get($file);
            $contents = json_decode($contents);
            $options = $contents->options;
            unset($contents->options);
            $usergroup = UserGroup::find($contents->id);
            if (!$usergroup) {
                $usergroup = new UserGroup((array)$contents);
            } else {
                $usergroup->fill((array)$contents);
            }
            $usergroup->save();
            echo "Saved usergroup ".$contents->name.PHP_EOL;
            foreach ($options as $option) {
                $option_obj = new UserGroupConfig([
                    'group_id' => $contents->id,
                    'option_name' => $option->option_name,
                    'option_value' => $option->value
                ]);
                $option_obj->save();
            }
        }
    }
}
