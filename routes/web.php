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


use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\AwardsController;
use App\Http\Controllers\ChannelsController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\CrosspostController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\HistoryEventsController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\InterprogramPackagesController;
use App\Http\Controllers\MassUploadController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PrivateMessagesController;
use App\Http\Controllers\ProgramsController;
use App\Http\Controllers\QuestionnairesController;
use App\Http\Controllers\RecordsController;
use App\Http\Controllers\ReputationController;
use App\Http\Controllers\SiteSearchController;
use App\Http\Controllers\TeletextController;
use App\Http\Controllers\TopListController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WarningsController;

Route::get('/', [IndexController::class, 'index']);
Route::get('/promo', [IndexController::class, 'promo']);

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

Route::any('/video/graphics', [InterprogramPackagesController::class, 'index']);
Route::any('/video/graphics/programs', [InterprogramPackagesController::class, 'program']);

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
Route::get('/video/calendar', [RecordsController::class, 'calendar']);
Route::get('/video/calendar/{year}', [RecordsController::class, 'calendarYear']);
Route::get('/video/calendar/{year}/{month}', [RecordsController::class, 'calendarMonth']);
Route::get('/video/{id}/edit', [RecordsController::class, 'edit']);
Route::get('/video/{id}', [RecordsController::class, 'show']);
Route::get('/video/vip/{id}/{channel?}/{url}', [RecordsController::class, 'showOld']);
Route::get('/video/vip/{id}//{url}', [RecordsController::class, 'showOld']);

Route::get('/mass-upload', [MassUploadController::class, 'index']);
Route::get('/mass-upload-list', [MassUploadController::class, 'fetchList']);
Route::post('/mass-upload', [MassUploadController::class, 'fetchList']);
Route::get('/mass-upload/from-device', [MassUploadController::class, 'uploadFromDevice']);
Route::get('/mass-upload/import-from-telegram', [MassUploadController::class, 'importFromTelegram']);
Route::get('/mass-upload/import-old-from-telegram', [MassUploadController::class, 'importOldFromTelegram']);

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
Route::get('/radio/{id}', [RecordsController::class, 'show']);
Route::get('/radio/{id}/edit', [RecordsController::class, 'edit']);

Route::get('/embed/{id}', [RecordsController::class, 'embed']);

Route::post('/records/approve', [RecordsController::class, 'approve']);
Route::any('/records/search', function () {
    return (new \App\Http\Controllers\RecordsController())->search([]);
});
Route::post('/records/upload', [RecordsController::class, 'upload']);
Route::any('/records/after-upload', [RecordsController::class, 'afterUpload']);
Route::post('/records/download', [RecordsController::class, 'download']);
Route::post('/records/mass-edit', [RecordsController::class, 'massEdit']);
Route::post('/records/add', [RecordsController::class, 'save']);
Route::post('/records/{id}/edit', [RecordsController::class, 'update']);
Route::any('/records/getinfo', [RecordsController::class, 'getInfo']);
Route::post('/records/delete', [RecordsController::class, 'delete']);
Route::get('/records/categories', [RecordsController::class, 'categories']);
Route::any('/records/ajax', [RecordsController::class, 'ajax']);
Route::post('/records/screenshot', [RecordsController::class, 'screenshot']);
Route::post('/records/set-telegram-id', [RecordsController::class, 'setTelegramID']);
Route::get('/records/playlist-ajax/{id}', [RecordsController::class, 'playlistAjax']);

Route::post('/programs/approve', [ProgramsController::class, 'approve']);
Route::get('/programs/{id}', [ProgramsController::class, 'show']);
Route::get('/channels/{id}/programs/add', [ProgramsController::class, 'add']);
Route::post('/channels/{id}/programs/add', [ProgramsController::class, 'save']);
Route::get('/radio-stations/{id}/programs/add', [ProgramsController::class, 'add']);
Route::post('/radio-stations/{id}/programs/add', [ProgramsController::class, 'save']);
Route::get('/channels/{id}/programs/edit', [ProgramsController::class, 'editList']);
Route::post('/channels/{id}/programs/edit', [ProgramsController::class, 'saveList']);
Route::get('/radio-stations/{id}/programs/edit', [ProgramsController::class, 'editList']);
Route::post('/radio-stations/{id}/programs/edit', [ProgramsController::class, 'saveList']);
Route::get('/programs/{id}/edit', [ProgramsController::class, 'edit']);
Route::post('/programs/{id}/edit', [ProgramsController::class, 'update']);
Route::post('/programs/merge', [ProgramsController::class, 'merge']);
Route::post('/programs/delete', [ProgramsController::class, 'delete']);

