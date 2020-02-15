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

Route::get('/', function () {
    return view('index');
});


Route::get('/new-design', function () {
    return view('new-design');
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
Route::any('/video/advertising', function () {
    return (new \App\Http\Controllers\RecordsController())->advertising(['is_radio' => false]);
});
Route::any('/video/search', function () {
    return (new \App\Http\Controllers\RecordsController())->search(['is_radio' => false]);
});
Route::get('/video/{id}/edit', 'RecordsController@edit');
Route::get('/video/{id}', 'RecordsController@show');
Route::get('/video/vip/{id}/{channel?}/{url}', 'RecordsController@showOld');
Route::get('/video/vip/{id}//{url}', 'RecordsController@showOld');

// RADIO
Route::get('/dir', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => true]);
});
Route::get('/radio', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => true]);
});
Route::any('/radio/advertising', function () {
    return (new \App\Http\Controllers\RecordsController())->advertising(['is_radio' => true]);
});
Route::get('/radio-recordings/add', function () {
    return (new \App\Http\Controllers\RecordsController())->add(['is_radio' => true]);
});
Route::get('/radio-recordings/{id}', 'RecordsController@show');
Route::get('/radio-recordings/{id}/edit', 'RecordsController@edit');
Route::any('/radio/search', function () {
    return (new \App\Http\Controllers\RecordsController())->search(['is_radio' => true]);
});
Route::any('/records/search', function () {
    return (new \App\Http\Controllers\RecordsController())->search([]);
});
Route::post('/records/mass-edit', 'RecordsController@massEdit');
Route::post('/records/add', 'RecordsController@save');
Route::post('/records/{id}/edit', 'RecordsController@update');
Route::post('/records/getinfo', 'RecordsController@getInfo');
Route::post('/records/delete', 'RecordsController@delete');

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


Route::get('/channels/add', 'ChannelsController@add');
Route::post('/channels/add', 'ChannelsController@save');
Route::get('/channels/ajax',  function () {
    return (new \App\Http\Controllers\ChannelsController())->getAjaxList(false);
});
Route::get('/radio-stations/ajax',  function () {
    return (new \App\Http\Controllers\ChannelsController())->getAjaxList(true);
});
Route::get('/radio-stations/{id}', 'ChannelsController@show');
Route::get('/channels/{id}', 'ChannelsController@show');
Route::get('/radio-stations/{id}', 'ChannelsController@show');
Route::get('/channels/{id}/edit', 'ChannelsController@edit');
Route::get('/channels/{id}/programs', 'ChannelsController@getPrograms');
Route::get('/channels/{id}/interprogram-packages', 'ChannelsController@getInterprogramPackages');
Route::post('/channels/{id}/edit', 'ChannelsController@update');
Route::post('/channels/merge', 'ChannelsController@merge');
Route::post('/channels/delete', 'ChannelsController@delete');

Route::post('/channels/{id}/graphics/add', 'InterprogramPackagesController@save');
Route::get('/channels/{id}/graphics/add', 'InterprogramPackagesController@add');
Route::get('/channels/{id}/graphics/edit/{package_id}', 'InterprogramPackagesController@edit');
Route::post('/channels/{id}/graphics/edit/{package_id}', 'InterprogramPackagesController@update');
Route::get('/channels/{id}/graphics', 'InterprogramPackagesController@show');
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


Route::get('/news/add', function () {
    return (new \App\Http\Controllers\ArticlesController())->add(['type_id' => \App\Article::TYPE_NEWS]);
});
Route::get('/blog/add', function () {
    return (new \App\Http\Controllers\ArticlesController())->add(['type_id' => \App\Article::TYPE_BLOG]);
});
Route::get('/articles/crosspost', 'ArticlesController@getCrosspostParameters');
Route::post('/articles/crosspost', 'ArticlesController@crosspost');
Route::post('/articles/delete', 'ArticlesController@delete');
Route::post('/articles/approve', 'ArticlesController@approve');

