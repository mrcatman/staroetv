<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use App\User;
use Carbon\Carbon;

Route::get('/', function () {
    $data = []; // , \App\Article::TYPE_BLOG
    $data['users_on_site'] = User::where('was_online', '>', Carbon::now()->subMinutes(15))->orderBy('was_online', 'desc')->get();

    $data['events'] = []; \App\HistoryEvent::where(['pending' => false])->orderBy('created_at', 'desc')->limit(8)->get();

    $data['first_news'] = \App\Article::where(['pending' => false])->whereNotNull('cover_id')->orderBy('created_at', 'desc')->limit(2)->get();
    $data['news'] = \App\Article::where(['pending' => false])->orderBy('created_at', 'desc')->whereNotIn('id',  $data['first_news']->pluck('id'))->limit(8)->get();
    $data['records'] = \App\Record::where(['is_radio' => false, 'pending' => false])->orderBy('original_added_at', 'desc')->limit(22)->get();
    $data['forum_topics'] = \App\ForumTopic::orderBy('last_reply_at', 'DESC')->limit(6)->get();
    $data['in_this_day'] = null;

    $last_viewed_limit = 5;
    $last_viewed = \App\Record::where(['is_radio' => false])->orderBy('updated_at', 'desc')->limit($last_viewed_limit)->get();
    $data['last_viewed'] = $last_viewed;


    $in_this_day_limit = 5;
    $records = \App\Record::where(['is_radio' => false, 'is_interprogram' => false, 'day' => date('d', time()), 'month' => date('m', time())])->inRandomOrder()->limit($in_this_day_limit)->get();
    $data['in_this_day'] = $records;

    $month_names = ["января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря"];
    $date_text = date('d', time()).' '.($month_names[date('m', time()) - 1]);
    $data['date_text'] = $date_text;

    $data['comments'] = \App\Comment::orderBy('id', 'desc')->limit(5)->get();
    $data['news_view'] = true;
    return view('index', $data);
});
Route::get('/new-comments', function () {
    $comments = \App\Comment::orderBy('id', 'desc')->where('material_type', '!=', '3')->paginate(24);
    return view("pages.users.comments", [
        'comments' => $comments,
        'user' => null,
    ]);
});

Route::get('/new-design', function () {
    return view('new-design');
});
Route::get('/new-design-v1', function () {
    return view('pages.new-design-v1');
});

// VIDEOS
Route::get('/video', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => false]);
});
Route::get('/video', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => false]);
});
Route::get('/video/add', function () {
    return (new \App\Http\Controllers\RecordsController())->add(['is_radio' => false]);
});
Route::any('/video/commercials', function () {
    return (new \App\Http\Controllers\RecordsController())->advertising(['is_radio' => false]);
});
Route::any('/video/commercials-search', function () {
    return (new \App\Http\Controllers\RecordsController())->advertisingBrands(['is_radio' => false]);
});
Route::any('/video/brands', function () {
    return (new \App\Http\Controllers\RecordsController())->advertisingBrands(['is_radio' => false]);
});
Route::any('/video/search', function () {
    return (new \App\Http\Controllers\RecordsController())->search(['is_radio' => false]);
});
Route::any('/video/other/{category}', function ($category_url) {
    return (new \App\Http\Controllers\RecordsController())->other(['is_radio' => false], $category_url);
});
Route::any('/video/other', function () {
    return (new \App\Http\Controllers\RecordsController())->other(['is_radio' => false]);
});

Route::any('/video/graphics', function () {
    return (new \App\Http\Controllers\RecordsController())->interprogramV2(['is_radio' => false]);
});
Route::any('/video/graphics/programs', function () {
    return (new \App\Http\Controllers\RecordsController())->programsGraphics(['is_radio' => false]);
});

Route::any('/video/graphics_old', function () {
    return (new \App\Http\Controllers\RecordsController())->interprogram(['is_radio' => false]);
});

