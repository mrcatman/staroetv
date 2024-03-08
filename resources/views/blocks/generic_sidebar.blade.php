<div class="generic-sidebar">
    <div class="box">
        <div class="box__heading box__heading--small">
            Случайные записи из архива
        </div>
        <div class="box__inner">
            <div class="record-page__related">
                @if (!isset($is_radio))
                    @php($is_radio = false)
                @endif
                @php ($count = isset($count) && $count ? $count : 10)
                @foreach (\App\Helpers\SidebarHelper::getRecords($is_radio, $count) as $record)
                    @include($is_radio ? 'blocks/radio_recording' : 'blocks/record', ['record' => $record])
                @endforeach
            </div>
        </div>
    </div>
    @if (!isset($hide_articles) || !$hide_articles)
    <div class="box">
        <div class="box__heading box__heading--small">
            Читайте на нашем сайте
        </div>
        <div class="box__inner">
            <div class="see-also">
                @php ($articles_count = isset($articles_count) && $articles_count ? $articles_count : 5)
                @foreach (\App\Helpers\SidebarHelper::getArticles($articles_count) as $see_also_item)
                    @include('blocks/article_small', ['article' => $see_also_item])
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