Route::get('/articles/add', function () {
    return (new \App\Http\Controllers\ArticlesController())->add(['type_id' => \App\Article::TYPE_ARTICLES]);
});
Route::post('/articles/add', 'ArticlesController@save');

Route::get('/news/edit/{id}', function ($id) {
    return (new \App\Http\Controllers\ArticlesController())->edit(['original_id' => $id, 'type_id' => \App\Article::TYPE_NEWS]);
});
Route::get('/blog/edit/{id}', function ($id) {
    return (new \App\Http\Controllers\ArticlesController())->edit(['original_id' => $id, 'type_id' => \App\Article::TYPE_BLOG]);
});
Route::get('/articles/edit/{id}', function ($id) {
    return (new \App\Http\Controllers\ArticlesController())->edit(['original_id' => $id, 'type_id' => \App\Article::TYPE_ARTICLES]);
});
Route::post('/articles/edit/{id}', 'ArticlesController@update');
Route::post('/articles/delete', 'ArticlesController@delete');

Route::get('/news/{id}', function ($path) {
    $data = explode("-", $path);
    if (!isset($data[3])) {
        return redirect("/news");
    }
    return (new \App\Http\Controllers\ArticlesController())->show([
        'type_id' => \App\Article::TYPE_NEWS,
        'original_id' => $data[3]
    ]);
});

Route::get('/blog/{id}', function ($path) {
    $data = explode("-", $path);
    if (!isset($data[3])) {
        return redirect("/articles");
    }
    return (new \App\Http\Controllers\ArticlesController())->show([
        'type_id' => \App\Article::TYPE_ARTICLES,
        'original_id' => $data[3]
    ]);
});

Route::get('/articles/{id}', function ($path) {
    $data = explode("-", $path);
    if (!isset($data[3])) {
        return redirect("/articles");
    }
    return (new \App\Http\Controllers\ArticlesController())->show([
        'type_id' => \App\Article::TYPE_ARTICLES,
        'original_id' => $data[3]
    ]);
});


Route::get('/stuff/{category_id}-1-0-{id}', function ($category_id, $id) {
    return (new \App\Http\Controllers\ArticlesController())->show([
        'type_id' => \App\Article::TYPE_BLOG,
        'original_id' => $id
    ]);
});


Route::get('/news', function () {
    return (new \App\Http\Controllers\ArticlesController())->list([
        'type_id' => \App\Article::TYPE_NEWS,
    ]);
});


Route::get('/blog', function () {
    return (new \App\Http\Controllers\ArticlesController())->list([
        'type_id' => \App\Article::TYPE_BLOG,
    ]);
});

Route::get('/articles', function () {
    return (new \App\Http\Controllers\ArticlesController())->list([
        'type_id' => \App\Article::TYPE_ARTICLES,
    ]);
});

Route::get('/news/category/{id}', function ($category_id) {
    return (new \App\Http\Controllers\ArticlesController())->list([
        'type_id' => \App\Article::TYPE_NEWS,
        'category_id' => $category_id,
    ]);
});
Route::get('/blog/category/{id}', function ($category_id) {
    return (new \App\Http\Controllers\ArticlesController())->list([
        'type_id' => \App\Article::TYPE_BLOG,
        'category_id' => $category_id,
    ]);
});
Route::get('/articles/category/{id}', function ($category_id) {
    return (new \App\Http\Controllers\ArticlesController())->list([
        'type_id' => \App\Article::TYPE_ARTICLES,
        'category_id' => $category_id,
    ]);
});

// USERS

Route::post('/users/autocomplete', 'UsersController@autocomplete');