Route::any('/video/youtube-ids/{author_id}', function ($author_id) {
    return (new \App\Http\Controllers\RecordsController())->getYoutubeVideoIds($author_id);
});
Route::any('/video/author/{author_id}', function ($author_id) {
    return (new \App\Http\Controllers\RecordsController())->getVideosForAuthor($author_id);
});

Route::get('/video/programs', function () {
    return (new \App\Http\Controllers\ProgramsController())->index(['is_radio' => false]);
});
Route::get('/video/programs/ajax', function () {
    return (new \App\Http\Controllers\ProgramsController())->loadAll(['is_radio' => false]);
});
Route::get('/video/calendar', 'RecordsController@calendar');
Route::get('/video/calendar/{year}', 'RecordsController@calendarYear');
Route::get('/video/calendar/{year}/{month}', 'RecordsController@calendarMonth');
Route::get('/video/{id}/edit', 'RecordsController@edit');
Route::get('/video/{id}', 'RecordsController@show');
Route::get('/video/vip/{id}/{channel?}/{url}', 'RecordsController@showOld');
Route::get('/video/vip/{id}//{url}', 'RecordsController@showOld');

Route::get('/mass-upload', 'MassUploadController@index');
Route::get('/mass-upload-list', 'MassUploadController@fetchList');
Route::post('/mass-upload', 'MassUploadController@fetchList');
Route::get('/mass-upload/from-device', 'MassUploadController@uploadFromDevice');
Route::get('/mass-upload/import-from-telegram', 'MassUploadController@importFromTelegram');
Route::get('/mass-upload/import-old-from-telegram', 'MassUploadController@importOldFromTelegram');

// RADIO
Route::get('/dir', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => true]);
});
Route::get('/radio', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => true]);
});

Route::any('/radio/search', function () {
    return (new \App\Http\Controllers\RecordsController())->search(['is_radio' => true]);
});
Route::any('/radio/other', function () {
    return (new \App\Http\Controllers\RecordsController())->other(['is_radio' => true]);
});
Route::any('/radio/commercials', function () {
    return (new \App\Http\Controllers\RecordsController())->advertising(['is_radio' => true]);
});
Route::any('/radio/commercials-search', function () {
    return (new \App\Http\Controllers\RecordsController())->advertisingBrands(['is_radio' => true]);
});
Route::any('/radio/brands', function () {
    return (new \App\Http\Controllers\RecordsController())->advertisingBrands(['is_radio' => true]);
});
Route::get('/radio/add', function () {
    return (new \App\Http\Controllers\RecordsController())->add(['is_radio' => true]);
});
Route::any('/radio/jingles', function () {
    return (new \App\Http\Controllers\RecordsController())->interprogram(['is_radio' => true]);
});
Route::get('/radio/programs', function () {
    return (new \App\Http\Controllers\ProgramsController())->index(['is_radio' => true]);
});
Route::get('/radio/programs/ajax', function () {
    return (new \App\Http\Controllers\ProgramsController())->loadAll(['is_radio' => true]);
});
Route::get('/radio/{id}', 'RecordsController@show');
Route::get('/radio/{id}/edit', 'RecordsController@edit');

Route::get('/embed/{id}', 'RecordsController@embed');

Route::post('/records/approve', 'RecordsController@approve');
Route::any('/records/search', function () {
    return (new \App\Http\Controllers\RecordsController())->search([]);
});
Route::post('/records/upload', 'RecordsController@upload');
Route::any('/records/after-upload', 'RecordsController@afterUpload');
Route::post('/records/download', 'RecordsController@download');
Route::post('/records/mass-edit', 'RecordsController@massEdit');
Route::post('/records/add', 'RecordsController@save');
Route::post('/records/{id}/edit', 'RecordsController@update');
Route::any('/records/getinfo', 'RecordsController@getInfo');
Route::post('/records/delete', 'RecordsController@delete');
Route::get('/records/categories', 'RecordsController@categories');
Route::any('/records/ajax', 'RecordsController@ajax');
Route::post('/records/screenshot', 'RecordsController@screenshot');
Route::post('/records/set-telegram-id', 'RecordsController@setTelegramID');
Route::get('/records/playlist-ajax/{id}','RecordsController@playlistAjax');

