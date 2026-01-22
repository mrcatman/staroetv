<?php

namespace App\Console\Commands;

use App\Models\AdditionalChannel;
use App\Models\Program;
use App\Models\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\select;

class FixMismatchedPrograms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'programs:fix-mismatched {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $programs = [];
        Program::chunk(100, function ($list) use (&$programs) {
            foreach ($list as $program) {
                if (!$program->channel_id) {
                    $programs[$program->id] = [-1];
                    continue;
                }
                $programs[$program->id] = [$program->channel_id];
                foreach ($program->additionalChannels as $channel) {
                    $programs[$program->id][] = $channel->channel_id;
                }
            }
        });

        $records = Record::whereNotNull('program_id');
        if ($this->argument('id')) {
            $records->where('program_id', $this->argument('id'));
        }
        $records->chunk(100, function ($records) use ($programs) {
            foreach ($records as $record) {
                if ($record->program_id && !$record->program) {
                    echo "Missing program: ".$record->title.PHP_EOL;
                    continue;
                }
                if ($record->program->channel_id == null) {
                    continue;
                }
                if (!$record->program->channel) {
                    echo "Missing program channel: ".$record->title. " / ".$record->program->name.' ('.$record->program->id.')'.PHP_EOL;
                    continue;
                }
                if (!$record->channel) {
                    echo "Missing channel: ".$record->title. " / ".$record->program->name.' ('.$record->program->id.')'.PHP_EOL;
                    continue;
                }
                if (!isset($programs[$record->program_id])) {
                   // echo "Missing program: ".$record->title.PHP_EOL;
                    continue;
                }
                if (!in_array($record->channel_id, $programs[$record->program_id])) {
                    if (!in_array(-1, $programs[$record->program_id])) {
                        echo "Mismatched: " . $record->title . " (".$record->channel->name.") / " . $record->program->name .' ('.$record->program->channel->name.')'.  PHP_EOL;
                        $action = select(
                            label: 'Action',
                            options: ['Add channel to program', 'Find similar by name', 'Create new', 'Skip']
                        );
                        if ($action == 'Add channel to program') {
                            AdditionalChannel::create(['program_id' => $record->program_id, 'channel_id' => $record->channel_id]);
                            $programs[$record->program_id][] = $record->channel_id;
                        }
                        if ($action == 'Find similar by name') {
                            $program = Program::where(['name' => $record->program->name, 'channel_id' => $record->channel_id])->first();
                            if ($program) {
                                echo "Updated program to ".$program->id.PHP_EOL;
                                $record->update(['program_id' => $program->id]);
                            } else {
                                echo "Similar program not found".PHP_EOL;
                            }
                        }
                        if ($action == 'Create new') {
                            $program = new Program(['name' => $record->program->name, 'channel_id' => $record->channel_id]);
                            $record->update(['program_id' => $program->id]);
                            $record->save();
                            echo "Created new program: ".$record->program->name.' ('.$record->channel->name.')'.PHP_EOL;
                        }
                    }

                }
            }
        });
    }
}