Route::post('/channels/approve', [ChannelsController::class, 'approve']);
Route::get('/channels/add', [ChannelsController::class, 'add']);
Route::post('/channels/add', [ChannelsController::class, 'save']);
Route::get('/channels/ajax',  function () {
    return (new \App\Http\Controllers\ChannelsController())->getAjaxList(false);
});
Route::post('/channels/autocomplete', [ChannelsController::class, 'autocomplete']);
Route::get('/radio-stations/ajax',  function () {
    return (new \App\Http\Controllers\ChannelsController())->getAjaxList(true);
});
Route::get('/radio-stations/{id}', [ChannelsController::class, 'show']);
Route::get('/channels/{id}', [ChannelsController::class, 'show']);
Route::get('/radio-stations/{id}', [ChannelsController::class, 'show']);
Route::get('/channels/{id}/edit', [ChannelsController::class, 'edit']);
Route::post('/channels/{id}/edit', [ChannelsController::class, 'update']);
Route::post('/channels/merge', [ChannelsController::class, 'merge']);
Route::post('/channels/delete', [ChannelsController::class, 'delete']);
Route::get('/channels/{id}/programs', [ChannelsController::class, 'getPrograms']);

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
Route::post('/programs/autocomplete', [ProgramsController::class, 'autocomplete']);

Route::get('/channels/{id}/graphics/{package_id}', [InterprogramPackagesController::class, 'show']);
Route::get('/programs/{id}/graphics', [InterprogramPackagesController::class, 'showByProgram']);
Route::post('/graphics/delete', [InterprogramPackagesController::class, 'delete']);


// TELETEXT
Route::get('/teletext', [TeletextController::class, 'index']);
Route::get('/teletext/add', [TeletextController::class, 'add']);
Route::post('/teletext/add', [TeletextController::class, 'save']);
Route::get('/teletext/{id}', [TeletextController::class, 'show']);
Route::get('/teletext/{id}/edit', [TeletextController::class, 'edit']);
Route::post('/teletext/{id}/edit', [TeletextController::class, 'update']);
Route::post('/teletext/approve', [TeletextController::class, 'approve']);


Route::post('/upload/pictures/by-url', [UploadController::class, 'uploadPicturesByURL']);
Route::get('/upload/pictures/getbychannel/{id}', [UploadController::class, 'getPicturesByChannel']);
Route::post('/upload/pictures', [UploadController::class, 'uploadPictures']);

Route::post('/comments/ajax', [CommentsController::class, 'ajax']);
Route::post('/comments/add', [CommentsController::class, 'add']);
Route::post('/comments/edit', [CommentsController::class, 'edit']);
Route::post('/comments/delete', [CommentsController::class, 'delete']);
Route::any('/comments/original/{id}', [CommentsController::class, 'getOriginal']);
Route::post('/comments/rating', [CommentsController::class, 'rating']);

Route::get('/new-comments', function () {

});

Route::get('articles', [ArticlesController::class, 'list']);
Route::get('/blog', function () {
    return redirect('/articles');
});
Route::get('/news', function () {
    return redirect('/articles');
});

Route::get('/articles/add', [ArticlesController::class, 'add']);
Route::get('/articles/crosspost', [ArticlesController::class, 'getCrosspostParameters']);
Route::post('/articles/crosspost', [ArticlesController::class, 'crosspost']);
Route::post('/articles/delete', [ArticlesController::class, 'delete']);
Route::post('/articles/approve', [ArticlesController::class, 'approve']);
Route::post('/articles/actions', [ArticlesController::class, 'getActions']);
Route::post('/articles/change-type', [ArticlesController::class, 'changeType']);
Route::post('/articles/add', [ArticlesController::class, 'save']);
Route::get('/articles/edit/{id}', [ArticlesController::class, 'edit']);
Route::post('/articles/edit/{id}', [ArticlesController::class, 'update']);
Route::post('/articles/delete', [ArticlesController::class, 'delete']);
Route::get('/articles/{id}', [ArticlesController::class, 'show']);

Route::get('/blog/{id}', function ($path) {
    $data = explode("-", $path);
    if (!isset($data[3])) {
        return redirect("/articles");
    }
    return (new \App\Http\Controllers\ArticlesController())->redirect([
        'type_id' => \App\Models\Article::TYPE_ARTICLES,
        'original_id' => $data[3]
    ]);
});