Route::post('/programs/approve', 'ProgramsController@approve');
Route::get('/programs/{id}', 'ProgramsController@show');
Route::get('/channels/{id}/programs/add', 'ProgramsController@add');
Route::post('/channels/{id}/programs/add', 'ProgramsController@save');
Route::get('/radio-stations/{id}/programs/add', 'ProgramsController@add');
Route::post('/radio-stations/{id}/programs/add', 'ProgramsController@save');
Route::get('/channels/{id}/programs/edit', 'ProgramsController@editList');
Route::post('/channels/{id}/programs/edit', 'ProgramsController@saveList');
Route::get('/radio-stations/{id}/programs/edit', 'ProgramsController@editList');
Route::post('/radio-stations/{id}/programs/edit', 'ProgramsController@saveList');
Route::get('/programs/{id}/edit', 'ProgramsController@edit');
Route::post('/programs/{id}/edit', 'ProgramsController@update');
Route::post('/programs/merge', 'ProgramsController@merge');
Route::post('/programs/delete', 'ProgramsController@delete');

Route::post('/channels/approve', 'ChannelsController@approve');
Route::get('/channels/add', 'ChannelsController@add');
Route::post('/channels/add', 'ChannelsController@save');
Route::get('/channels/ajax',  function () {
    return (new \App\Http\Controllers\ChannelsController())->getAjaxList(false);
});
Route::post('/channels/autocomplete', 'ChannelsController@autocomplete');
Route::get('/radio-stations/ajax',  function () {
    return (new \App\Http\Controllers\ChannelsController())->getAjaxList(true);
});
Route::get('/radio-stations/{id}', 'ChannelsController@show');
Route::get('/channels/{id}', 'ChannelsController@show');
Route::get('/radio-stations/{id}', 'ChannelsController@show');
Route::get('/channels/{id}/edit', 'ChannelsController@edit');
Route::post('/channels/{id}/edit', 'ChannelsController@update');
Route::post('/channels/merge', 'ChannelsController@merge');
Route::post('/channels/delete', 'ChannelsController@delete');
Route::get('/channels/{id}/programs', 'ChannelsController@getPrograms');

Route::get('/channels/{id}/graphics', function($id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->showAll(['channel_id' => $id]);
});

Route::get('/channels/{id}/graphics/ajax', function($id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->ajax(['channel_id' => $id]);
});
Route::get('/channels/{id}/graphics/add', function($id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->add(['channel_id' => $id]);
});
Route::post('/channels/{id}/graphics/add', function($id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->save(['channel_id' => $id]);
});
Route::get('/channels/{id}/graphics/edit/{package_id}', function($id, $package_id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->edit(['channel_id' => $id], $package_id);
});
Route::post('/channels/{id}/graphics/edit/{package_id}', function($id, $package_id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->update(['channel_id' => $id], $package_id);
});

Route::get('/programs/{id}/graphics/ajax', function($id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->ajax(['program_id' => $id]);
});
Route::get('/programs/{id}/graphics/add', function($id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->add(['program_id' => $id]);
});
Route::post('/programs/{id}/graphics/add', function($id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->save(['program_id' => $id]);
});
Route::get('/programs/{id}/graphics/edit/{package_id}', function($id, $package_id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->edit(['program_id' => $id], $package_id);
});
Route::post('/programs/{id}/graphics/edit/{package_id}', function($id, $package_id) {
    return (new \App\Http\Controllers\InterprogramPackagesController())->update(['program_id' => $id], $package_id);
});
Route::post('/programs/autocomplete', 'ProgramsController@autocomplete');

