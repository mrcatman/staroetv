<form class="form bb-editor" action="/comments/add" method="POST" data-reset="1" data-auto-close-modal="1">
    <div class="bb-editor__inner">
        <input type="hidden" name="material_type" value="{{$material_type}}"/>
        <input type="hidden" name="material_id" value="{{$material_id}}"/>
        <input type="hidden" name="parent_id" value=""/>
        <input type="hidden" name="id" value=""/>
        <span class="bb-editor__input-container">
            <input type="button" title="Bold" value="b" onclick="bb.simpletag('b','','','')" class="codeButtons" id="b" style="width:20px;font-weight:bold">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="Italic" value="i" onclick="bb.simpletag('i','','','')" class="codeButtons" id="i" style="width:20px;font-style:italic">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="Underline" value="u" onclick="bb.simpletag('u','','','')" class="codeButtons" id="u" style="width:20px;text-decoration:underline">
        </span>
        <span class="bb-editor__input-container">
            <select id="fsize" class="codeButtons" onchange="bb.alterfont(this.options[this.selectedIndex].value,'size','');this.selectedIndex=0;">
                <option value="0">SIZE</option>
                <option value="6">6 pt</option>
                <option value="7">7 pt</option>
                <option value="8">8 pt</option>
                <option value="9">9 pt</option>
                <option value="10">10 pt</option>
                <option value="11">11 pt</option>
                <option value="12">12 pt</option>
                <option value="13">13 pt</option>
                <option value="14">14 pt</option>
                <option value="15">15 pt</option>
                <option value="16">16 pt</option>
                <option value="17">17 pt</option>
                <option value="18">18 pt</option>
                <option value="19">19 pt</option>
                <option value="20">20 pt</option>
                <option value="21">21 pt</option>
                <option value="22">22 pt</option>
            </select>
        </span>
        <span class="bb-editor__input-container">
            <select id="fcolor" class="codeButtons" onchange="bb.alterfont(this.options[this.selectedIndex].value, 'color','');this.selectedIndex=0;">
                <option value="0">COLOR</option>
                <option value="blue" style="color:Blue">Blue</option>
                <option value="red" style="color:Red">Red</option>
                <option value="purple" style="color:Purple">Purple</option>
                <option value="orange" style="color:Orange">Orange</option>
                <option value="yellow" style="color:Yellow">Yellow</option>
                <option value="gray" style="color:Gray">Gray</option>
                <option value="green" style="color:Green">Green</option>
            </select>
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="URL" value="http://" onclick="bb.tag_url()" class="codeButtons" style="direction:ltr;width:45px;" id="url">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="E-mail" value="@" onclick="bb.tag_email()" class="codeButtons" style="width:30px;" id="email">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="Image" value="img" onclick="bb.tag_image()" class="codeButtons" style="width:35px;" id="img">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="List" value="list" onclick="bb.tag_list()" class="codeButtons" id="list" style="width:30px;">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="Left" style="width:20px;text-align:left;" value="···" onclick="bb.simpletag('l','cdl','···','message')" class="codeButtons" id="cdl">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="Center" style="width:20px;text-align:center;" value="···" onclick="bb.simpletag('c','cdc','···','message')" class="codeButtons" id="cdc">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" title="Right" style="width:20px;text-align:right;" value="···" onclick="bb.simpletag('r','cdr','···','message')" class="codeButtons" id="cdr">
        </span>
        <!--
        <span class="bb-editor__input-container">
            <input type="button" title="All codes" style="width:60px;" value="All codes" onclick="window.open('/index/17', 'bbcodes', 'scrollbars=1, width=550, height=450, left=0, top=0');" class="codeButtons">
        </span>
        -->
        <span class="bb-editor__input-container">
            <input style="font-weight:bold;width:20px" type="button" value="/" class="codeButtons codeCloseAll" title="Close all opened codes" onclick="bb.closeall('');">
        </span>
        <span class="bb-editor__input-container">
            <input type="button" value="Смайлы" class="codeButtons bb-editor__smiles__all">
        </span>

        <input type="hidden" id="tagcount" value="">
        <div class="bb-editor__text-container">
            <textarea class="bb-editor__text" rows="1" placeholder="Введите ваш комментарий..." name="message" id="message" ></textarea>
            <!--
            <div class="bb-editor__smiles">
                <div class="bb-editor__smiles__list">
                    @foreach (\App\Smile::where(['show_in_panel' => true])->get() as $smile)
                        <a class="bb-editor__smile" onclick="bb.emoticon('{{$smile->text}}','message');return false;">
                            <img class="bb-editor__smile__picture" src="{{$smile->picture->url}}"/>
                        </a>
                    @endforeach
                </div>
                <a class="bb-editor__smiles__all">Все смайлы</a>
            </div>
            -->
        </div>
        <div class="bb-editor__submit">
            <input class="button button--light bb-editor__submit__button" type="submit" name="submit" value="Отправить">
            <div class="response response--light"></div>
        </div>
    </div>
</form>
<div class="bb-editor__all-smiles" id="all_smiles" style="display:none" data-title="Все смайлы">
    @foreach (\App\Smile::all() as $smile)
        <a class="bb-editor__smile bb-editor__smile--with-text" onclick="bb.emoticon('{{$smile->text}}','message');return false;">
            <img class="bb-editor__smile__picture" src="{{$smile->picture->url}}"/>
            <div class="bb-editor__smile__text">{{$smile->text}}</div>
        </a>
    @endforeach
</div>
