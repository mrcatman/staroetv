<?php

namespace App\Http\Controllers;


use App\Comment;
use App\EmailChange;
use App\ForumMessage;
use App\Helpers\BBCodesHelper;
use App\Helpers\DatesHelper;
use App\Helpers\PermissionsHelper;
use App\Mail\ChangeEmail;
use App\Mail\VerifyAccount;
use App\Record;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersController extends Controller {

    public function show($conditions) {
        $user = User::where($conditions)->first();

        if (!$user) {
            return view("pages.errors.404");
        }
        $videos = Record::approved()->where(['author_id' => $user->id, 'is_radio' => false])->orderBy('id', 'desc')->get();
        $radio_recordings = Record::approved()->where(['author_id' => $user->id, 'is_radio' => true])->orderBy('id', 'desc')->get();

        $banned_till = null;
        $is_banned_forever = $user->group_id == 255;
        if ($user->warnings->count() > 0) {
            $last_ban = $user->warnings->first();
            if ($last_ban->weight == 1 && $last_ban->time_expires > time()) {
                $banned_till = DatesHelper::formatTS($last_ban->time_expires);
            }
        }
        return view("pages.users.show", [
            'banned_till' => $banned_till,
            'is_banned_forever' => $is_banned_forever,
            'user' => $user,
            'radio_recordings' => $radio_recordings,
            'videos' => $videos,
        ]);
    }

    public function list() {
        $on_page = request()->input('on_page', 10);
        $search = "";
        $group_id = 0;
        $sort_by = "username";
        $sort_dir = "ASC";
        $available_sortings = ["username", "group_id", "created_at", "was_online"];
        if (PermissionsHelper::allows('usearch')) {
            $is_moderator = PermissionsHelper::allows('usedita');
            if (request()->has('sort_by') && in_array(request()->input('sort_by'), $available_sortings)) {
                $sort_by = request()->input('sort_by');
            }
            if ($sort_by == "was_online") {
                $sort_dir = "DESC";
            }
            $users = User::orderBy($sort_by, $sort_dir);
            if (request()->has('search')) {
                $search = request()->input('search');
                $field = "username";
                if (request()->has('search_field') && $is_moderator) {
                    $field = request()->input('search_field');
                }
                $users = $users->where($field, 'LIKE', '%'.$search.'%');
            }
            if (request()->has('group_id')) {
                $group_id = request()->input('group_id');
                if ($group_id > 0) {
                    $users = $users->where(['group_id' => $group_id]);
                }
            }

            $total = $users->count();
            $users = $users->paginate($on_page);
            $users = $users->appends(request()->except('page'));
            return view("pages.users.list", [
                'is_moderator' => $is_moderator,
                'sort_by' => $sort_by,
                'on_page' => $on_page,
                'group_id' => $group_id,
                'search' => $search,
                'total' => $total,
                'users' => $users
            ]);
        } else {
            return redirect("https://staroetv.su/");
        }
    }

    private function countries() {
        return [["text"=>"Российская Федерация","id"=>"169"],["text"=>"Украина","id"=>"204"],["text"=>"Беларусь","id"=>"31"],["text"=>"Казахстан","id"=>"111"],["text"=>"Австралия","id"=>"12"],["text"=>"Австрия","id"=>"11"],["text"=>"Азербайджан","id"=>"14"],["text"=>"Албания","id"=>"5"],["text"=>"Алжир","id"=>"55"],["text"=>"Американское Самоа","id"=>"10"],["text"=>"Ангилья","id"=>"221"],["text"=>"Английская Индийская Океаническая Территория","id"=>"94"],["text"=>"Ангола","id"=>"8"],["text"=>"Андорра","id"=>"1"],["text"=>"Антарктика","id"=>"222"],["text"=>"Антигуа и Барбуда","id"=>"4"],["text"=>"Антильский Остров Нидерландов","id"=>"7"],["text"=>"Арабская Республика Суринам","id"=>"186"],["text"=>"Аргентина","id"=>"9"],["text"=>"Армения","id"=>"6"],["text"=>"Аруба","id"=>"13"],["text"=>"Афганистан","id"=>"3"],["text"=>"Багамский Остров","id"=>"28"],["text"=>"Бангладеш","id"=>"17"],["text"=>"Барбадос","id"=>"16"],["text"=>"Бахрейн","id"=>"21"],["text"=>"Белиз","id"=>"32"],["text"=>"Бельгия","id"=>"18"],["text"=>"Бенин","id"=>"23"],["text"=>"Болгария","id"=>"20"],["text"=>"Боливия","id"=>"26"],["text"=>"Босния и Герцеговина","id"=>"15"],["text"=>"Ботсвана","id"=>"30"],["text"=>"Бразилия","id"=>"27"],["text"=>"Бруней","id"=>"25"],["text"=>"Буркина Фасо","id"=>"19"],["text"=>"Бурунди","id"=>"22"],["text"=>"Бутан","id"=>"29"],["text"=>"Вануату","id"=>"215"],["text"=>"Ватикан","id"=>"209"],["text"=>"Великобритания","id"=>"69"],["text"=>"Венгрия","id"=>"89"],["text"=>"Венесуэла","id"=>"211"],["text"=>"Виргинские острова (UK)","id"=>"212"],["text"=>"Восточный Тимор","id"=>"195"],["text"=>"Вьетнам","id"=>"214"],["text"=>"Габон","id"=>"68"],["text"=>"Гаити","id"=>"88"],["text"=>"Гайана","id"=>"84"],["text"=>"Гамбия","id"=>"76"],["text"=>"Гана","id"=>"73"],["text"=>"Гваделупа","id"=>"78"],["text"=>"Гватемала","id"=>"81"],["text"=>"Гвинея","id"=>"77"],["text"=>"Гвинея-Бисау","id"=>"83"],["text"=>"Германия","id"=>"50"],["text"=>"Гернси и Олдерни","id"=>"235"],["text"=>"Гибралтар","id"=>"74"],["text"=>"Гонг-Конг","id"=>"85"],["text"=>"Гондурас","id"=>"86"],["text"=>"Гренада","id"=>"70"],["text"=>"Гренландия","id"=>"75"],["text"=>"Греция","id"=>"80"],["text"=>"Грузия","id"=>"71"],["text"=>"Дания","id"=>"52"],["text"=>"Дем. республика Конго","id"=>"34"],["text"=>"Джибути","id"=>"51"],["text"=>"Доминика","id"=>"53"],["text"=>"Доминиканская Республика","id"=>"54"],["text"=>"Египет","id"=>"58"],["text"=>"Еритреа","id"=>"59"],["text"=>"Замбия","id"=>"219"],["text"=>"Зимбабве","id"=>"220"],["text"=>"Израиль","id"=>"92"],["text"=>"Индия","id"=>"93"],["text"=>"Индонезия","id"=>"90"],["text"=>"Иордания","id"=>"100"],["text"=>"Ирак","id"=>"95"],["text"=>"Иран","id"=>"96"],["text"=>"Ирландия","id"=>"91"],["text"=>"Исландия","id"=>"97"],["text"=>"Испания","id"=>"60"],["text"=>"Италия","id"=>"98"],["text"=>"Йемен","id"=>"217"],["text"=>"Кабо Верде","id"=>"47"],["text"=>"Каймановы острова","id"=>"110"],["text"=>"Камбоджа","id"=>"104"],["text"=>"Камерун","id"=>"41"],["text"=>"Канада","id"=>"33"],["text"=>"Катар","id"=>"166"],["text"=>"Кения","id"=>"102"],["text"=>"Кипр","id"=>"48"],["text"=>"Кирибати","id"=>"105"],["text"=>"Китай","id"=>"42"],["text"=>"Колумбия","id"=>"43"],["text"=>"Комморские острова","id"=>"106"],["text"=>"Конго","id"=>"36"],["text"=>"Корея","id"=>"108"],["text"=>"Коста Рика","id"=>"44"],["text"=>"Кот Д'ивуар","id"=>"38"],["text"=>"Куба","id"=>"46"],["text"=>"Кувейт","id"=>"109"],["text"=>"Кыргызстан","id"=>"103"],["text"=>"Лаос","id"=>"112"],["text"=>"Латвия","id"=>"121"],["text"=>"Лесото","id"=>"118"],["text"=>"Либерия","id"=>"117"],["text"=>"Ливан","id"=>"113"],["text"=>"Ливийская Арабская республика Джамахирия","id"=>"122"],["text"=>"Литва","id"=>"119"],["text"=>"Лихтенштейн","id"=>"115"],["text"=>"Люксембург","id"=>"120"],["text"=>"Маврикий","id"=>"137"],["text"=>"Мавритания","id"=>"135"],["text"=>"Мадагаскар","id"=>"126"],["text"=>"Макао","id"=>"132"],["text"=>"Македония","id"=>"128"],["text"=>"Малави","id"=>"139"],["text"=>"Малайзия","id"=>"141"],["text"=>"Мали","id"=>"129"],["text"=>"Мальдивы","id"=>"138"],["text"=>"Мальта","id"=>"136"],["text"=>"Марокко","id"=>"123"],["text"=>"Мартиника","id"=>"134"],["text"=>"Маршалловы острова","id"=>"127"],["text"=>"Мексика","id"=>"140"],["text"=>"Мозамбик","id"=>"142"],["text"=>"Молдова","id"=>"125"],["text"=>"Монако","id"=>"124"],["text"=>"Монголия","id"=>"131"],["text"=>"Монтсеррат","id"=>"229"],["text"=>"Мьянмар","id"=>"130"],["text"=>"Намибия","id"=>"143"],["text"=>"Науру","id"=>"151"],["text"=>"Непал","id"=>"150"],["text"=>"Нигер","id"=>"145"],["text"=>"Нигерия","id"=>"146"],["text"=>"Нидерланды","id"=>"148"],["text"=>"Никарагуа","id"=>"147"],["text"=>"Ниуэ","id"=>"231"],["text"=>"Новая Зеландия","id"=>"152"],["text"=>"Новая Каледония","id"=>"144"],["text"=>"Норвегия","id"=>"149"],["text"=>"Объединенные Арабские Эмираты","id"=>"2"],["text"=>"Оман","id"=>"153"],["text"=>"Остров Святой Елены","id"=>"237"],["text"=>"Острова Кука","id"=>"39"],["text"=>"Острова Уоллис и Футуна","id"=>"233"],["text"=>"Острова Фару","id"=>"66"],["text"=>"Пакистан","id"=>"159"],["text"=>"Палау","id"=>"164"],["text"=>"Палестинская Территория","id"=>"162"],["text"=>"Панама","id"=>"154"],["text"=>"Папуа Новая Гвинея","id"=>"157"],["text"=>"Парагвай","id"=>"165"],["text"=>"Перу","id"=>"155"],["text"=>"Польша","id"=>"160"],["text"=>"Португалия","id"=>"163"],["text"=>"Реюнион","id"=>"167"],["text"=>"Руанда","id"=>"170"],["text"=>"Румыния","id"=>"168"],["text"=>"Самоа","id"=>"216"],["text"=>"Сан-Марино","id"=>"180"],["text"=>"Сан-Томе и Принсипи","id"=>"184"],["text"=>"Саудовская Аравия","id"=>"171"],["text"=>"Свазиленд","id"=>"187"],["text"=>"Святого Винсента и Гренадины","id"=>"210"],["text"=>"Святой Киттс и Невис","id"=>"107"],["text"=>"Северная Корея","id"=>"236"],["text"=>"Сейшелы","id"=>"173"],["text"=>"Сенегал","id"=>"181"],["text"=>"Сент-Люсия","id"=>"114"],["text"=>"Сербия","id"=>"45"],["text"=>"Сингапур","id"=>"176"],["text"=>"Словакия","id"=>"178"],["text"=>"Словения","id"=>"177"],["text"=>"Соединенные Штаты","id"=>"206"],["text"=>"Соломоновы острова","id"=>"172"],["text"=>"Сомали","id"=>"182"],["text"=>"Судан","id"=>"174"],["text"=>"Суринам","id"=>"183"],["text"=>"Сьерра-Леоне","id"=>"179"],["text"=>"Таджикистан","id"=>"193"],["text"=>"Таиланд","id"=>"192"],["text"=>"Тайвань","id"=>"202"],["text"=>"Такелау","id"=>"194"],["text"=>"Танзания","id"=>"203"],["text"=>"Того","id"=>"191"],["text"=>"Тонга","id"=>"198"],["text"=>"Тринидад и Тобаго","id"=>"200"],["text"=>"Тувалу","id"=>"201"],["text"=>"Тунис","id"=>"197"],["text"=>"Туркменистан","id"=>"196"],["text"=>"Турция","id"=>"199"],["text"=>"Уганда","id"=>"205"],["text"=>"Узбекистан","id"=>"208"],["text"=>"Уругвай","id"=>"207"],["text"=>"Федеративные Штаты Микронезия","id"=>"65"],["text"=>"Фиджи","id"=>"63"],["text"=>"Филиппины","id"=>"158"],["text"=>"Финляндия","id"=>"62"],["text"=>"Фолклендские острова","id"=>"64"],["text"=>"Франция","id"=>"67"],["text"=>"Французская Гвиана","id"=>"72"],["text"=>"Французская Полинезия","id"=>"156"],["text"=>"Хорватия","id"=>"87"],["text"=>"Центральная Африканская Республика","id"=>"35"],["text"=>"Чад","id"=>"189"],["text"=>"Черногория","id"=>"228"],["text"=>"Чешская Республика","id"=>"49"],["text"=>"Чили","id"=>"40"],["text"=>"Швейцария","id"=>"37"],["text"=>"Швеция","id"=>"175"],["text"=>"Шри-Ланка","id"=>"116"],["text"=>"Эквадор","id"=>"56"],["text"=>"Экваториальная Гвинея","id"=>"79"],["text"=>"Эль Сальвадор","id"=>"185"],["text"=>"Эстония","id"=>"57"],["text"=>"Эфиопия","id"=>"61"],["text"=>"Южная Африка","id"=>"218"],["text"=>"Ямайка","id"=>"99"],["text"=>"Япония","id"=>"101"]];
    }

    public function edit($id = null) {
        $user = auth()->user();
        $edit_id = null;
        if ($id && PermissionsHelper::allows('usedita')) {
            $edit_id = $id;
            $user = User::find($id);
        }
        if (!$user) {
            return redirect("https://staroetv.su/");
        }
        return view("pages.forms.user", [
            'edit_id' => $edit_id,
            'user' => $user,
            'countries' => $this->countries()
        ]);
    }

    public function editPassword() {
        $user = auth()->user();

        return view("pages.forms.user-password", [
            'user' => $user,
        ]);
    }


    public function save() {
        if (!auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();
        if (request()->has('user_id')) {
            if (PermissionsHelper::allows('usedita')) {
                $user = User::find(request()->input('user_id'));
                if (!$user) {
                    return [
                        'status' => 0,
                        'text' => 'Пользователь не найден'
                    ];
                }
            } else {
                return [
                    'status' => 0,
                    'text' => 'Ошибка доступа'
                ];
            }
        }
        $data = request()->validate([
            'avatar_id' => 'sometimes',
            'username' => 'required|unique:users,username,'.$user->id,
            'email' => 'required|email',
            'name' => 'sometimes',
            'date_of_birth' => 'sometimes',
            'country' => 'sometimes',
            'city' => 'sometimes',
            'avatar' => 'sometimes',
            'signature' => 'sometimes',
            'vk' => 'sometimes',
            'youtube' => 'sometimes',
            'yandex_video' => 'sometimes',
            'facebook' => 'sometimes',
            'user_comment' => 'sometimes'
        ]);
        $meta = $user->meta;
        if (!$meta) {
            $meta = new UserMeta(['user_id' => $user->id]);
        }
        $change_email = false;
        $meta_fields = ['date_of_birth', 'country', 'city', 'vk', 'youtube', 'yandex_video', 'facebook'];
        foreach ($data as $field => $value) {
            $value = trim($value);
            if (in_array($field, $meta_fields)) {
                if ($field === "date_of_birth") {
                    $value = Carbon::parse( $value);
                }
                $meta->{$field} = $value;
            } else {
                if ($field === "signature") {
                    $value = BBCodesHelper::BBToHTML($value);
                }
                if ($field === "email" && $user->email != $value && !request()->has('user_id')) {
                    $user_with_same_email = User::where(['email' => $value])->first();
                    if ($user_with_same_email) {
                        $error = \Illuminate\Validation\ValidationException::withMessages([
                            'email' => ['Другой пользователь с такой почтой уже зарегистрирован'],
                        ]);
                        throw $error;
                    }
                    $change = new EmailChange([
                        'user_id' => $user->id,
                        'email' => $value,
                        'code' => bin2hex(random_bytes(8))
                    ]);
                    $change->save();
                    Mail::to($value)->send(new ChangeEmail($user, $change));
                    $change_email = true;
                } else {
                    $user->{$field} = $value;
                }
            }
        }
        $user->save();
        $meta->save();
        return [
            'status' => 1,
            'text' => $change_email ? 'Сохранено. На новый e-mail адрес выслано письмо со ссылкой для подтверждения' : 'Сохранено'
        ];
    }

    public function savePassword() {
        if (!auth()->user()) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        $user = auth()->user();

        $data = request()->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed|min:7',
        ]);
        if (!Hash::check($data['old_password'], $user->password)) {
            return [
                'status' => 0,
                'text' => 'Неверный старый пароль'
            ];
        }
        $user->password = Hash::make($data['password']);
        $user->save();
        return [
            'status' => 1,
            'text' => 'Пароль изменен'
        ];
    }

    public function changeEmail($code) {
        $code = EmailChange::where(['code' => $code])->first();
        if ($code) {
            $user = User::find($code->user_id);
            $user->email = $code->email;
            $user->save();
            $code->delete();
            return redirect($user->url)->with('after_confirm', true);
        }
    }

    public function autocomplete() {
        $count = 30;
        $users = User::select('id', 'username')->orderBy('was_online', 'desc');
        if (request()->has('term')) {
            $users = $users->where('username', 'LIKE', '%'.request()->input('term').'%');
        }
        $total = $users->count();
        $page = request()->input('page', 1);
        $users = $users->limit($count)->offset($count * ($page - 1))->get();
        return [
            'status' => 1,
            'data' => [
                'total' => $total,
                'users' => $users
            ]
        ];
    }

    public function getNotifications() {
        $user = auth()->user();
        if (!$user) {
            return [
                'status' => 0,
                'text' => 'Ошибка доступа'
            ];
        }
        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }
        $list = $user->notifications;
        $notifications = [];
        foreach ($list as $notification) {
            $data = $notification->data;
            if ($notification->type == "App\Notifications\NewCommentReply") {
                $comment = null;
                if (isset($data['comment_id'])) {
                    $comment = Comment::find($data['comment_id']);
                }
                if (!$comment) {
                    $notification->delete();
                    continue;
                }
                $text = "<strong>" . $data['comment_username'] . "</strong> ответил вам в комментариях:";
                $short_content = Str::limit($data['comment_text'], 160, '...');
                $text .= "<div class='notification__quote'  data-full-text='" . $data['comment_text'] . "'>" . $short_content . "</div>";
                $link = $comment->url;
                $picture = $data['comment_avatar'];
                $notifications[] = (object)[
                    'picture' => $picture,
                    'text' => $text,
                    'link' => $link,
                    'time' => $notification->created_at->format('d.m.Y H:i')
                ];
            } elseif ($notification->type == "App\Notifications\NewForumReply") {
                $message = null;
                if (isset($data['message_id'])) {
                    $message = ForumMessage::find($data['message_id']);
                }
                if (!$message) {
                    $notification->delete();
                    continue;
                }
                $text = "<strong>" . $data['message_username'] . "</strong> ответил вам на форуме:";
                //$short_reply_to = Str::limit($data['message_reply_to'], 100, '...');
                $short_content = Str::limit($data['message_content'], 160, '...');
                $text .= "<div class='notification__quote'  data-full-text='" . $data['message_content'] . "'>" . $short_content . "</div>";
                $link = "/forum/0-" . $data['message_id'] . '#' . $data['message_id'];
                $picture = $data['message_avatar'];
                $notifications[] = (object)[
                    'picture' => $picture,
                    'text' => $text,
                    'link' => $link,
                    'time' => $notification->created_at->format('d.m.Y H:i')
                ];
            }
        }
        return [
            'status' => 1,
            'data' => [
                'dom' => [
                    [
                        'replace' => '.notifications__list',
                        'html' => view("blocks/notifications", ['notifications' => $notifications])->render()
                    ]
                ]
            ]
        ];
    }


    public function comments($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect("https://staroetv.su/");
        }
        $comments = Comment::where(['user_id' => $id])->orderBy('id', 'desc')->paginate(30);

        return view("pages.users.comments", [
            'comments' => $comments,
            'user' => $user,
        ]);
    }

    public function videos($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect("https://staroetv.su/");
        }
        $records = Record::where(['is_radio' => false, 'author_id' => $id])->orderBy('id', 'desc')->paginate(30);
        return view("pages.users.records", [
            'page_title' => 'Видеозаписи',
            'records' => $records,
            'user' => $user,
        ]);
    }

    public function radioRecordings($id) {
        $user = User::find($id);
        if (!$user) {
            return redirect("https://staroetv.su/");
        }
        $records = Record::where(['is_radio' => true, 'author_id' => $id])->orderBy('id', 'desc')->paginate(30);
        return view("pages.users.records", [
            'page_title' => 'Радиозаписи',
            'records' => $records,
            'user' => $user,
        ]);
    }


}