Route::get('/channels/{id}/graphics/{package_id}', 'InterprogramPackagesController@show');
Route::get('/programs/{id}/graphics', 'InterprogramPackagesController@showByProgram');
Route::post('/graphics/delete', 'InterprogramPackagesController@delete');

Route::post('/upload/pictures/by-url', 'UploadController@uploadPicturesByURL');
Route::get('/upload/pictures/getbychannel/{id}', 'UploadController@getPicturesByChannel');
Route::post('/upload/pictures', 'UploadController@uploadPictures');

Route::post('/comments/ajax', 'CommentsController@ajax');
Route::post('/comments/add', 'CommentsController@add');
Route::post('/comments/edit', 'CommentsController@edit');
Route::post('/comments/delete', 'CommentsController@delete');
Route::any('/comments/original/{id}', 'CommentsController@getOriginal');
Route::post('/comments/rating', 'CommentsController@rating');

Route::get('articles', 'ArticlesController@list');
Route::get('/blog', function () {
    return redirect('https://staroetv.su/articles');
});
Route::get('/news', function () {
    return redirect('https://staroetv.su/articles');
});

Route::get('/articles/add', 'ArticlesController@add');
Route::get('/articles/crosspost', 'ArticlesController@getCrosspostParameters');
Route::post('/articles/crosspost', 'ArticlesController@crosspost');
Route::post('/articles/delete', 'ArticlesController@delete');
Route::post('/articles/approve', 'ArticlesController@approve');
Route::post('/articles/actions', 'ArticlesController@getActions');
Route::post('/articles/change-type', 'ArticlesController@changeType');
Route::post('/articles/add', 'ArticlesController@save');
Route::get('/articles/edit/{id}', 'ArticlesController@edit');
Route::post('/articles/edit/{id}', 'ArticlesController@update');
Route::post('/articles/delete', 'ArticlesController@delete');
Route::get('/articles/{id}', 'ArticlesController@show');

Route::get('/blog/{id}', function ($path) {
    $data = explode("-", $path);
    if (!isset($data[3])) {
        return redirect("https://staroetv.su/articles");
    }
    return (new \App\Http\Controllers\ArticlesController())->redirect([
        'type_id' => \App\Article::TYPE_ARTICLES,
        'original_id' => $data[3]
    ]);
});

Route::get('/news/{id}', function ($path) {
    $data = explode("-", $path);
    if (!isset($data[3])) {
        return redirect("https://staroetv.su/articles");
    }
    return (new \App\Http\Controllers\ArticlesController())->redirect([
        'type_id' => \App\Article::TYPE_NEWS,
        'original_id' => $data[3]
    ]);
});


Route::get('/stuff/{category_id}-1-0-{id}', function ($category_id, $id) {
    return (new \App\Http\Controllers\ArticlesController())->redirect([
        'type_id' => \App\Article::TYPE_BLOG,
        'original_id' => $id
    ]);
});




// FORUM

Route::get('/forum', 'ForumController@index');
Route::any('/forum/get-edit-form', 'ForumController@getEditForm');

Route::get('/forum/{id}/new-topic', 'ForumController@newTopic');
Route::post('/forum/{id}/new-topic', 'ForumController@createTopic');
Route::get('/forum/edit-topic/{id}', 'ForumController@editTopic');
Route::post('/forum/edit-topic/{id}', 'ForumController@saveTopic');
Route::post('/forum/move-topic', 'ForumController@moveTopic');
Route::post('/forum/delete-topic', 'ForumController@deleteTopic');
Route::get('/forum/profile/{id}', 'ForumController@getProfile');

Route::get('/forum/new-section', 'ForumController@newSection');
Route::get('/forum/{id}/new', 'ForumController@newForum');
Route::get('/forum/edit/{id}', 'ForumController@editForum');
Route::post('/forum/new', 'ForumController@createForum');
Route::post('/forum/edit/{id}', 'ForumController@saveForum');

