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

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ProfileTelegramController;
use App\Http\Controllers\AwardsController;
use App\Http\Controllers\ChannelsController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\CrosspostController;
use App\Http\Controllers\Forum\ForumController;
use App\Http\Controllers\Forum\QuestionnairesController;
use App\Http\Controllers\HistoryEventsController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\InterprogramPackagesController;
use App\Http\Controllers\MassUploadController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PrivateMessagesController;
use App\Http\Controllers\ProgramsController;
use App\Http\Controllers\RecordsController;
use App\Http\Controllers\ReputationController;
use App\Http\Controllers\SiteSearchController;
use App\Http\Controllers\TeletextController;
use App\Http\Controllers\TopListController;
use App\Http\Controllers\PictureUploadController;
use App\Http\Controllers\SmilesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VideoCutController;
use App\Http\Controllers\WarningsController;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ChannelsController as AdminChannelsController;
use App\Http\Controllers\Admin\GenresController as AdminGenresController;
use App\Http\Controllers\Admin\PermissionsController as AdminPermissionsController;
use App\Http\Controllers\Admin\SmilesController as AdminSmilesController;
use App\Http\Controllers\Admin\UsersController as AdminUsersController;

if (!function_exists('defineCrudRoutes')) {
    function defineCrudRoutes($controller, $routes = [])
    {
        $routes = array_merge([
            'index' => true,
            'add' => true,
            'save' => true,
            'show' => true,
            'edit' => true,
            'update' => true,
            'approve' => true,
            'delete' => true
        ], $routes);

        if ($routes['index']) {
            Route::get('', [$controller, 'index'])->name('index');
        }

        if ($routes['add']) {
            Route::get('add', [$controller, 'add'])->name('add');
        }

        if ($routes['save']) {
            Route::post('add', [$controller, 'save'])->name('save');
        }

        if ($routes['show']) {
            Route::get('{id}', [$controller, 'show'])->name('show');
        }

        if ($routes['edit']) {
            Route::get('{id}/edit', [$controller, 'edit'])->name('edit');
        }

        if ($routes['update']) {
            Route::post('{id}/edit', [$controller, 'update'])->name('update');
        }

        if ($routes['approve']) {
            Route::post('approve', [$controller, 'approve'])->name('approve');
        }

        if ($routes['delete']) {
            Route::post('delete', [$controller, 'delete'])->name('delete');
        }
    }
}

