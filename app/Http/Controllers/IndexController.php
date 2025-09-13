<?php

namespace App\Http\Controllers;

use App\Helpers\DatesHelper;
use App\Models\Article;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\ForumTopic;
use App\Models\Program;
use App\Models\Record;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller {


    public function index() {
        $data = Cache::remember('index', 5, function () {
            $data = []; // , \App\Models\Article::TYPE_BLOG
            $data['users_on_site'] = User::where('was_online', '>', Carbon::now()->subMinutes(15))->orderBy('was_online', 'desc')->get();

            $data['events'] = [];
            //HistoryEvent::where(['pending' => false])->orderBy('created_at', 'desc')->limit(8)->get();

            $data['first_news'] = Article::where(['pending' => false])->whereNotNull('cover_id')->orderBy('created_at', 'desc')->limit(2)->get();
            $data['news'] = Article::where(['pending' => false])->orderBy('created_at', 'desc')->whereNotIn('id', $data['first_news']->pluck('id'))->limit(8)->get();
            $data['records'] = Record::where(['is_radio' => false, 'pending' => false])->orderBy('original_added_at', 'desc')->limit(22)->get();
            $data['forum_topics'] = ForumTopic::orderBy('last_reply_at', 'DESC')->limit(6)->get();

            $last_viewed_limit = 5;
            $last_viewed = Record::where(['is_radio' => false])->orderBy('updated_at', 'desc')->limit($last_viewed_limit)->get();
            $data['last_viewed'] = $last_viewed;

            $in_this_day_limit = 5;
            $records = Record::where(['is_radio' => false, 'is_interprogram' => false, 'day' => date('d', time()), 'month' => date('m', time())])->inRandomOrder()->limit($in_this_day_limit)->get();
            $data['in_this_day'] = $records;

            $month_names = DatesHelper::monthNamesParentalCase();
            $date_text = date('d', time()) . ' ' . ($month_names[date('m', time()) - 1]);
            $data['date_text'] = $date_text;

            $data['comments'] = Comment::orderBy('id', 'desc')->limit(5)->get();
            $data['news_view'] = true;
            return $data;
        });
        return view('pages.index', $data);
    }

    public function promo()
    {
        $channels = Channel::where(['is_federal' => true, 'is_radio' => false])->orderBy('order', 'ASC')->get();
        $programs = Program::where(['channel_id' => 35])->orderBy('views', 'desc')->limit(20)->get();

        return view ('dev', [
            'channels' => $channels,
            'programs' => $programs
        ]);
    }

}