Route::post('/forum/post-message', 'ForumController@postMessage');
Route::post('/forum/edit-message', 'ForumController@editMessage');
Route::post('/forum/delete-message', 'ForumController@deleteMessage');

Route::get('/forum/0-0-1-34', function () {
    return redirect("https://staroetv.su/forum/last-topics");
});

Route::get('/forum/last-topics', 'ForumController@lastTopics');
Route::get('/forum/user-messages/{user_id}', 'ForumController@userMessages');
Route::get('/forum/0-{message_id}', 'ForumController@redirectToMessageById');
Route::get('/forum/{id}-0-{page_id}', 'ForumController@subforum');
Route::get('/forum/{forum_id}-{topic_id}-0-17-1', 'ForumController@redirectToLastMessage');
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{time}', 'ForumController@redirectToMessage');
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{page_id}-{time}', 'ForumController@redirectToMessage');
Route::get('/forum/{forum_id}-{topic_id}-{page_id}', 'ForumController@showTopic');
Route::get('/forum/{forum_id}-{topic_id}', 'ForumController@showTopic');

Route::get('/forum/{id}', 'ForumController@subforum');
Route::post('/questionnaire/vote', 'QuestionnairesController@vote');
Route::post('/questionnaire/form', 'QuestionnairesController@form');

Route::post('reputation/ajax', 'ReputationController@ajax');
Route::post('reputation/change', 'ReputationController@change');
Route::post('reputation/edit', 'ReputationController@edit');
Route::post('reputation/delete', 'ReputationController@delete');
Route::post('reputation/reply', 'ReputationController@reply');

Route::post('awards/ajax', 'AwardsController@ajax');
Route::post('awards/list', 'AwardsController@list');
Route::post('awards/give-out', 'AwardsController@create');
Route::post('awards/edit', 'AwardsController@edit');
Route::post('awards/delete', 'AwardsController@delete');

Route::post('warnings/ajax', 'WarningsController@ajax');
Route::post('warnings/form', 'WarningsController@form');
Route::post('warnings/add', 'WarningsController@add');


// CONTACT FORM
Route::get('/index/0-3', 'ContactFormController@show');
Route::get('/contact', 'ContactFormController@show');
Route::post('contact', 'ContactFormController@send');

//PAGES
Route::get('/index/0-{id}', 'PagesController@show');
Route::get('/pages/add', 'PagesController@add');
Route::post('/pages/add', 'PagesController@save');
Route::get('/pages/{url}', 'PagesController@showByURL');
Route::get('/pages/{id}/edit', 'PagesController@edit');
Route::post('/pages/{id}/edit', 'PagesController@update');
Route::post('/pages/delete', 'PagesController@delete');
Route::get('/team', 'PagesController@team');
//USERS

Route::post('/users/autocomplete', 'UsersController@autocomplete');

Route::get('/index/8{id?}', function ($path = null) {
    if (!$path) {
        $data = [0, 0];
    } else {
        $data = explode("-", $path);
    }
    if (count($data) == 2) {
        $id = $data[1];
        if ($id == 0) {
            if ($user = auth()->user()) {
                return (new \App\Http\Controllers\UsersController())->show([
                    'id' => $user->id
                ]);
            } else {
                return view("pages.errors.403");
            }
        }
        return (new \App\Http\Controllers\UsersController())->show([
            'id' => $id
        ]);
    } else {
        return (new \App\Http\Controllers\UsersController())->show([
            'username' => $data[2]
        ]);
    }
});
Route::get('/users/{id}', function ($id) {
    return (new \App\Http\Controllers\UsersController())->show([
        'id' => $id
    ]);
});
Route::get('/index/15', 'UsersController@list');
Route::get('/index/15-{page}', 'UsersController@list');
Route::post('/index/15', 'UsersController@list');
Route::get('/index/11', 'UsersController@edit');
Route::get('/index/11-{id}-0-1', 'UsersController@edit');
Route::get('/profile/edit', 'UsersController@edit');
Route::get('/profile/edit/{id}', 'UsersController@edit');
Route::post('/profile/edit', 'UsersController@save');
Route::get('/profile/password', 'UsersController@editPassword');
Route::post('/profile/password', 'UsersController@savePassword');
Route::get('/index/34-{id}', 'UsersController@comments');

