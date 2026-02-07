<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Actions;
use App\Constants\Permissions;
use App\Helpers\ActionsLogHelper;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Channel;
use App\Models\Genre;
use App\Models\HistoryEvent;
use App\Models\Page;
use App\Models\Program;
use App\Models\Record;
use App\Models\Smile;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserGroupConfig;
use App\Models\UserReputation;
use Illuminate\Support\Facades\Hash;

class ProgramsController extends Controller {

    public function index() {
        return view("pages.admin.programs");
    }

    public function list() {
        $programs = Program::with('genre')->withCount('records');
        if (request()->has('sort.0.key')) {
            $programs = $programs->orderBy(request()->input('sort.0.key'), request()->input('sort.0.order'));
        } else {
            $programs = $programs->orderBy('id', 'desc');
        }
        if (request()->has('search')) {
            $programs = $programs->where(function ($q) {
                $q->where('name', 'like', '%' . request()->input('search') . '%');
            });
        }
        $programs = $programs->paginate(request()->input('count', 50))->through(fn ($program) => [
            ...$program->toArray(),
            'сhannel_id' => $program->channel?->id ?? '-',
            'сhannel_name' => $program->channel?->name ?? '-',
            'genre_name' => $program->genre?->name ?? '-',
        ]);
        return $programs;
    }


}