Route::get('/index/8{id?}', function ($path = null) {
    if (!$path) {
        $data = [0];
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

Route::get('/index/15', 'UsersController@list');
Route::get('/index/15-{page}', 'UsersController@list');
Route::post('/index/15', 'UsersController@list');

// FORUM

Route::get('/forum', 'ForumController@index');

Route::get('/forum/{id}/new-topic', 'ForumController@newTopic');
Route::post('/forum/{id}/new-topic', 'ForumController@createTopic');
Route::get('/forum/edit-topic/{id}', 'ForumController@editTopic');
Route::post('/forum/edit-topic/{id}', 'ForumController@saveTopic');
Route::post('/forum/move-topic', 'ForumController@moveTopic');
Route::post('/forum/delete-topic', 'ForumController@deleteTopic');

Route::get('/forum/new-section', 'ForumController@newSection');
Route::get('/forum/{id}/new', 'ForumController@newForum');
Route::get('/forum/edit/{id}', 'ForumController@editForum');
Route::post('/forum/new', 'ForumController@createForum');
Route::post('/forum/edit/{id}', 'ForumController@saveForum');

Route::post('/forum/post-message', 'ForumController@postMessage');
Route::post('/forum/edit-message', 'ForumController@editMessage');
Route::post('/forum/delete-message', 'ForumController@deleteMessage');

Route::get('/forum/0-{message_id}', 'ForumController@redirectToMessageById');
Route::get('/forum/{id}-0-{page_id}', 'ForumController@subforum');
Route::get('/forum/{forum_id}-{topic_id}-0-17-1', 'ForumController@redirectToLastMessage');
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{time}', 'ForumController@redirectToMessage');
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{page_id}-{time}', 'ForumController@redirectToMessage');
Route::get('/forum/{forum_id}-{topic_id}-{page_id}', 'ForumController@showTopic');
Route::get('/forum/{forum_id}-{topic_id}', 'ForumController@showTopic');
Route::get('/forum/{id}', 'ForumController@subforum');
Route::post('/forum/get-edit-form', 'ForumController@getEditForm');

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
//PAGES
Route::get('/index/0-{id}', 'PagesController@show');
Route::get('/pages/add', 'PagesController@add');
Route::post('/pages/add', 'PagesController@save');
Route::get('/pages/{url}', 'PagesController@showByURL');
Route::get('/pages/{id}/edit', 'PagesController@edit');
Route::post('/pages/{id}/edit', 'PagesController@update');
Route::post('/pages/delete', 'PagesController@delete');

//PROFILE
Route::get('/index/11', 'UsersController@edit');
Route::get('/index/11-{id}-0-1', 'UsersController@edit');
Route::get('/profile/edit', 'UsersController@edit');
Route::get('/profile/edit/{id}', 'UsersController@edit');
Route::post('/profile/edit', 'UsersController@save');
Route::get('/index/34-{id}', 'UsersController@comments');
Route::get('/users/comments/{id}', 'UsersController@comments');

// PM
Route::get('/pm', 'PrivateMessagesController@index');
Route::get('/index/14', 'PrivateMessagesController@index');
Route::post('/pm/update', 'PrivateMessagesController@update');
Route::get('/pm/send', 'PrivateMessagesController@send');
Route::post('/pm/send', 'PrivateMessagesController@post');
Route::post('/pm/delete', 'PrivateMessagesController@delete');
Route::post('/pm/cancel', 'PrivateMessagesController@cancel');
Route::get('/pm/{id}', 'PrivateMessagesController@show');

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
    return redirect($path);
});


// ADMIN

Route::middleware(\App\Http\Middleware\checkAdmin::class)->prefix('admin')->group(function () {
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

    Route::get('program-categories', 'AdminController@getProgramCategories');
    Route::post('program-categories', 'AdminController@saveProgramCategories');
});


// CROSSPOST
Route::middleware(\App\Http\Middleware\checkAdmin::class)->group(function() {
    Route::get('/crosspost/test', function() {
        $crossposter = new \App\Crossposting\VKCrossposter();
        $crossposter->deletePost(62);
        return [];
    });
    Route::get('/crosspost/autoconnect/{name}', 'CrosspostController@autoconnect')->name('crosspostAutoconnect');
    Route::post('/crosspost/settings/{name}', 'CrosspostController@saveSettings')->name('crosspostSaveSettings');

    Route::get('/crosspost/redirect/{name}', 'CrosspostController@afterRedirect')->name('crosspostRedirectUri');
});

Auth::routes();

Route::any('/migrate', function () {
    $response = \Illuminate\Support\Facades\Artisan::call('migrate');
    dd($response);
});
