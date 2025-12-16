<div class="generic-sidebar">
    <div class="box">
        <div class="box__heading">
            <div class="box__heading__inner">
                Случайные записи
            </div>
        </div>
        <div class="box__inner">
            <div class="records-list">
                @if (!isset($is_radio))
                    @php($is_radio = false)
                @endif
                @php ($count = isset($count) && $count ? $count : 10)
                @foreach (\App\Helpers\SidebarHelper::getRecords($is_radio, $count) as $record)
                    @include($is_radio ? 'blocks.records.radio-item' : 'blocks.records.item', ['record' => $record])
                @endforeach
            </div>
        </div>
    </div>
    @if (!isset($hide_articles) || !$hide_articles)
    <div class="box">
        <div class="box__heading box__heading--small">
            <div class="box__heading__inner">
                Читайте на нашем сайте
            </div>
        </div>
        <div class="box__inner">
            <div class="see-also">
                @php ($articles_count = isset($articles_count) && $articles_count ? $articles_count : 5)
                @foreach (\App\Helpers\SidebarHelper::getArticles($articles_count) as $see_also_item)
                    @include('blocks.articles.item-small', ['article' => $see_also_item])
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
