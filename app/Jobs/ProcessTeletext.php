<?php

namespace App\Jobs;

use App\Helpers\TeletextHelper;
use App\Models\Teletext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessTeletext implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Teletext $teletext,
    ) { }

    public function handle(): void
    {
        TeletextHelper::process($this->teletext);
    }
}
