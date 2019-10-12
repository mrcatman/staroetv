<html><head>
    <style>
        @import  url('https://fonts.googleapis.com/css?family=Oswald&display=swap&subset=cyrillic');
        @font-face {
            font-family: "FuturaDemiC";
            src: url("/fonts/FuturaDemiC.otf") format("opentype");
        }
        body {

        }
        body {
            margin: 0;
            padding: 0;
            background: #111;
            color: #fff;
            font-size: 20px;
            font-family: "FuturaDemiC";
        }

        a {
            color: #fff;
            font-family: "Oswald";
        }

        .logo {
            font-family: "Oswald";
            font-size: 2.5em;
            text-transform: uppercase;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2em;
            font-family: "Oswald";
            font-size: .75em;
        }

        .header__left {
            display: flex;
            align-items: center;
        }

        .top-menu {
            font-size: 1.25em;
            margin: 0 0 0 1em;
        }

        a.top-menu__link {
            margin: 0 .5em;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
        }

        .main {
            padding: 30em 0 0;
        }

        .box {
            background: #fff;
            color: #000;
            padding: 1.5em;
            margin: 1em 0;
        }

        .header__right {
            font-size: 1.5em;
        }

        .article__cover {
            height: 13em;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .articles-list {
            display: flex;
            flex-wrap: wrap;
        }

        a.article {
            width: calc(100% / 3 - 3em);
            padding: 1em;
            background: #fff;
            color: #000;
            margin: .5em;
            text-decoration: none;
        }

        .article__short-content {
            display: none;
        }

        a.article:nth-of-type(1) {
            /* flex: 1; */
            width: calc(100% / 3 * 2 - 2em);
        }

        .main {
            position: relative;
        }

        .main:before {content: "";display: block;position: absolute;top: 0;left: 0;width: 100%;height: 10em;background: linear-gradient(#111, #0000);}

        .main:after {
            background-image: url(https://st.depositphotos.com/1796022/3518/v/600/depositphotos_35186363-stock-video-tv-noise.jpg);
            background-size: cover;
            position: relative;
            content: "";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: .5;
        }

        .main__text {
            font-size: 1.5em;
        }

        .article:nth-of-type(3n), .article:nth-of-type(4n), .article:nth-of-type(5n), .article:nth-of-type(6n) {
            width: calc(100% / 4 - 5em);
        }

        .article:nth-of-type(6n) {
            margin-right: 0;
        }

        .article__title {
            font-size: 1.25em;
            text-decoration: none;
        }

        html {}
    </style>
</head>
<body>
<div class="header">
    <div class="header__left">
        <div class="logo">Старый Телевизор</div>
        <div class="top-menu">
            <a class="top-menu__link" href="/articles">Статьи</a>
            <a class="top-menu__link" href="/videos">Видеоархив</a>
            <a class="top-menu__link" href="/forum">Форум</a>
        </div>
    </div>
    <div class="header__right">
        <div class="profile">
            Добро пожаловать, <a href="/index/8-1">username</a>!
            <a class="profile__logout">Выйти</a>
        </div>
    </div>
</div>
<div class="main">
    <div class="container">
        <div class="main__text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In et leo semper purus gravida ullamcorper quis sit amet mi. Quisque et libero scelerisque, rutrum felis volutpat, tristique erat. Quisque mattis tristique metus, in feugiat justo suscipit non. Aliquam erat volutpat. Donec ac semper neque. Praesent interdum nisi sit amet justo consectetur convallis. Nam vehicula ligula ac orci commodo ultricies. Nulla eget lacinia lorem. Nullam nec lorem sapien. Fusce diam erat, aliquam a tellus a, placerat porttitor est. Maecenas ullamcorper auctor elit, vitae elementum est. Nulla elementum lacinia vestibulum. Nulla facilisi. Integer laoreet placerat lacus eu pellentesque.</div>
        <div class="box">
            Quisque varius tincidunt arcu, vel ullamcorper nisl consectetur at. Aenean mattis nunc eget libero venenatis, in viverra mi facilisis. Aenean ultrices mi eu vulputate dignissim. Donec consectetur velit et pellentesque maximus. Praesent nec augue eu diam dictum lobortis vel eget elit. Pellentesque bibendum, leo vitae feugiat cursus, ex nibh commodo mauris, quis pulvinar nibh urna eu est. Fusce hendrerit laoreet porta. Mauris id lectus vulputate nunc pharetra iaculis a ultrices eros. Proin laoreet scelerisque neque vel euismod. Cras at lacinia neque. Ut fringilla ipsum ut vulputate viverra. Vestibulum quis semper urna.
            <br>
            Nulla sollicitudin, nunc nec elementum accumsan, mi nisl sollicitudin tellus, et egestas lacus sapien eu odio. Mauris consequat, nisl sed malesuada hendrerit, risus lectus consequat diam, quis mattis elit odio ornare felis. Maecenas quis mi at ligula tristique venenatis nec venenatis nisl. Integer est arcu, volutpat in condimentum at, convallis id eros. Donec consequat urna vel pharetra pharetra. Integer ut lacinia mauris. Duis in velit libero. Mauris pulvinar porta neque ac aliquam.
        </div>
        <div class="articles-list">
            <a href="" class="article" style="
    flex: 3;
">
                <div class="article__cover" style="background-image: url(http://staroetv.su/01_history/0001/1995ntvneotdaviyniver_00000.png)"></div>
                <div class="article__title">20 декабря 1995. Ельцин оставил четвёртый канал за «Российскими университетами». Глава НТВ Игорь Малашенко с решением не согласен</div>
                <div class="article__short-content">Серьезный конфликт двух крупных российских телекомпаний &amp;mdash; НТВ и ВГТРК &amp;mdash; из-за IV канала, похоже, разрешился в пользу ВГТРК. Борис Ельцин в минувшую пятницу подписал распоряжение, согласно которому ВГТРК поручено разработать новую концепцию канала &amp;laquo;Российские университеты&amp;raquo;. Казалось бы, один из самых острых телевизионных конфликтов года исчерпан. Однако полученный ВГТРК и НТВ документ вызвал диаметрально противоположную реакцию руководителей телекомпаний. Председатель ВГТР...</div>
            </a>
            <a href="" class="article">
                <div class="article__cover" style="background-image: url(http://staroetv.su/01_history/0001/2000putinvstv_00000.png);"></div>
                <div class="article__title">14 августа 2000. Кремль хочет подчинить себе ОРТ и НТВ. Ясности нет ни там, ни там</div>
                <div class="article__short-content">О желании власти кардинально изменить ситуацию на рынке электронных СМИ и поставить под свой жесткий контроль все три общенациональных телеканала (добавив к полностью принадлежащему ему РТР еще и полугосударственное ОРТ, и коммерческое НТВ) в телесообществе говорили с момента президентских выборов, но высказывать это публично до настоящего момента никто из госчиновников не решался. И вот в первый день августа Кремль устами представителя президентской администрации, настаивающего на своей анонимн...</div>
            </a>
            <a href="" class="article">
                <div class="article__cover" style="background-image: url(http://staroetv.su/01_history/0001/2003ntvsenkevich_00000.png);"></div>
                <div class="article__title">27 января 2003. Николай Сенкевич неожиданно заменил Бориса Йордана на посту гендиректора НТВ</div>
                <div class="article__short-content">Председатель правления компании &amp;laquo;Газпром&amp;raquo; Алексей Миллер продолжил традицию неожиданных назначений, начатую его собственным появлением в крупнейшей российской корпорации. Неожиданно уволив Бориса Йордана, результатами деятельности которого еще недавно был доволен, назначил на место главы крупнейшей компании, входящей в &amp;laquo;Газпром-Медиа&amp;raquo;, врача Николая Сенкевича.Чем бы ни было вызвано увольнение Йордана (недовольством Путина информационной политикой канала, не удовлетворяющи...</div>
            </a>
            <a href="" class="article">
                <div class="article__cover" style="background-image: url(http://staroetv.su/01_history/0001/2003reality_00000.png)"></div>
                <div class="article__title">Июнь 2003. Власть хочет, а телебизнес заинтересован стать сугубо развлекательным. Реалити-шоу и сериалы заменяют реальность</div>
                <div class="article__short-content">В эпоху Путина телевидение стало прибыльным бизнесом, но утратило независимость. С развалом ТВС в России закончилась эпоха частных общественно-политических каналов. Отечественное телевидение стало в массе своей развлекательным. С точки зрения бизнеса это оправданно &amp;mdash;&amp;nbsp;телекомпании одна за другой стали показывать прибыль. С точки зрения общественной пользы выгоды сомнительны &amp;mdash;&amp;nbsp;электронные СМИ больше не могут осуществлять контроль за деятельностью государства.У нового российск...</div>
            </a>
            <a href="" class="article">
                <div class="article__cover" style="background-image: url(http://staroetv.su/01_history/0001/2006tvcprocvetaet_00000.png)"></div>
                <div class="article__title">Июнь 2006. «Неконкурентоспособный» ТВЦ хочет омолодить аудиторию. Утверждены новая концепция и стиль канала</div>
                <div class="article__short-content">Как стало известно &amp;laquo;Бизнесу&amp;raquo;, к началу осени канал ТВЦ изменит концепцию, увеличив объем развлекательного вещания, и фирменный стиль. За счет этих действий руководство канала рассчитывает существенно омолодить аудиторию и довести долю до 4% по России и 8% &amp;mdash; по Москве.&amp;nbsp;Эксперты говорят, что развлекательное вещание помогает привлечь аудиторию, однако указывают на то, что в новой нише ТВЦ придется конкурировать с СТС и ТНТ.Напомним, в декабре прошлого года ушел в отставку пре...</div>
            </a>
            <a href="" class="article">
                <div class="article__cover" style="background-image: url(http://staroetv.su/01_history/0001/2000tntdvagoda_00000.png)"></div>
                <div class="article__title">10 января 2000. Павел Корчагин о двухлетнем юбилее ТНТ: «Мы создали ситуацию, когда другие каналы поняли, что живут неправильно»</div>
                <div class="article__short-content">1 января телесеть ТНТ отметила маленький юбилей &amp;mdash; два года. Два года пребывания в эфире. Как телекомпания ТНТ существует несколько больше: она была создана осенью 1997 года в рамках &amp;laquo;НТВ-Холдинга&amp;raquo;. А 1 января 1998 года вышла в российский эфир. Об истории создания ТНТ, его планах рассказывает генеральный директор Павел КОРЧАГИН.&amp;mdash; Чем ТНТ как телекомпания занималась до появления в эфире?&amp;mdash; Мы готовились к тому, чтобы появиться в эфире. Три месяца готовились. Обычно люд...</div>
            </a>
            <a href="" class="article" style="
    flex: 1;
">
                <div class="article__cover" style="background-image: url(http://staroetv.su/01_history/0001/2000ng_00000.png)"></div>
                <div class="article__title">28 декабря 2000. Гид по новогоднему телевидению 2000-2001</div>
                <div class="article__short-content">Новый год, пожалуй, единственный праздник, во время которого в каждой квартире беспрерывно работает телевизор. По большей части фоном застолья. Наш гид может послужить не только по своему прямому назначению, но и как помощник в освежении воспоминаний о буйной эйфории праздника30 декабря. В ожидании чудаПод Новый год все ждут чудес. Осуществить их нам поможет чародей и кудесник Валдис Пельш в новом игровом шоу &amp;laquo;О, Чудо&amp;raquo;, где он исполняет заветные мечты участников &amp;mdash; сыграть в фут...</div>
            </a>

        </div>
    </div>

</div></body></html>