Route::get('/news/{id}', function ($path) {
    $data = explode("-", $path);
    if (!isset($data[3])) {
        return redirect("/articles");
    }
    return (new ArticlesController())->redirect([
        'type_id' => \App\Models\Article::TYPE_NEWS,
        'original_id' => $data[3]
    ]);
});


Route::get('/stuff/{category_id}-1-0-{id}', function ($category_id, $id) {
    return (new \App\Http\Controllers\ArticlesController())->redirect([
        'type_id' => \App\Models\Article::TYPE_BLOG,
        'original_id' => $id
    ]);
});




// FORUM

Route::get('/forum', [ForumController::class, 'index']);
Route::any('/forum/get-edit-form', [ForumController::class, 'getEditForm']);

Route::get('/forum/{id}/new-topic', [ForumController::class, 'newTopic']);
Route::post('/forum/{id}/new-topic', [ForumController::class, 'createTopic']);
Route::get('/forum/edit-topic/{id}', [ForumController::class, 'editTopic']);
Route::post('/forum/edit-topic/{id}', [ForumController::class, 'saveTopic']);
Route::post('/forum/move-topic', [ForumController::class, 'moveTopic']);
Route::post('/forum/delete-topic', [ForumController::class, 'deleteTopic']);
Route::get('/forum/profile/{id}', [ForumController::class, 'getProfile']);

Route::get('/forum/new-section', [ForumController::class, 'newSection']);
Route::get('/forum/{id}/new', [ForumController::class, 'newForum']);
Route::get('/forum/edit/{id}', [ForumController::class, 'editForum']);
Route::post('/forum/new', [ForumController::class, 'createForum']);
Route::post('/forum/edit/{id}', [ForumController::class, 'saveForum']);

Route::post('/forum/post-message', [ForumController::class, 'postMessage']);
Route::post('/forum/edit-message', [ForumController::class, 'editMessage']);
Route::post('/forum/delete-message', [ForumController::class, 'deleteMessage']);

Route::get('/forum/0-0-1-34', function () {
    return redirect("/forum/last-topics");
});

Route::get('/forum/last-topics', [ForumController::class, 'lastTopics']);
Route::get('/forum/user-messages/{user_id}', [ForumController::class, 'userMessages']);
Route::get('/forum/0-{message_id}', [ForumController::class, 'redirectToMessageById']);
Route::get('/forum/{id}-0-{page_id}', [ForumController::class, 'subforum']);
Route::get('/forum/{forum_id}-{topic_id}-0-17-1', [ForumController::class, 'redirectToLastMessage']);
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{time}', [ForumController::class, 'redirectToMessage']);
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{page_id}-{time}', [ForumController::class, 'redirectToMessage']);
Route::get('/forum/{forum_id}-{topic_id}-{page_id}', [ForumController::class, 'showTopic']);
Route::get('/forum/{forum_id}-{topic_id}', [ForumController::class, 'showTopic']);

Route::get('/forum/{id}', [ForumController::class, 'subforum']);
Route::post('/questionnaire/vote', [QuestionnairesController::class, 'vote']);
Route::post('/questionnaire/form', [QuestionnairesController::class, 'form']);

Route::post('reputation/ajax', [ReputationController::class, 'ajax']);
Route::post('reputation/change', [ReputationController::class, 'change']);
Route::post('reputation/edit', [ReputationController::class, 'edit']);
Route::post('reputation/delete', [ReputationController::class, 'delete']);
Route::post('reputation/reply', [ReputationController::class, 'reply']);

Route::post('awards/ajax', [AwardsController::class, 'ajax']);
Route::post('awards/list', [AwardsController::class, 'list']);
Route::post('awards/give-out', [AwardsController::class, 'create']);
Route::post('awards/edit', [AwardsController::class, 'edit']);
Route::post('awards/delete', [AwardsController::class, 'delete']);

Route::post('warnings/ajax', [WarningsController::class, 'ajax']);
Route::post('warnings/form', [WarningsController::class, 'form']);
Route::post('warnings/add', [WarningsController::class, 'add']);


// CONTACT FORM
Route::get('/index/0-3', [ContactFormController::class, 'show']);
Route::get('/contact', [ContactFormController::class, 'show']);
Route::post('contact', [ContactFormController::class, 'send']);

//PAGES
Route::get('/index/0-{id}', [\App\Http\Controllers\PagesController::class, 'show']);
Route::get('/pages/add', [PagesController::class, 'add']);
Route::post('/pages/add', [PagesController::class, 'save']);
Route::get('/pages/{url}', [PagesController::class, 'showByURL']);
Route::get('/pages/{id}/edit', [PagesController::class, 'edit']);
Route::post('/pages/{id}/edit', [PagesController::class, 'update']);
Route::post('/pages/delete', [PagesController::class, 'delete']);
Route::get('/team', [PagesController::class, 'team']);
//USERS