Route::get('/users/change-email/{code}', 'UsersController@changeEmail');
Route::get('/users/{id}/comments', 'UsersController@comments');
Route::get('/users/{id}/videos', 'UsersController@videos');
Route::get('/users/{id}/radio', 'UsersController@radioRecordings');
Route::get('/profile/notifications', 'UsersController@getNotifications');
// PM
Route::get('/pm', 'PrivateMessagesController@index');
Route::get('/index/14', 'PrivateMessagesController@index');
Route::post('/pm/update', 'PrivateMessagesController@update');
Route::get('/pm/send', 'PrivateMessagesController@send');
Route::post('/pm/send', 'PrivateMessagesController@post');
Route::post('/pm/delete', 'PrivateMessagesController@delete');
Route::post('/pm/cancel', 'PrivateMessagesController@cancel');
Route::get('/pm/{id}', 'PrivateMessagesController@show');

// CUTTING
Route::any('/cut/download-external', 'VideoCutController@downloadExternal');
Route::any('/cut/downloaded/{id}', 'VideoCutController@onDownloaded');
Route::get('/cut/{id}', 'VideoCutController@show');
Route::post('/cut/{id}', 'VideoCutController@save');
Route::any('/cut/{id}/make-video/{index}', 'VideoCutController@makeVideo');
Route::get('/cut/start/{id}', 'VideoCutController@showForm');
Route::post('/cut/start/{id}', 'VideoCutController@start');

// EVENTS
Route::get('/events', 'HistoryEventsController@index');
Route::post('/events/add', 'HistoryEventsController@save');
Route::get('/events/add', 'HistoryEventsController@add');
Route::get('/events/{id}', 'HistoryEventsController@show');
Route::get('/events/{id}/edit', 'HistoryEventsController@edit');
Route::post('/events/{id}/edit', 'HistoryEventsController@update');
Route::post('/events/approve', 'HistoryEventsController@approve');
Route::post('/events/delete', 'HistoryEventsController@delete');

// TOP LISTS
Route::any('/top-list/videos', 'TopListController@videos');
Route::any('/top-list/radio-recordings', 'TopListController@radioRecordings');
Route::any('/top-list/news', 'TopListController@news');
Route::any('/top-list/articles', 'TopListController@articles');
Route::any('/top-list/forum', 'TopListController@forum');
Route::any('/top-list/comments', 'TopListController@comments');
Route::any('/top-list/awards', 'TopListController@awards');
Route::any('/top-list/reputation', 'TopListController@reputation');

//REDACTOR
Route::get('/redactor-panel', 'AdminController@editorPanel');


Route::any('/smiles', function() {
    $smiles = \App\Smile::all();
    return [
        'status' => 1,
        'data' => [
            'title' => 'Все смайлы',
            'html' => view('blocks/bb_editor_smiles', ['smiles' => $smiles])->render()
        ]
    ];
});

Route::get('/go', function () {
    $path = explode("/go?",$_SERVER['REQUEST_URI'])[1];
    return view('pages.redirect', ['path' => $path]);
    //return redirect($path);
});


Route::any('/site-search', 'SiteSearchController@search');

// ADMIN
Route::get('/records/dailymotion', function () {
    $record_ids = \App\Record::where('embed_code', 'LIKE', '%dailymotion%')->where(['use_own_player' => false])->where(function($q) {
        $q->where(['is_interprogram' => true]);
        $q->orWhere(['is_advertising' => true]);
    })->pluck('id');
    return $record_ids;
});
Route::get('/records/get-download-ids', function () {
    $record_ids = \App\Record::where(['interprogram_package_id' => 1, 'use_own_player' => false])->pluck('id');
    return $record_ids;
});
Route::get('/records/dailymotion_list', function () {
    $records = \App\Record::where('embed_code', 'LIKE', '%dailymotion%')->where(['use_own_player' => false])->where(function($q) {
        $q->where(['is_interprogram' => false]);
        $q->where(['is_advertising' => false]);
       // $q->whereNotIn('program_id',  [1541, 643]);
    })->whereDate('supposed_date','<', \Carbon\Carbon::createFromDate(2008, 1, 1))->pluck('title');
    echo implode("<br>", $records->toArray());
});


