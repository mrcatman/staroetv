<?php

namespace App\Console\Commands;

use App\Models\Program;
use Illuminate\Console\Command;

class MakeProgramGlobal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'program:make-global {name} {exclude?}';

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
        $name = $this->argument('name');
        $programs = Program::where('name', 'like', '%' . $name . '%');
        if ($this->argument('exclude') != '') {
            $programs = $programs->where('name', 'not like', '%' . $this->argument('exclude') . '%');
        }
        $programs = $programs->get();

        $first = $programs->first();
        echo "Base: ".$first->name." (".$first->channel->name.")".PHP_EOL;
        $first->channel_id = null;
        $first->save();

        foreach ($programs as $program) {
            if ($program->id != $first->id) {
                echo $program->name." (".($program->channel ? $program->channel->name : "-").")".PHP_EOL;
                foreach ($program->records as $record) {
                    $record->program_id = $first->id;
                    $record->save();
                }
                $program->delete();
            }
        }
    }
}