Route::post('/users/autocomplete', [UsersController::class, 'autocomplete']);

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
Route::get('/index/15', [UsersController::class, 'list']);
Route::get('/index/15-{page}', [UsersController::class, 'list']);
Route::post('/index/15', [UsersController::class, 'list']);
Route::get('/index/11', [UsersController::class, 'edit']);
Route::get('/index/11-{id}-0-1', [UsersController::class, 'edit']);
Route::get('/profile/edit', [UsersController::class, 'edit']);
Route::get('/profile/edit/{id}', [UsersController::class, 'edit']);
Route::post('/profile/edit', [UsersController::class, 'save']);
Route::get('/profile/password', [UsersController::class, 'editPassword']);
Route::post('/profile/password', [UsersController::class, 'savePassword']);
Route::get('/index/34-{id}', [UsersController::class, 'comments']);

Route::get('/users/change-email/{code}', [UsersController::class, 'changeEmail']);
Route::get('/users/{id}/comments', [UsersController::class, 'comments']);
Route::get('/users/{id}/videos', [UsersController::class, 'videos']);
Route::get('/users/{id}/radio', [UsersController::class, 'radioRecordings']);
Route::get('/profile/notifications', [UsersController::class, 'getNotifications']);
// PM
Route::get('/pm', [PrivateMessagesController::class, 'index']);
Route::get('/index/14', [PrivateMessagesController::class, 'index']);
Route::post('/pm/update', [PrivateMessagesController::class, 'update']);
Route::get('/pm/send', [PrivateMessagesController::class, 'send']);
Route::post('/pm/send', [PrivateMessagesController::class, 'post']);
Route::post('/pm/delete', [PrivateMessagesController::class, 'delete']);
Route::post('/pm/cancel', [PrivateMessagesController::class, 'cancel']);
Route::get('/pm/{id}', [PrivateMessagesController::class, 'show']);

// CUTTING
Route::any('/cut/download-external', [VideoCutController::class, 'downloadExternal']);
Route::any('/cut/downloaded/{id}', [VideoCutController::class, 'onDownloaded']);
Route::get('/cut/{id}', [VideoCutController::class, 'show']);
Route::post('/cut/{id}', [VideoCutController::class, 'save']);
Route::any('/cut/{id}/make-video/{index}', [VideoCutController::class, 'makeVideo']);
Route::get('/cut/start/{id}', [VideoCutController::class, 'showForm']);
Route::post('/cut/start/{id}', [VideoCutController::class, 'start']);

// EVENTS
Route::get('/events', [HistoryEventsController::class, 'index']);
Route::post('/events/add', [HistoryEventsController::class, 'save']);
Route::get('/events/add', [HistoryEventsController::class, 'add']);
Route::get('/events/{id}', [HistoryEventsController::class, 'show']);
Route::get('/events/{id}/edit', [HistoryEventsController::class, 'edit']);
Route::post('/events/{id}/edit', [HistoryEventsController::class, 'update']);
Route::post('/events/approve', [HistoryEventsController::class, 'approve']);
Route::post('/events/delete', [HistoryEventsController::class, 'delete']);

// TOP LISTS
Route::any('/top-list/videos', [TopListController::class, 'videos']);
Route::any('/top-list/radio-recordings', [TopListController::class, 'radioRecordings']);
Route::any('/top-list/news', [TopListController::class, 'news']);
Route::any('/top-list/articles', [TopListController::class, 'articles']);
Route::any('/top-list/forum', [TopListController::class, 'forum']);
Route::any('/top-list/comments', [TopListController::class, 'comments']);
Route::any('/top-list/awards', [TopListController::class, 'awards']);
Route::any('/top-list/reputation', [TopListController::class, 'reputation']);

//REDACTOR
Route::get('/redactor-panel', [AdminController::class, 'editorPanel']);