Route::get('/records/dailymotion_download', function () {
    ini_set('max_execution_time', '600');
    $record_ids = \App\Record::where('embed_code', 'LIKE', '%dailymotion%')->where(['use_own_player' => false])->pluck('id');
    $index = request()->input('index', 0);
    $response = (new \App\Http\Controllers\RecordsController())->download($record_ids[$index]);
    $record = \App\Record::find($record_ids[$index]);
    var_dump($record->title, $response);
    $index++;
    return "<meta http-equiv='refresh' content='1;url=/records/dailymotion_download?index=$index' />";
});

Route::any('admin-login', function() {
    return view("pages.maintenance_login");
});

Route::middleware(\App\Http\Middleware\checkAdmin::class)->prefix('admin')->group(function () {
	Route::get('', function() {
		return redirect("https://staroetv.su/admin/pages");
	});

    Route::get('smiles', 'AdminController@getSmiles');
    Route::post('smiles', 'AdminController@saveSmiles');

    Route::resource('user-groups', 'UserGroupsController');

    Route::get('permissions', 'AdminController@getPermissions');
    Route::post('permissions', 'AdminController@savePermissions');

    Route::get('channels', 'AdminController@getChannels');
    Route::get('channels/order', 'AdminController@getChannelsOrder');
    Route::post('channels/order', 'AdminController@setChannelsOrder');

    Route::get('users', 'AdminController@getUsers');
    Route::post('users/change-group', 'AdminController@changeUserGroup');
    Route::post('users/change-password', 'AdminController@changeUserPassword');
    Route::post('users/delete', 'AdminController@deleteUser');

    Route::get('pages', 'AdminController@getPages');
    Route::get('crossposting', 'CrosspostController@getServices');

    Route::get('categories', 'AdminController@getCategories');
    Route::post('categories', 'AdminController@saveCategories');

    Route::get('reputation', 'AdminController@getReputationHistory');

    Route::get('run-command', function () {
        if (request()->has('command')) {
            Artisan::call(request()->input('command'));
        }
    });
});

// CROSSPOST
Route::middleware(\App\Http\Middleware\checkCanCrosspost::class)->group(function() {
    Route::get('/crossposts', 'CrosspostController@index');
    Route::get('/crossposts/add', 'CrosspostController@add');
    Route::post('/crossposts/add', 'CrosspostController@save');
    Route::get('/crossposts/{id}/edit', 'CrosspostController@edit');
    Route::post('/crossposts/{id}/edit', 'CrosspostController@update');
    Route::any('/crossposts/{id}/make-post/{service}', 'CrosspostController@makePost');
    Route::any('/crossposts/{id}/delete-post/{service}', 'CrosspostController@deletePost');
    Route::post('/crossposts/delete', 'CrosspostController@delete');
});

Route::middleware(\App\Http\Middleware\checkAdmin::class)->group(function() {
    Route::get('/crosspost/autoconnect/{name}', 'CrosspostController@autoconnect')->name('crosspostAutoconnect');
    Route::post('/crosspost/settings/{name}', 'CrosspostController@saveSettings')->name('crosspostSaveSettings');
    Route::get('/crosspost/redirect/{name}', 'CrosspostController@afterRedirect')->name('crosspostRedirectUri');
});
Route::get('forgot-password', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('forgot-password', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
Route::get('/confirm-account/{code}', 'Auth\RegisterController@confirm');
Auth::routes();
