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
                @foreach (\App\Helpers\SidebarHelper::getRecords($is_radio) as $record)
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
                @foreach (\App\Helpers\SidebarHelper::getArticles() as $see_also_item)
                    @include('blocks/article_small', ['article' => $see_also_item])
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
