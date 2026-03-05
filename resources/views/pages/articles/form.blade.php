@extends('layouts.default', ['vue' => true])
@section('page-title')
    {{$article ? "Редактировать публикацию" : "Добавить публикацию"}}
@endsection
@section('content')
    <form class="form box" action="{{$article ? route('articles.edit', $article->id) : route('articles.add')}}" method="POST">
        <div class="box__heading">
            <div class="box__heading__inner">
                {{$article ? "Редактировать публикацию" : "Добавить публикацию"}}
            </div>

            <div class="box__heading__right">
                @if ($article)
                    <a href="{{$article->full_url}}" class="button button--light">Назад</a>
                @endif
            </div>
        </div>
        <div class="box__inner">
            <div class="form__content">
                <div class="response"></div>
                <div class="input-container">
                    <label class="input-container__label">Заголовок<span
                            class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <input class="input" name="title" value="{{$article ? $article->title : ""}}"/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                @if ($can_edit_all)
                    <div class="input-container">
                        <label class="input-container__label">Дата публикации</label>
                        <div class="input-container__inner">
                            <input class="input" type="datetime-local" name="created_at" value="{{$article ? \Carbon\Carbon::createFromTimestamp($article->created_at_original)->format('Y-m-d H:i') : '' }}"/>
                            <div class="input-container__description">Если дата больше текущей, то статья автоматически станет доступной в указанное время</div>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                @endif
                <div class="input-container">
                    <label class="input-container__label">Краткое описание</label>
                    <div class="input-container__inner">
                        <div class="input-container__element-outer">
                        <textarea class="input"
                                  name="short_content">{{$article ? $article->short_content : ""}}</textarea>
                            <div class="input-container__description">Будет отображаться в общем списке статей</div>
                            <span class="input-container__message"></span>
                        </div>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Текст<span class="input-container__required">*</span></label>
                    <div class="input-container__inner">
                        <tiptap-editor name="content" :content='@json($article ? $article->content : '')'></tiptap-editor>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Короткий URL</label>
                    <div class="input-container__inner">
                        <input class="input" name="slug" value="{{$article ? $article->slug : ""}}"/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Источник</label>
                    <div class="input-container__inner">
                        <input class="input" name="source" value="{{$article ? $article->source : ""}}"/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Обложка</label>
                    <div class="input-container__inner">
                        <picture-uploader name="cover_id"
                                          :data="{{$article && $article->coverPicture ? $article->coverPicture : "null"}}"/>
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Теги</label>
                    <div class="input-container__inner">
                        <tags-editor
                            name="tags"
                            :tags="{{$article ? $article->tags : '[]'}}"
                            :all-tags="{{$tags}}"

                        />
                        <span class="input-container__message"></span>
                    </div>
                </div>
                <div class="input-container">
                    <label class="input-container__label">Привязка</label>
                    <div class="input-container__inner">
                        <article-bindings-editor :bindings="{{$article ? $article->bindings : '[]'}}"/>
                        <span class="input-container__message"></span>
                    </div>
                </div>

                @if (isset($networks) && $networks)
                    <crossposts-editor :crossposts="{{$crossposts}}" :article="{{$article}}" :networks="{{$networks}}"></crossposts-editor>
                @endif


                <button class="button">Сохранить</button>

            </div>


        </div>
        @csrf
    </form>
@endsection
