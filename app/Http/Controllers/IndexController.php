<?php

namespace App\Http\Controllers;

use App\Constants\CacheTimes;
use App\Constants\Periods;
use App\Helpers\DatesHelper;
use App\Models\Article;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\ForumTopic;
use App\Models\Program;
use App\Models\Record;
use App\Models\Teletext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller {


    public function index() {
        $data = Cache::remember('index1', CacheTimes::PAGE_SHORT, function () {
            $data = [];
            $data['users_on_site'] = User::where('was_online', '>', Carbon::now()->subMinutes(15))->orderBy('was_online', 'desc')->get();

            $data['events'] = [];
            //HistoryEvent::where(['pending' => false])->orderBy('created_at', 'desc')->limit(8)->get();

            $data['first_news'] = Article::where(['pending' => false])->whereNotNull('cover_id')->orderBy('created_at', 'desc')->limit(2)->get();
            $data['news'] = Article::where(['pending' => false])->orderBy('created_at', 'desc')->whereNotIn('id', $data['first_news']->pluck('id'))->limit(5)->get();
            $data['records'] = [];

            $records_period_limit = 8;
            foreach (Periods::LIST as $period) {
                $period = [
                    'name' => $period['name'],
                    'records' =>  Record::where(['is_radio' => false, 'pending' => false])->whereBetween('year', $period['years'])->orderBy('original_added_at', 'desc')->limit($records_period_limit)->get()
                ];
                $data['records'][] = $period;
            }
            $data['records'] = array_reverse($data['records']);

            $forum_topics_limit = 8;
            $data['forum_topics'] = ForumTopic::orderBy('last_reply_at', 'DESC')->limit($forum_topics_limit)->get();

            $last_viewed_limit = 5;
            $data['last_viewed'] = Record::where(['is_radio' => false])->orderBy('updated_at', 'desc')->limit($last_viewed_limit)->get();

            $commercials_limit = 5;
            $data['commercials'] = Record::where(['is_radio' => false, 'is_advertising' => true])->orderBy('original_added_at', 'desc')->limit($commercials_limit)->get();

            $teletext_limit = 5;
            $data['teletext'] = Teletext::where(['pending' => false])->orderBy('created_at', 'desc')->limit($teletext_limit)->get();


            $in_this_day_limit = 5;
            $in_this_day_records = Record::where(['is_radio' => false, 'is_interprogram' => false, 'day' => date('d', time()), 'month' => date('m', time())])->inRandomOrder()->limit($in_this_day_limit)->get();
            $data['in_this_day'] = $in_this_day_records;

            $month_names = DatesHelper::monthNamesParentalCase();
            $date_text = date('d', time()) . ' ' . ($month_names[date('m', time()) - 1]);
            $data['date_text'] = $date_text;

            $data['comments'] = Comment::orderBy('id', 'desc')->limit(5)->get();
            $data['news_view'] = true;
            return $data;
        });
        return view('pages.index', $data);
    }

}
