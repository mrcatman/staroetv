<div class="bb-editor__all-smiles" >
    @foreach ($smiles as $smile)
        <a class="bb-editor__smile bb-editor__smile--with-text" onclick="bb.emoticon('{{$smile->text}}','message');return false;">
            <img class="bb-editor__smile__picture" src="{{$smile->picture->url}}"/>
            <div class="bb-editor__smile__text">{{$smile->text}}</div>
        </a>
    @endforeach
</div>