Route::any('/smiles', function() {
    $smiles = \App\Models\Smile::all();
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


Route::any('/site-search', [SiteSearchController::class, 'search']);

// ADMIN
Route::get('/records/dailymotion', function () {
    $record_ids = \App\Models\Record::where('embed_code', 'LIKE', '%dailymotion%')->where(['use_own_player' => false])->where(function($q) {
        $q->where(['is_interprogram' => true]);
        $q->orWhere(['is_advertising' => true]);
    })->pluck('id');
    return $record_ids;
});
Route::get('/records/get-download-ids', function () {
    $record_ids = \App\Models\Record::where(['interprogram_package_id' => 1, 'use_own_player' => false])->pluck('id');
    return $record_ids;
});
Route::get('/records/dailymotion_list', function () {
    $records = \App\Models\Record::where('embed_code', 'LIKE', '%dailymotion%')->where(['use_own_player' => false])->where(function($q) {
        $q->where(['is_interprogram' => false]);
        $q->where(['is_advertising' => false]);
       // $q->whereNotIn('program_id',  [1541, 643]);
    })->whereDate('supposed_date','<', \Carbon\Carbon::createFromDate(2008, 1, 1))->pluck('title');
    echo implode("<br>", $records->toArray());
});


Route::get('/records/dailymotion_download', function () {
    ini_set('max_execution_time', '600');
    $record_ids = \App\Models\Record::where('embed_code', 'LIKE', '%dailymotion%')->where(['use_own_player' => false])->pluck('id');
    $index = request()->input('index', 0);
    $response = (new \App\Http\Controllers\RecordsController())->download($record_ids[$index]);
    $record = \App\Models\Record::find($record_ids[$index]);
    var_dump($record->title, $response);
    $index++;
    return "<meta http-equiv='refresh' content='1;url=/records/dailymotion_download?index=$index' />";
});

Route::any('admin-login', function() {
    return view("pages.maintenance_login");
});

Route::middleware(\App\Http\Middleware\checkAdmin::class)->prefix('admin')->group(function () {
	Route::get('', function() {
		return redirect("/admin/pages");
	});

    Route::get('smiles', [AdminController::class, 'getSmiles']);
    Route::post('smiles', [AdminController::class, 'saveSmiles']);

    Route::resource('user-groups', 'UserGroupsController');

    Route::get('permissions', [AdminController::class, 'getPermissions']);
    Route::post('permissions', [AdminController::class, 'savePermissions']);

    Route::get('channels', [AdminController::class, 'getChannels']);
    Route::get('channels/order', [AdminController::class, 'getChannelsOrder']);
    Route::post('channels/order', [AdminController::class, 'setChannelsOrder']);

    Route::get('users', [AdminController::class, 'getUsers']);
    Route::post('users/change-group', [AdminController::class, 'changeUserGroup']);
    Route::post('users/change-password', [AdminController::class, 'changeUserPassword']);
    Route::post('users/delete', [AdminController::class, 'deleteUser']);

    Route::get('pages', [AdminController::class, 'getPages']);
    Route::get('crossposting', [CrosspostController::class, 'getServices']);

    Route::get('categories', [AdminController::class, 'getCategories']);
    Route::post('categories', [AdminController::class, 'saveCategories']);

    Route::get('reputation', [AdminController::class, 'getReputationHistory']);

    Route::get('run-command', function () {
        if (request()->has('command')) {
            Artisan::call(request()->input('command'));
        }
    });
});

// CROSSPOST
Route::middleware(\App\Http\Middleware\checkCanCrosspost::class)->group(function() {
    Route::get('/crossposts', [CrosspostController::class, 'index']);
    Route::get('/crossposts/add', [CrosspostController::class, 'add']);
    Route::post('/crossposts/add', [CrosspostController::class, 'save']);
    Route::get('/crossposts/{id}/edit', [CrosspostController::class, 'edit']);
    Route::post('/crossposts/{id}/edit', [CrosspostController::class, 'update']);
    Route::any('/crossposts/{id}/make-post/{service}', [CrosspostController::class, 'makePost']);
    Route::any('/crossposts/{id}/delete-post/{service}', [CrosspostController::class, 'deletePost']);
    Route::post('/crossposts/delete', [CrosspostController::class, 'delete']);
});

Route::middleware(\App\Http\Middleware\checkAdmin::class)->group(function() {
    Route::get('/crosspost/autoconnect/{name}', [CrosspostController::class, 'autoconnect'])->name('crosspostAutoconnect');
    Route::post('/crosspost/settings/{name}', [CrosspostController::class, 'saveSettings'])->name('crosspostSaveSettings');
    Route::get('/crosspost/redirect/{name}', [CrosspostController::class, 'afterRedirect'])->name('crosspostRedirectUri');
});
Route::get('forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm']);
Route::post('forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::get('password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm']);
Route::post('password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset']);
Route::get('/confirm-account/{code}', [\App\Http\Controllers\Auth\RegisterController::class, 'confirm']);
Auth::routes();

Route::get('garland', function() {
    return view('blocks.garland');
});