Route::group(['middleware' => \App\Http\Middleware\SetUserLastSeenPage::class], function () {
    Route::get('/', [IndexController::class, 'index'])->name('index');
    Route::get('/promo', [IndexController::class, 'promo']);

    Route::get('/new-design', function () {
        return view('new-design');
    });
    Route::get('/new-design-v1', function () {
        return view('pages.new-design-v1');
    });

    // VIDEOS + RADIO
    foreach (['video', 'radio'] as $prefix) {
        Route::name('records.'.$prefix.'.')->prefix($prefix)->group(function () use ($prefix) {
            $is_radio = $prefix === 'radio';

            Route::get('', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => $is_radio]);
            })->name('index');
            Route::get('add', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->add(['is_radio' => $is_radio]);
            })->name('add');

            Route::any('search', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->search(['is_radio' => $is_radio]);
            })->name('search');

            Route::any('other', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->other(['is_radio' => $is_radio]);
            })->name('other');
            Route::any('other/{category}', function ($category_url) use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->other(['is_radio' => $is_radio], $category_url);
            })->name('other.category');

            Route::any('commercials', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->advertising(['is_radio' => $is_radio]);
            })->name('commercials');

            Route::any('commercials-search', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->advertisingBrands(['is_radio' => $is_radio]);
            })->name('commercials-search');

            Route::get('programs', function () use ($is_radio) {
                return (new \App\Http\Controllers\ProgramsController())->index(['is_radio' => $is_radio]);
            })->name('programs');
            Route::get('programs/ajax', function () use ($is_radio) {
                return (new \App\Http\Controllers\ProgramsController())->loadAll(['is_radio' => $is_radio]);
            })->name('programs.ajax');

            Route::get('calendar', [RecordsController::class, 'calendar'])->name('calendar.index');
            Route::get('calendar/{year}', [RecordsController::class, 'calendarYear'])->name('calendar.year');
            Route::get('calendar/{year}/{month}', [RecordsController::class, 'calendarMonth'])->name('calendar.month');

            Route::get('{id}/edit', [RecordsController::class, 'edit'])->name('edit');
            Route::get('{id}', [RecordsController::class, 'show'])->name('show');
        });
    }

    Route::get('/video/vip/{id}/{channel?}/{url}', [RecordsController::class, 'ucozRedirect']);
    Route::get('/video/vip/{id}//{url}', [RecordsController::class, 'ucozRedirect']);

    Route::any('/video/graphics', [InterprogramPackagesController::class, 'index'])->name('records.video.graphics');
    Route::any('/video/graphics/programs', [InterprogramPackagesController::class, 'program'])->name('records.video.programs-graphics');

    Route::any('/video/graphics_old', function () {
        return (new \App\Http\Controllers\RecordsController())->interprogram(['is_radio' => false]);
    });

    Route::any('/video/youtube-ids/{author_id}', function ($author_id) {
        return (new \App\Http\Controllers\RecordsController())->getYoutubeVideoIds($author_id);
    });
    Route::any('/video/author/{author_id}', function ($author_id) {
        return (new \App\Http\Controllers\RecordsController())->getVideosForAuthor($author_id);
    });

    Route::get('/dir', function () {
        return redirect(route('records.radio'));
    });

    Route::any('/radio/jingles', function () {
        return (new \App\Http\Controllers\RecordsController())->interprogram(['is_radio' => true]);
    })->name('records.radio.jingles');


    Route::get('/embed/{id}', [RecordsController::class, 'embed'])->name('records.embed');

    Route::name('records.')->prefix('records')->group(function () {
        Route::post('approve', [RecordsController::class, 'approve'])->name('approve');
        Route::any('search', function () {
            return (new \App\Http\Controllers\RecordsController())->search([]);
        })->name('search');
        Route::post('upload', [RecordsController::class, 'upload'])->name('upload');
        Route::any('after-upload', [RecordsController::class, 'afterUpload'])->name('after-upload');

        Route::post('download', [RecordsController::class, 'download'])->name('download');
        Route::post('mass-edit', [RecordsController::class, 'massEdit'])->name('mass-edit');
        Route::post('add', [RecordsController::class, 'save'])->name('save');
        Route::post('{id}/edit', [RecordsController::class, 'update'])->name('update');
        Route::any('getinfo', [RecordsController::class, 'getInfo'])->name('get-info');
        Route::post('delete', [RecordsController::class, 'delete'])->name('delete');
        Route::get('categories', [RecordsController::class, 'categories'])->name('categories');
        Route::any('ajax', [RecordsController::class, 'ajax'])->name('ajax');
        Route::post('screenshot', [RecordsController::class, 'screenshot'])->name('screenshot');
        Route::post('set-telegram-id', [RecordsController::class, 'setTelegramID'])->name('set-telegram-id');
        Route::get('playlist-ajax/{id}', [RecordsController::class, 'playlistAjax'])->name('playlist-ajax');
    });

    // MASS UPLOAD

    Route::name('mass-upload.')->prefix('mass-upload')->group(function () {
        Route::get('', [MassUploadController::class, 'index'])->name('index');
        Route::post('', [MassUploadController::class, 'fetchList'])->name('list');
        Route::get('from-device', [MassUploadController::class, 'uploadFromDevice'])->name('from-device');
    });

    //  Route::get('/mass-upload/import-from-telegram', [MassUploadController::class, 'importFromTelegram']);
    //  Route::get('/mass-upload/import-old-from-telegram', [MassUploadController::class, 'importOldFromTelegram']);


    // PROGRAMS
    Route::name('programs.')->prefix('programs')->group(function () {
        defineCrudRoutes(ProgramsController::class, [
            'index' => false,
            'add' => false,
            'update' => false
        ]);

        Route::post('merge', [ProgramsController::class, 'merge'])->name('merge');
        Route::get('autocomplete', [ProgramsController::class, 'autocomplete'])->name('autocomplete');
    });


    foreach (['channels', 'radio-stations'] as $prefix) {
        Route::name($prefix.'.')->prefix($prefix)->group(function () use ($prefix) {
            $is_radio = $prefix === 'radio-stations';

            defineCrudRoutes(ChannelsController::class, [
                'index' => false
            ]);

            Route::post('merge', [ChannelsController::class, 'merge'])->name('merge');

            Route::get('{id}/programs/add', [ProgramsController::class, 'add'])->name('programs.add');
            Route::post('{id}/programs/add', [ProgramsController::class, 'save'])->name('programs.save');

            Route::get('{id}/programs/edit', [ProgramsController::class, 'editList'])->name('programs.edit-list');
            Route::post('{id}/programs/edit', [ProgramsController::class, 'saveList'])->name('programs.save-list');

            Route::get('ajax', function () use ($is_radio) {
                return (new \App\Http\Controllers\ChannelsController())->getAjaxList($is_radio);
            });
            Route::post('autocomplete', [ChannelsController::class, 'autocomplete'])->name('autocomplete');

            Route::get('{id}/programs', [ChannelsController::class, 'getPrograms'])->name('programs.ajax');
        });
    }


    // GRAPHICS

    Route::prefix('channels')->group(function () {
        Route::get('{id}/graphics/ajax', function ($id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->ajax(['channel_id' => $id]);
        })->name('graphics.channels.ajax');

        Route::get('{id}/graphics', function ($id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->showAll(['channel_id' => $id]);
        })->name('graphics.channels.index');

        Route::get('{id}/graphics/add', function ($id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->add(['channel_id' => $id]);
        })->name('graphics.channels.add');

        Route::post('{id}/graphics/add', function ($id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->save(['channel_id' => $id]);
        })->name('graphics.channels.save');

        Route::get('{id}/graphics/edit/{package_id}', function ($id, $package_id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->edit(['channel_id' => $id], $package_id);
        })->name('graphics.channels.edit');

        Route::post('{id}/graphics/edit/{package_id}', function ($id, $package_id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->update(['channel_id' => $id], $package_id);
        })->name('graphics.channels.update');

        Route::get('/channels/{id}/graphics/{package_id}', [InterprogramPackagesController::class, 'show'])->name('graphics.channels.show');
    });

    Route::prefix('programs')->group(function () {
        Route::get('{id}/graphics', [InterprogramPackagesController::class, 'showByProgram'])->name('graphics.programs.show');

        Route::get('{id}/graphics/ajax', function ($id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->ajax(['program_id' => $id]);
        })->name('graphics.programs.ajax');

        Route::get('{id}/graphics/add', function ($id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->add(['program_id' => $id]);
        })->name('graphics.programs.add');

        Route::post('{id}/graphics/add', function ($id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->save(['program_id' => $id]);
        })->name('graphics.programs.save');

        Route::get('{id}/graphics/edit/{package_id}', function ($id, $package_id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->edit(['program_id' => $id], $package_id);
        })->name('graphics.programs.edit');

        Route::post('{id}/graphics/edit/{package_id}', function ($id, $package_id) {
            return (new \App\Http\Controllers\InterprogramPackagesController())->update(['program_id' => $id], $package_id);
        })->name('graphics.programs.update');
    });

    Route::post('/graphics/delete', [InterprogramPackagesController::class, 'delete'])->name('graphics.delete');


    // TELETEXT

    Route::name('teletext.')->prefix('teletext')->group(function () {
        defineCrudRoutes(TeletextController::class, ['approve' => true]);

        Route::get('channels/{id}', [TeletextController::class, 'channel'])->name('channel');
    });

    // COMMENTS

    Route::name('comments.')->prefix('comments')->group(function () {
        Route::post('ajax', [CommentsController::class, 'ajax'])->name('ajax');
        Route::post('add', [CommentsController::class, 'add'])->name('add');
        Route::post('edit', [CommentsController::class, 'edit'])->name('edit');
        Route::post('delete', [CommentsController::class, 'delete'])->name('delete');
       // Route::any('original/{id}', [CommentsController::class, 'getOriginal'])->name('get-original');
        Route::post('rating', [CommentsController::class, 'rating'])->name('rating');
    });
    Route::get('/new-comments', [CommentsController::class, 'latest'])->name('comments.latest');

    Route::get('/users/{id}/comments', [CommentsController::class, 'user'])->name('comments.user');
    Route::get('/index/34-{id}', function($id) {
        return redirect(route('comments.user', $id));
    });


    // UPLOAD PICTURES

    Route::name('pictures.')->prefix('upload/pictures')->group(function () {
        Route::get('getbychannel/{id}', [PictureUploadController::class, 'getPicturesByChannel'])->name('get-by-channel');
        Route::post('by-url', [PictureUploadController::class, 'uploadPicturesByURL'])->name('upload-by-url');
        Route::post('', [PictureUploadController::class, 'upload'])->name('upload');
    });


    // ARTICLES

    Route::name('articles.')->prefix('articles')->group(function () {
        defineCrudRoutes(ArticlesController::class, ['approve' => true]);

        Route::get('crosspost', [ArticlesController::class, 'getCrosspostParameters'])->name('get-crosspost-parameters');
        Route::post('crosspost', [ArticlesController::class, 'crosspost'])->name('crosspost');


        Route::post('actions', [ArticlesController::class, 'getActions'])->name('get-actions');
        Route::post('change-type', [ArticlesController::class, 'changeType'])->name('change-type');

    });

    Route::get('/blog', function () {
        return redirect('/articles');
    });
    Route::get('/news', function () {
        return redirect('/articles');
    });

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

    Route::name('forum.')->prefix('forum')->group(function () {
        Route::get('', [ForumController::class, 'index'])->name('index');
        Route::post('get-edit-form', [ForumController::class, 'getEditForm'])->name('get-edit-form');

        Route::get('{id}/new-topic', [ForumController::class, 'newTopic'])->name('topics.new');
        Route::post('{id}/new-topic', [ForumController::class, 'createTopic'])->name('topics.create');
        Route::get('edit-topic/{id}', [ForumController::class, 'editTopic'])->name('topics.edit');
        Route::post('edit-topic/{id}', [ForumController::class, 'saveTopic'])->name('topics.save');
        Route::post('move-topic', [ForumController::class, 'moveTopic'])->name('topics.move');
        Route::post('delete-topic', [ForumController::class, 'deleteTopic'])->name('topics.delete');

        Route::get('profile/{id}', [ForumController::class, 'getProfile'])->name('profile');

        //Route::get('new-section', [ForumController::class, 'newSection'])->name('sections.new');

        Route::get('{forum_id}-{topic_id}-0-17-1', [ForumController::class, 'redirectToLastMessage'])->name('topics.show-last-message');
        Route::get('{forum_id}-{topic_id}-{message_id}-{time}', [ForumController::class, 'redirectToMessage']);
        Route::get('{forum_id}-{topic_id}-{message_id}-{page_id}-{time}', [ForumController::class, 'redirectToMessage']);

        Route::get('{forum_id}-{topic_id}-{page_id}', [ForumController::class, 'showTopic'])->name('topics.show-page');
        Route::get('{forum_id}-{topic_id}', [ForumController::class, 'showTopic'])->name('topics.show');

        Route::get('{id}-0-{page_id}', [ForumController::class, 'subforum'])->name('subforums.show');
        Route::get('{id}', [ForumController::class, 'subforum'])->name('subforums.show');
        Route::get('{id}/new', [ForumController::class, 'newForum'])->name('subforums.new');
        Route::get('edit/{id}', [ForumController::class, 'editForum'])->name('subforums.edit');
        Route::post('new', [ForumController::class, 'createForum'])->name('subforums.create');
        Route::post('edit/{id}', [ForumController::class, 'saveForum'])->name('subforums.save');



        Route::post('post-message', [ForumController::class, 'postMessage'])->name('messages.create');
        Route::post('edit-message', [ForumController::class, 'editMessage'])->name('messages.update');
        Route::post('delete-message', [ForumController::class, 'deleteMessage'])->name('messages.delete');

        Route::get('last-topics', [ForumController::class, 'lastTopics'])->name('last-topics');
        Route::get('user-messages/{user_id}', [ForumController::class, 'userMessages'])->name('user-messages');
        Route::get('0-{message_id}', [ForumController::class, 'redirectToMessageById'])->name('redirect-to-message-by-id');


    });

    Route::get('/forum/0-0-1-34', function () {
        return redirect("/forum/last-topics");
    });

    Route::name('forum.questionnaire.')->prefix('questionnaire')->group(function () {
        Route::post('vote', [QuestionnairesController::class, 'vote'])->name('vote');
        Route::post('form', [QuestionnairesController::class, 'form'])->name('form');
    });


    // REPUTATION

    Route::name('reputation.')->prefix('reputation')->group(function () {
        Route::get('ajax', [ReputationController::class, 'ajax'])->name('ajax');
        Route::post('change', [ReputationController::class, 'change'])->name('change');
        Route::post('edit', [ReputationController::class, 'edit'])->name('edit');
        Route::post('delete', [ReputationController::class, 'delete'])->name('delete');
        Route::post('reply', [ReputationController::class, 'reply'])->name('reply');
    });


    // AWARDS

    Route::name('awards.')->prefix('awards')->group(function () {
        Route::get('ajax', [AwardsController::class, 'ajax'])->name('ajax');
        Route::post('list', [AwardsController::class, 'list'])->name('list');
        Route::post('give-out', [AwardsController::class, 'create'])->name('create');
        Route::post('edit', [AwardsController::class, 'edit'])->name('edit');
        Route::post('delete', [AwardsController::class, 'delete'])->name('delete');
    });


    // WARNINGS

    Route::name('warnings.')->prefix('warnings')->group(function () {
        Route::get('ajax', [WarningsController::class, 'ajax'])->name('ajax');
        Route::get('warnings/form', [WarningsController::class, 'form'])->name('form');
        Route::post('warnings/add', [WarningsController::class, 'add'])->name('add');
    });


    // CONTACT FORM

    Route::name('contact.')->group(function () {
        Route::get('/contact', [ContactFormController::class, 'index'])->name('index');
        Route::post('contact', [ContactFormController::class, 'send'])->name('send');
        Route::get('/tape-digitization', [ContactFormController::class, 'digitization'])->name('digitization.index');
        Route::post('/tape-digitization', [ContactFormController::class, 'digitizationSend'])->name('digitization.send');
    });
    Route::get('/index/0-3', function() {
        return redirect(route('contact.index'));
    });


    //PAGES

    Route::name('pages.')->prefix('pages')->group(function () {
        defineCrudRoutes(PagesController::class, ['approve' => false]);
        Route::get('{url}', [PagesController::class, 'showByURL'])->name('show-by-url');
    });
    Route::get('/index/0-{id}', [\App\Http\Controllers\PagesController::class, 'show'])->name('pages.show');
    Route::get('/team', [PagesController::class, 'team'])->name('pages.team');


    //USERS

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

    Route::get('/index/8-0-{username}', function ($username) {

        return (new \App\Http\Controllers\UsersController())->show([
            'username' => $username
        ]);
    })->where('username', '.*')->name('users.show-by-username');

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
            $username = implode('-', array_slice($data, 2, count($data)));
            return (new \App\Http\Controllers\UsersController())->show([
                'username' => $username
            ]);
        }
    });

    Route::name('users.')->group(function() {
        Route::get('/users/autocomplete', [UsersController::class, 'autocomplete'])->name('autocomplete');

        Route::get('/users/{id}', function ($id) {
            return (new \App\Http\Controllers\UsersController())->show([
                'id' => $id
            ]);
        })->name('show');

        Route::any('/index/15', [UsersController::class, 'index'])->name('index');
        Route::any('/index/15-{page}', [UsersController::class, 'index'])->name('index');

        Route::get('users/{id}/videos', [UsersController::class, 'videos'])->name('videos');
        Route::get('users/{id}/radio', [UsersController::class, 'radioRecordings'])->name('radio-recordings');

    });

    Route::name('profile.')->prefix('profile')->group(function() {
        Route::get('edit', [ProfileController::class, 'edit'])->name('edit');
        Route::get('edit/{id}', [ProfileController::class, 'edit'])->name('edit.user');
        Route::post('edit', [ProfileController::class, 'save'])->name('save');

        Route::get('password', [ProfileController::class, 'editPassword'])->name('edit-password');
        Route::post('password', [ProfileController::class, 'savePassword'])->name('save-password');

        Route::get('notifications', [ProfileController::class, 'notifications'])->name('notifications');

        Route::name('telegram.')->prefix('telegram')->group(function() {
            Route::get('register', [ProfileTelegramController::class, 'registerForm'])->name('register-form');
            Route::post('register', [ProfileTelegramController::class, 'register'])->name('register');
            Route::post('connect', [ProfileTelegramController::class, 'connect'])->name('connect');
            Route::post('disconnect', [ProfileTelegramController::class, 'disconnect'])->name('disconnect');
        });
    });

    Route::name('profile.')->group(function() {
        Route::get('forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgot-password');
        Route::post('forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forgot-password-send');
        Route::get('password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('reset-password');
        Route::post('password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('reset-password-send');
        Route::get('/confirm-account/{code}', [\App\Http\Controllers\Auth\RegisterController::class, 'confirm'])->name('confirm');
        Route::get('/users/change-email/{code}', [ProfileController::class, 'changeEmail'])->name('change-email');
    });

    Auth::routes();


    Route::get('/index/11', function() {
        return redirect(route('profile.edit'));
    });
    Route::get('/index/11-{id}-0-1', function($id) {
        return redirect(route('profile.edit', $id));
    });


    // PM

    Route::name('pm.')->prefix('pm')->group(function() {
        defineCrudRoutes(PrivateMessagesController::class, [
            'approve' => false
        ]);
        Route::post('cancel', [PrivateMessagesController::class, 'cancel'])->name('cancel');
    });

    Route::get('/index/14', function() {
        return redirect(route('pm.index'));
    });


    // CUTTING

    Route::name('cut.')->prefix('cut')->group(function() {
        Route::any('download-external', [VideoCutController::class, 'downloadExternal'])->name('download-external');
        Route::any('downloaded/{id}', [VideoCutController::class, 'onDownloaded'])->name('on-downloaded');
        Route::get('{id}', [VideoCutController::class, 'show'])->name('show');
        Route::post('{id}', [VideoCutController::class, 'save'])->name('save');
        Route::any('{id}/make-video/{index}', [VideoCutController::class, 'makeVideo'])->name('make-video');
        Route::get('start/{id}', [VideoCutController::class, 'showForm'])->name('show-form');
        Route::post('start/{id}', [VideoCutController::class, 'start'])->name('start');
    });


    // EVENTS

    Route::name('events.')->prefix('events')->group(function() {
        defineCrudRoutes(HistoryEventsController::class);
    });


    // TOP LISTS

    Route::name('top-list.')->prefix('top-list')->group(function() {
        Route::get('videos', [TopListController::class, 'videos'])->name('videos');
        Route::get('radio-recordings', [TopListController::class, 'radioRecordings'])->name('radio-recordings');
        Route::get('news', [TopListController::class, 'news'])->name('news');
        Route::get('articles', [TopListController::class, 'articles'])->name('articles');
        Route::get('forum', [TopListController::class, 'forum'])->name('forum');
        Route::get('comments', [TopListController::class, 'comments'])->name('comments');
        Route::get('awards', [TopListController::class, 'awards'])->name('awards');
        Route::get('reputation', [TopListController::class, 'reputation'])->name('reputation');
    });


    //REDACTOR
    Route::get('/redactor-panel', [AdminController::class, 'editorPanel'])->name('redactor-panel');


    Route::any('/smiles', [SmilesController::class, 'ajax'])->name('smiles.ajax');

    Route::get('/go', function () {
        $path = explode("/go?", $_SERVER['REQUEST_URI'])[1];
        return view('pages.redirect', ['path' => $path]);
        //return redirect($path);
    });


    Route::post('/site-search', [SiteSearchController::class, 'search'])->name('site-search');


    // ADMIN

//    Route::any('admin-login', function () {
//        return view("pages.maintenance_login");
//    });

    Route::middleware(\App\Http\Middleware\checkAdmin::class)->name('admin.')->prefix('admin')->group(function () {
        Route::get('', function () {
            return redirect(route('admin.pages'));
        });

        Route::resource('user-groups', 'UserGroupsController');

        Route::name('smiles.')->prefix('smiles')->group(function() {
            Route::get('', [AdminSmilesController::class, 'index'])->name('index');
            Route::post('', [AdminSmilesController::class, 'save'])->name('save');
        });

        Route::name('permissions.')->prefix('permissions')->group(function() {
            Route::get('', [AdminPermissionsController::class, 'index'])->name('index');
            Route::post('', [AdminPermissionsController::class, 'save'])->name('save');
        });

        Route::name('channels.')->prefix('channels')->group(function() {
            Route::get('', [AdminChannelsController::class, 'index'])->name('index');
            Route::get('order', [AdminChannelsController::class, 'getOrder'])->name('order.index');
            Route::post('order', [AdminChannelsController::class, 'saveOrder'])->name('order.save');
        });

        Route::name('users.')->prefix('users')->group(function() {
            Route::get('', [AdminUsersController::class, 'index'])->name('index');
            Route::post('change-group', [AdminUsersController::class, 'changeGroup'])->name('change-group');
            Route::post('change-password', [AdminUsersController::class, 'changePassword'])->name('change-password');
            Route::post('delete', [AdminUsersController::class, 'delete'])->name('delete');
            Route::get('reputation', [AdminUsersController::class, 'getReputationHistory']);
        });

        Route::name('genres.')->prefix('genres')->group(function() {
            Route::get('', [AdminGenresController::class, 'index'])->name('index');
            Route::post('', [AdminGenresController::class, 'save'])->name('save');
        });

        Route::get('pages', [AdminController::class, 'getPages']);
        Route::get('crossposting', [CrosspostController::class, 'getServices']);

//        Route::get('run-command', function () {
//            if (request()->has('command')) {
//                Artisan::call(request()->input('command'));
//            }
//        });
    });

// CROSSPOST
    Route::middleware(\App\Http\Middleware\checkCanCrosspost::class)->group(function () {
        Route::get('/crossposts', [CrosspostController::class, 'index']);
        Route::get('/crossposts/add', [CrosspostController::class, 'add']);
        Route::post('/crossposts/add', [CrosspostController::class, 'save']);
        Route::get('/crossposts/{id}/edit', [CrosspostController::class, 'edit']);
        Route::post('/crossposts/{id}/edit', [CrosspostController::class, 'update']);
        Route::any('/crossposts/{id}/make-post/{service}', [CrosspostController::class, 'makePost']);
        Route::any('/crossposts/{id}/delete-post/{service}', [CrosspostController::class, 'deletePost']);
        Route::post('/crossposts/delete', [CrosspostController::class, 'delete']);
    });

    Route::middleware(\App\Http\Middleware\checkAdmin::class)->group(function () {
        Route::get('/crosspost/autoconnect/{name}', [CrosspostController::class, 'autoconnect'])->name('crosspostAutoconnect');
        Route::post('/crosspost/settings/{name}', [CrosspostController::class, 'saveSettings'])->name('crosspostSaveSettings');
        Route::get('/crosspost/redirect/{name}', [CrosspostController::class, 'afterRedirect'])->name('crosspostRedirectUri');
    });


    Route::get('garland', function () {
        return view('blocks.garland');
    });

});
