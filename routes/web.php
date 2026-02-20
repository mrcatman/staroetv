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
use App\Http\Controllers\DesignPackagesController;
use App\Http\Controllers\MassUploadController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PrivateMessagesController;
use App\Http\Controllers\ProgramsController;
use App\Http\Controllers\RecordsController;
use App\Http\Controllers\RecordsAutocompleteController;
use App\Http\Controllers\RecordsEditController;
use App\Http\Controllers\RecordsUploadController;
use App\Http\Controllers\ReputationController;
use App\Http\Controllers\SiteSearchController;
use App\Http\Controllers\TeletextController;
use App\Http\Controllers\TopListController;
use App\Http\Controllers\PictureUploadController;
use App\Http\Controllers\SmilesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VideoCutController;
use App\Http\Controllers\WarningsController;

use App\Http\Controllers\Admin\EditorController;
use App\Http\Controllers\Admin\ActionsLogsController as AdminActionsLogsController;
use App\Http\Controllers\Admin\ChannelsController as AdminChannelsController;
use App\Http\Controllers\Admin\GenresController as AdminGenresController;
use App\Http\Controllers\Admin\PagesController as AdminPagesController;
use App\Http\Controllers\Admin\PermissionsController as AdminPermissionsController;
use App\Http\Controllers\Admin\ProgramsController as AdminProgramsController;
use App\Http\Controllers\Admin\RecordComplaintsController as AdminRecordComplaintsController;
use App\Http\Controllers\Admin\SmilesController as AdminSmilesController;
use App\Http\Controllers\Admin\UsersController as AdminUsersController;
use App\Http\Controllers\Admin\UserGroupsController as AdminUserGroupsController;

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

    Route::any('/video/design', [DesignPackagesController::class, 'index'])->name('design.channels.index');
    Route::any('/radio/design', function () {
        return (new \App\Http\Controllers\DesignPackagesController())->catalog(['is_radio' => true]);
    })->name('design.radio-stations.index');

    foreach (['video', 'radio'] as $prefix) {
        Route::name('records.'.$prefix.'.')->prefix($prefix)->group(function () use ($prefix) {
            $is_radio = $prefix === 'radio';

            Route::get('', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->index(['is_radio' => $is_radio]);
            })->name('index');
            Route::get('add', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->add(['is_radio' => $is_radio]);
            })->name('add');


            Route::any('other', function () use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->other(['is_radio' => $is_radio]);
            })->name('other');
            Route::any('other/{category}', function ($category_url) use ($is_radio) {
                return (new \App\Http\Controllers\RecordsController())->other(['is_radio' => $is_radio], $category_url);
            })->name('other.category');

            Route::any('commercials', function () use ($is_radio) {
                $data = request()->all();
                $data['is_radio'] = $is_radio;
                return redirect(route('records.commercials', $data));
            })->name('commercials');

            Route::any('commercials-search', function () use ($is_radio) {
                $data = request()->all();
                $data['is_radio'] = $is_radio;
                return redirect(route('records.commercials', $data));
            })->name('commercials.search');

            Route::get('programs', function () use ($is_radio) {
                return (new \App\Http\Controllers\ProgramsController())->index(['is_radio' => $is_radio]);
            })->name('programs');
            Route::get('programs/show-all', function () use ($is_radio) {
                return (new \App\Http\Controllers\ProgramsController())->showAll(['is_radio' => $is_radio]);
            })->name('programs.show-all');

            Route::get('calendar', function() use ($is_radio) {
                return (new RecordsController())->calendar($is_radio);
            })->name('calendar.index');
            Route::get('calendar/{year}', function($year) use ($is_radio) {
                return (new RecordsController())->calendarYear($year, $is_radio);
            })->name('calendar.year');
            Route::get('calendar/{year}/{month}', function($year, $month) use ($is_radio) {
                return (new RecordsController())->calendarMonth($year, $month, $is_radio);
            })->name('calendar.month');

            Route::get('{id}/edit', [RecordsController::class, 'edit'])->name('edit');
            Route::get('{id}', [RecordsController::class, 'show'])->name('show');
        });
    }

    Route::get('/video/vip/{id}/{channel?}/{url}', [RecordsController::class, 'ucozRedirect']);
    Route::get('/video/vip/{id}//{url}', [RecordsController::class, 'ucozRedirect']);

    Route::any('/video/youtube-ids/{author_id}', function ($author_id) {
        return (new \App\Http\Controllers\RecordsController())->getYoutubeVideoIds($author_id);
    });
    Route::any('/video/author/{author_id}', function ($author_id) {
        return (new \App\Http\Controllers\RecordsController())->getVideosForAuthor($author_id);
    });

    Route::get('/dir', function () {
        return redirect(route('records.radio'));
    });


    Route::get('/embed/{id}', [RecordsController::class, 'embed'])->name('records.embed');

    Route::name('records.')->prefix('records')->group(function () {
        Route::post('approve', [RecordsController::class, 'approve'])->name('approve');
        Route::any('search', [RecordsController::class, 'search'])->name('search');
        Route::get('commercials', [RecordsController::class, 'search'])->name('commercials');

        Route::get('upload/config', [RecordsUploadController::class, 'config'])->name('upload.config');
        Route::post('upload/process', [RecordsUploadController::class, 'process'])->name('upload.process');

        Route::post('download', [RecordsUploadController::class, 'download'])->name('download');
        Route::post('mass-edit', [RecordsController::class, 'massEdit'])->name('mass-edit');
        Route::post('add', [RecordsController::class, 'save'])->name('save');
        Route::post('{id}/edit', [RecordsController::class, 'update'])->name('update');
        Route::any('get-info', [RecordsController::class, 'getInfo'])->name('get-info');
        Route::post('delete', [RecordsController::class, 'delete'])->name('delete');
        Route::get('categories', [RecordsController::class, 'categories'])->name('categories');
        Route::any('ajax', [RecordsController::class, 'ajax'])->name('ajax');
        Route::post('thumbnail', [RecordsController::class, 'thumbnail'])->name('thumbnail');
        Route::post('set-telegram-id', [RecordsController::class, 'setTelegramID'])->name('set-telegram-id');
        Route::get('playlist-ajax/{id}', [RecordsController::class, 'playlistAjax'])->name('playlist-ajax');

        Route::get('autocomplete/countries', [RecordsAutocompleteController::class, 'countries'])->name('autocomplete.countries');
        Route::get('autocomplete/regions', [RecordsAutocompleteController::class, 'regions'])->name('autocomplete.regions');
        Route::get('autocomplete/brands', [RecordsAutocompleteController::class, 'commercialsBrands'])->name('autocomplete.commercials-brands');
        Route::get('autocomplete/categories', [RecordsAutocompleteController::class, 'commercialsCategories'])->name('autocomplete.commercials-categories');

        Route::get('similar', [RecordsController::class, 'similar'])->name('similar');
        Route::post('complaint', [RecordsController::class, 'complaint'])->name('complaint');

        Route::name('edit.')->prefix('edit')->group(function () {
            Route::get('menu', [RecordsEditController::class, 'menu'])->name('menu');
            Route::get('basic-info', [RecordsEditController::class, 'basicInfoForm'])->name('basic-info.form');
            Route::post('basic-info', [RecordsEditController::class, 'saveBasicInfo'])->name('basic-info.save');
            Route::get('transfer', [RecordsEditController::class, 'transferForm'])->name('transfer.form');
            Route::post('transfer', [RecordsEditController::class, 'saveTransfer'])->name('transfer.save');
            Route::get('type', [RecordsEditController::class, 'typeForm'])->name('type.form');
            Route::post('type', [RecordsEditController::class, 'saveType'])->name('type.save');
            Route::get('commercials-info', [RecordsEditController::class, 'commercialsInfoForm'])->name('commercials-info.form');
            Route::post('commercials-info', [RecordsEditController::class, 'saveCommercialsInfo'])->name('commercials-info.save');

            Route::post('update-thumbnails', [RecordsEditController::class, 'updateThumbnails'])->name('update-thumbnails');
            Route::post('upload-to-server', [RecordsEditController::class, 'uploadToServer'])->name('upload-to-server');
            Route::post('approve', [RecordsEditController::class, 'approve'])->name('approve');
            Route::post('unapprove', [RecordsEditController::class, 'unapprove'])->name('unapprove');


        });
    });

    // MASS UPLOAD

    Route::name('mass-upload.')->prefix('mass-upload')->group(function () {
        Route::get('video', function() {
            return (new MassUploadController())->index(false);
        })->name('video');
        Route::get('radio', function() {
            return (new MassUploadController())->index(true);
        })->name('radio');
        Route::post('', [MassUploadController::class, 'fetchList'])->name('list');
    });

    //  Route::get('/mass-upload/import-from-telegram', [MassUploadController::class, 'importFromTelegram']);
    //  Route::get('/mass-upload/import-old-from-telegram', [MassUploadController::class, 'importOldFromTelegram']);


    // PROGRAMS
    Route::name('programs.')->prefix('programs')->group(function () {
        Route::post('merge', [ProgramsController::class, 'merge'])->name('merge');
        Route::get('autocomplete', [ProgramsController::class, 'autocomplete'])->name('autocomplete');
        defineCrudRoutes(ProgramsController::class, [
            'index' => false,
        ]);

    });


    foreach (['channels', 'radio-stations'] as $prefix) {
        Route::name($prefix.'.')->prefix($prefix)->group(function () use ($prefix) {
            Route::get('ajax', [ChannelsController::class, 'ajaxList'])->name('ajax');
            Route::get('autocomplete', [ChannelsController::class, 'autocomplete'])->name('autocomplete');

            Route::post('merge', [ChannelsController::class, 'merge'])->name('merge');

            defineCrudRoutes(ChannelsController::class, [
                'index' => false
            ]);

            Route::get('{id}/programs/edit', [ProgramsController::class, 'editList'])->name('programs.edit-list');
            Route::post('{id}/programs/edit', [ProgramsController::class, 'saveList'])->name('programs.save-list');

            Route::get('{id}/unknown-programs', [ChannelsController::class, 'unknownPrograms'])->name('programs.unknown');
            Route::get('{id}/programs', [ChannelsController::class, 'programs'])->name('programs.ajax');
        });
    }


    // GRAPHICS

    foreach (['channels', 'radio-stations'] as $name) {
        Route::name('design.'.$name.'.')->prefix($name)->group(function () use ($name) {
            Route::get('{id}/design/ajax', function ($id) {
                return (new \App\Http\Controllers\DesignPackagesController())->ajax(['channel_id' => $id]);
            })->name('ajax');

            Route::get('{id}/design', function ($id) {
                return (new \App\Http\Controllers\DesignPackagesController())->showAll(['channel_id' => $id]);
            })->name('all');

            Route::get('{id}/design/add', function ($id) {
                return (new \App\Http\Controllers\DesignPackagesController())->add(['channel_id' => $id]);
            })->name('add');

            Route::post('{id}/design/add', function ($id) {
                return (new \App\Http\Controllers\DesignPackagesController())->save(['channel_id' => $id]);
            })->name('save');

            Route::get('{id}/design/edit/{package_id}', function ($id, $package_id) {
                return (new \App\Http\Controllers\DesignPackagesController())->edit(['channel_id' => $id], $package_id);
            })->name('edit');

            Route::post('{id}/design/edit/{package_id}', function ($id, $package_id) {
                return (new \App\Http\Controllers\DesignPackagesController())->update(['channel_id' => $id], $package_id);
            })->name('update');

            Route::get('{id}/design/{package_id}', [DesignPackagesController::class, 'show'])->name('show');
        });
    }

    Route::prefix('programs')->name('design.programs.')->group(function () {
        Route::get('{id}/design', [DesignPackagesController::class, 'showByProgram'])->name('show');

        Route::get('{id}/design/ajax', function ($id) {
            return (new \App\Http\Controllers\DesignPackagesController())->ajax(['program_id' => $id]);
        })->name('ajax');

        Route::get('{id}/design/add', function ($id) {
            return (new \App\Http\Controllers\DesignPackagesController())->add(['program_id' => $id]);
        })->name('add');

        Route::post('{id}/design/add', function ($id) {
            return (new \App\Http\Controllers\DesignPackagesController())->save(['program_id' => $id]);
        })->name('save');

        Route::get('{id}/design/edit/{package_id}', function ($id, $package_id) {
            return (new \App\Http\Controllers\DesignPackagesController())->edit(['program_id' => $id], $package_id);
        })->name('edit');

        Route::post('{id}/design/edit/{package_id}', function ($id, $package_id) {
            return (new \App\Http\Controllers\DesignPackagesController())->update(['program_id' => $id], $package_id);
        })->name('update');
    });
    Route::post('design/delete', [DesignPackagesController::class, 'delete'])->name('design.delete');

    Route::get('/video/design/programs', function () {
        return (new \App\Http\Controllers\DesignPackagesController())->programs(false);
    })->name('design.programs.channels');
    Route::get('/radio-recordings/design/programs', function () {
        return (new \App\Http\Controllers\DesignPackagesController())->programs(true);
    })->name('design.programs.radio-stations');

    // TELETEXT

    Route::name('teletext.')->prefix('teletext')->group(function () {
        defineCrudRoutes(TeletextController::class, ['approve' => true]);

        Route::get('channels/{id}', [TeletextController::class, 'channel'])->name('channel');
    });

    // COMMENTS

    Route::name('comments.')->prefix('comments')->group(function () {
        Route::get('ajax', [CommentsController::class, 'ajax'])->name('ajax');
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
        return redirect(route('articles.index'));
    });
    Route::get('/news', function () {
        return redirect(route('articles.index'));
    });

    Route::get('/blog/{id}', function ($path) {
        $data = explode("-", $path);
        if (!isset($data[3])) {
            return redirect(route('articles.index'));
        }
        return (new \App\Http\Controllers\ArticlesController())->redirect([
            'type_id' => \App\Constants\MaterialTypes::TYPE_ARTICLES,
            'original_id' => $data[3]
        ]);
    });

    Route::get('/news/{id}', function ($path) {
        $data = explode("-", $path);
        if (!isset($data[3])) {
            return redirect(route('articles.index'));
        }
        return (new ArticlesController())->redirect([
            'type_id' => \App\Constants\MaterialTypes::TYPE_NEWS,
            'original_id' => $data[3]
        ]);
    });


    Route::get('/stuff/{category_id}-1-0-{id}', function ($category_id, $id) {
        return (new \App\Http\Controllers\ArticlesController())->redirect([
            'type_id' => \App\Constants\MaterialTypes::TYPE_BLOG,
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

        Route::get('last-topics', [ForumController::class, 'lastTopics'])->name('last-topics');

        Route::get('0-{message_id}', [ForumController::class, 'redirectToMessageById'])->name('redirect-to-message-by-id');

        Route::get('{forum_id}-{topic_id}-0-17-1', [ForumController::class, 'redirectToLastMessage'])->name('topics.show-last-message');
        Route::get('{forum_id}-{topic_id}-{message_id}-{time}', [ForumController::class, 'redirectToMessage'])->name('topics.redirect-to-message');
        Route::get('{forum_id}-{topic_id}-{message_id}-{page_id}-{time}', [ForumController::class, 'redirectToMessage'])->name('topics.redirect-to-message-with-page');

        Route::get('{id}-0-{page_id}', [ForumController::class, 'subforum'])->name('subforums.show');

        Route::get('{forum_id}-{topic_id}-{page_id}', [ForumController::class, 'showTopic'])->name('topics.show-page');
        Route::get('{forum_id}-{topic_id}', [ForumController::class, 'showTopic'])->name('topics.show');

        Route::get('{id}', [ForumController::class, 'subforum'])->name('subforums.show');
        Route::get('{id}/new', [ForumController::class, 'newForum'])->name('subforums.new');
        Route::get('edit/{id}', [ForumController::class, 'editForum'])->name('subforums.edit');
        Route::post('new', [ForumController::class, 'createForum'])->name('subforums.create');
        Route::post('edit/{id}', [ForumController::class, 'saveForum'])->name('subforums.save');



        Route::post('post-message', [ForumController::class, 'postMessage'])->name('messages.create');
        Route::post('edit-message', [ForumController::class, 'editMessage'])->name('messages.update');
        Route::post('delete-message', [ForumController::class, 'deleteMessage'])->name('messages.delete');

        Route::get('user-messages/{user_id}', [ForumController::class, 'userMessages'])->name('user-messages');

    });

    Route::get('/forum/0-0-1-34', function () {
        return redirect(route('forum.last-topics'));
    });

    Route::name('forum.questionnaire.')->prefix('questionnaire')->group(function () {
        Route::post('vote', [QuestionnairesController::class, 'vote'])->name('vote');
        Route::get('form', [QuestionnairesController::class, 'form'])->name('form');
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
        Route::get('form', [AwardsController::class, 'form'])->name('form');
        Route::post('give-out', [AwardsController::class, 'create'])->name('create');
        Route::post('edit', [AwardsController::class, 'edit'])->name('edit');
        Route::post('delete', [AwardsController::class, 'delete'])->name('delete');
    });


    // WARNINGS

    Route::name('warnings.')->prefix('warnings')->group(function () {
        Route::get('ajax', [WarningsController::class, 'ajax'])->name('ajax');
        Route::get('form', [WarningsController::class, 'form'])->name('form');
        Route::post('add', [WarningsController::class, 'add'])->name('add');
    });


    // CONTACT FORM

    Route::name('contact.')->group(function () {
        Route::get('contact', [ContactFormController::class, 'index'])->name('index');
        Route::post('contact', [ContactFormController::class, 'send'])->name('send');
        Route::get('tape-digitization', [ContactFormController::class, 'digitization'])->name('digitization.index');
        Route::post('tape-digitization', [ContactFormController::class, 'digitizationSend'])->name('digitization.send');
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
                    return redirect('/');
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

    Route::get('/index/8', [UsersController::class, 'showMe'])->name('users.show-me');

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
                    return redirect('/');
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
      //  Route::any('/index/15-{page}', [UsersController::class, 'index'])->name('index');

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
        Route::get('permissions', [ProfileController::class, 'getPermissions'])->name('permissions');

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

    Route::name('pm.')->prefix('pm')->middleware(\App\Http\Middleware\Authenticate::class)->group(function() {
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

    Route::name('redactor.')->prefix('redactor-panel')->group(function() {
        Route::get('', [EditorController::class, 'approvePanel'])->name('approve-panel');

        Route::get('commercials', [EditorController::class, 'commercialsPanel'])->name('commercials.panel');
        Route::get('commercials/random', [EditorController::class, 'getRandomCommercial'])->name('commercials.get-random');
    });


    // OTHER

    Route::get('/smiles', [SmilesController::class, 'ajax'])->name('smiles.ajax');

    Route::get('/go', function () {
        $path = explode("/go?", $_SERVER['REQUEST_URI'])[1];
        return view('pages.redirect', ['path' => $path]);
        //return redirect($path);
    });

    Route::get('/site-search', [SiteSearchController::class, 'search'])->name('site-search');

    // ADMIN

//    Route::any('admin-login', function () {
//        return view("pages.maintenance_login");
//    });

    Route::middleware(\App\Http\Middleware\checkAdmin::class)->name('admin.')->prefix('admin')->group(function () {
        Route::get('', function () {
            return redirect(route('admin.pages'));
        });

        Route::resource('user-groups', AdminUserGroupsController::class);

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
            Route::get('list', [AdminUsersController::class, 'list'])->name('list');
            Route::post('change-group', [AdminUsersController::class, 'changeGroup'])->name('change-group');
            Route::post('change-password', [AdminUsersController::class, 'changePassword'])->name('change-password');
            Route::post('delete', [AdminUsersController::class, 'delete'])->name('delete');
            Route::get('reputation', [AdminUsersController::class, 'getReputationHistory'])->name('reputation');
        });

        Route::name('programs.')->prefix('programs')->group(function() {
            Route::get('', [AdminProgramsController::class, 'index'])->name('index');
            Route::get('list', [AdminProgramsController::class, 'list'])->name('list');
        });


        Route::name('genres.')->prefix('genres')->group(function() {
            Route::get('', [AdminGenresController::class, 'index'])->name('index');
            Route::post('', [AdminGenresController::class, 'save'])->name('save');
        });

        Route::get('pages', [AdminPagesController::class, 'index'])->name('pages.index');
        Route::get('actions-logs', [AdminActionsLogsController::class, 'index'])->name('actions-logs.index');
        Route::get('records-complaints', [AdminRecordComplaintsController::class, 'index'])->name('records-complaints.index');

        Route::get('crossposting', [CrosspostController::class, 'getServices'])->name('crossposting');

//        Route::get('run-command', function () {
//            if (request()->has('command')) {
//                Artisan::call(request()->input('command'));
//            }
//        });
    });

    // CROSSPOST
    Route::prefix('crosspost')->name('crossposts.')->group(function () {
        Route::middleware(\App\Http\Middleware\checkCanCrosspost::class)->group(function () {
            defineCrudRoutes(CrosspostController::class, [
                'show' => false,
                'approve' => false
            ]);

            Route::any('{id}/make-post/{service}', [CrosspostController::class, 'makePost'])->name('make-post');
            Route::any('{id}/delete-post/{service}', [CrosspostController::class, 'deletePost'])->name('delete-post');
        });

        Route::middleware(\App\Http\Middleware\checkAdmin::class)->group(function () {
            Route::get('autoconnect/{name}', [CrosspostController::class, 'autoconnect'])->name('autoconnect');
            Route::post('settings/{name}', [CrosspostController::class, 'saveSettings'])->name('save-settings');
            Route::get('redirect/{name}', [CrosspostController::class, 'afterRedirect'])->name('redirect-uri');
        });
    });


    Route::get('garland', function () {
        return view('blocks.global.garland');
    });

});
