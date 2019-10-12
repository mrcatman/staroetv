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
Route::get('/videos', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => false]);
});
Route::get('/videos/add', function () {
    return (new \App\Http\Controllers\RecordsController())->add(['is_radio' => false]);
});
Route::get('/videos/{id}', 'RecordsController@show');
Route::get('/video/vip/{id}/{channel?}/{url}', 'RecordsController@showOld');
Route::get('/video/vip/{id}//{url}', 'RecordsController@showOld');
Route::post('/videos/add', 'RecordsController@save');
Route::get('/videos/{id}/edit', 'RecordsController@edit');
Route::post('/videos/{id}/edit', 'RecordsController@update');
Route::post('/videos/getinfo', 'RecordsController@getInfo');


// RADIO
Route::get('/dir', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => true]);
});
Route::get('/radio', function () {
    return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => true]);
});
Route::get('/radio-recordings/add', function () {
    return (new \App\Http\Controllers\RecordsController())->add(['is_radio' => true]);
});
Route::get('/radio-recordings/{id}', 'RecordsController@show');
Route::post('/radio-recordings/add', 'RecordsController@save');
Route::get('/radio-recordings/{id}/edit', 'RecordsController@edit');
Route::post('/radio-recordings/{id}/edit', 'RecordsController@update');
Route::post('/radio-recordings/getinfo', 'RecordsController@getInfo');



Route::get('/programs/{id}', 'ProgramsController@show');

Route::get('/channels/add', 'ChannelsController@add');
Route::post('/channels/add', 'ChannelsController@save');
Route::get('/channels/{id}', 'ChannelsController@show');
Route::get('/channels/{id}/edit', 'ChannelsController@edit');
Route::get('/channels/{id}/programs', 'ChannelsController@getPrograms');
Route::get('/channels/{id}/interprogram-packages', 'ChannelsController@getInterprogramPackages');
Route::post('/channels/{id}/edit', 'ChannelsController@update');
Route::post('/channels/merge', 'ChannelsController@merge');
Route::post('/channels/delete', 'ChannelsController@delete');

Route::post('/upload/pictures/by-url', 'UploadController@uploadPicturesByURL');
Route::get('/upload/pictures/getbychannel/{id}', 'UploadController@getPicturesByChannel');
Route::post('/upload/pictures', 'UploadController@uploadPictures');

Route::post('/comments/ajax', 'CommentsController@ajax');
Route::post('/comments/add', 'CommentsController@add');
Route::post('/comments/edit', 'CommentsController@edit');
Route::post('/comments/delete', 'CommentsController@delete');
Route::any('/comments/original/{id}', 'CommentsController@getOriginal');



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
Route::post('/index/15', 'UsersController@list');


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

Route::get('/forum/{id}-0-{page_id}', 'ForumController@subforum');
Route::get('/forum/{forum_id}-{topic_id}-0-17-1', 'ForumController@redirectToLastMessage');
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{time}', 'ForumController@redirectToMessage');
Route::get('/forum/{forum_id}-{topic_id}-{message_id}-{page_id}-{time}', 'ForumController@redirectToMessage');
Route::get('/forum/{forum_id}-{topic_id}-{page_id}', 'ForumController@showTopic');
Route::get('/forum/{forum_id}-{topic_id}', 'ForumController@showTopic');
Route::get('/forum/{id}', 'ForumController@subforum');
Route::post('/forum/get-edit-form', 'ForumController@getEditForm');

Route::post('reputation/ajax', 'ReputationController@ajax');
Route::post('reputation/change', 'ReputationController@change');
Route::post('awards/ajax', 'AwardsController@ajax');
Route::post('warnings/ajax', 'WarningsController@ajax');


Route::get('/index/0-{id}', 'PagesController@show');

Route::get('/index/11', 'UsersController@edit');
Route::get('/index/11-{id}-0-1', 'UsersController@edit');
Route::get('/profile/edit', 'UsersController@edit');
Route::get('/profile/edit/{id}', 'UsersController@edit');
Route::post('/profile/edit', 'UsersController@save');
Route::get('/index/34-{id}', 'UsersController@comments');
Route::get('/users/comments/{id}', 'UsersController@comments');

Route::get('/go', function () {
    $path = explode("/go?",$_SERVER['REQUEST_URI'])[1];
    return redirect($path);
});

Route::middleware(\App\Http\Middleware\checkAdmin::class)->prefix('admin')->group(function () {
    Route::get('smiles', 'AdminController@getSmiles');
    Route::post('smiles', 'AdminController@saveSmiles');
    Route::resource('user-groups', 'UserGroupsController');
    Route::get('permissions', 'AdminController@getPermissions');
    Route::post('permissions', 'AdminController@savePermissions');
    Route::get('channels', 'AdminController@getChannels');
    Route::get('channels/order', 'AdminController@getChannelsOrder');
    Route::post('channels/order', 'AdminController@setChannelsOrder');
});

Auth::routes();

