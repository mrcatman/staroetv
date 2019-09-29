

let bb = function() {
    return {
        init(id) {
            this.id = id;
            this.opens = [];
            this.isSel = false;
            this.bbtags = [];
            this.myAgent = navigator.userAgent.toLowerCase();
            this.myVersion = parseInt(navigator.appVersion);
            this.selection = "";
            this.is_ie = ((this.myAgent.indexOf("msie") !== -1) && (this.myAgent.indexOf("opera") === -1));
            this.is_nav = ((this.myAgent.indexOf('mozilla') !== -1) && (this.myAgent.indexOf('spoofer') === -1) && (this.myAgent.indexOf('compatible') === -1) && (this.myAgent.indexOf('opera') === -1) && (this.myAgent.indexOf('webtv') === -1) && (this.myAgent.indexOf('hotjava') === -1));
            this.is_win = ((this.myAgent.indexOf("win") !== -1) || (this.myAgent.indexOf("16bit") !== -1));
            this.is_mac = (this.myAgent.indexOf("mac") !== -1);
        },
        cstat(fi) {
            if (!fi) {
                fi = '';
            }
            let c = this.stacksize(this.bbtags);

            if ((c < 1) || (c == null)) {
                c = 0;
            }

            if (!this.bbtags[0]) {
                c = 0;
            }
            eval('document.getElementById("tagcount' + fi + '").value=' + c);
        },
        stacksize(thearray) {
            for (let i = 0; i < thearray.length; i++) {
                if ((thearray[i] === "") || (thearray[i] == null) || (thearray === undefined)) {
                    return i;
                }
            }
            return thearray.length;
        },
        pushstack(thearray, newval, fi) {
            let arraysize = this.stacksize(thearray);
            thearray[arraysize] = newval;
        },
        popstack(thearray) {
            let arraysize = this.stacksize(thearray);
            let theval = thearray[arraysize - 1];
            delete thearray[arraysize - 1];
            return theval;
        },
        closeall(fi) {
            if (!fi) {
                fi = '';
            }
            let wh = this.id;
            if (this.bbtags[0]) {
                try {
                    while (this.bbtags[0]) {
                        let tagRemove = this.popstack(this.bbtags)
                        document.getElementById(wh).value += "[/" + tagRemove + "]";
                        if ((tagRemove !== 'font') && (tagRemove !== 'size') && (tagRemove !== 'color')) {
                            if (tagRemove === 'code') {
                                eval("document.getElementById('codes" + fi + "').value = ' " + tagRemove + " '");
                            } else {
                                eval("document.getElementById('" + tagRemove + fi + "').value = ' " + tagRemove + " '");
                            }
                            this.opens[tagRemove + fi] = 0;
                        }
                    }
                } catch (e) {
                }
            }

            eval('document.getElementById("tagcount' + fi + '").value=0');
            this.bbtags = [];
            document.getElementById(wh).focus();
        },
        emoticon(theSmilie) {
            this.doInsert(" " + theSmilie + " ", "", false);
        },
        add_code(NewCode) {
            let wh = this.id;
            document.getElementById(wh).value += NewCode;
            document.getElementById(wh).focus();
        },
        alterfont(theval, thetag, fi) {
            if (!fi) {
                fi = '';
            }
            if (theval === 0)
                return;

            if (this.doInsert("[" + thetag + "=" + theval + "]", "[/" + thetag + "]", true))
                this.pushstack(this.bbtags, thetag);

            this.cstat(fi);
        },
        _alterfont(theval, thetag) {
            if (theval === 0) {
                return;
            }
            if (thetag === 'size') {
                this.doInsert('<span style="font-size:' + theval + 'pt">', '</span>', 3);
            } else if (thetag === 'color') {
                this.doInsert('<span style="color:' + theval + '">', "</span>", 3);
            } else if (thetag === 'font') {
                this.doInsert('<span style="font-family:\'' + theval + '\'">', "</span>", 3);
            } else if (thetag === 'pos') {
                this.doInsert('<div align="' + theval + '">', "</div>", 3);
            }
        },
        _simpletag(thetag) {
            this.simpletag(thetag, '', '', '', 1);
        },
        simpletag(thetag, fid, chtxt, fi, tp) {
            if (!fi) {
                fi = '';
            }
            let tagOpen;
            tagOpen = this.opens[thetag + fid];
            let bracket1 = '[';
            let bracket2 = ']';
            let doClose = true;
            if (tp) {
                bracket1 = '<';
                bracket2 = '>';
                doClose = 3;
            }
            if (!tagOpen) {
                if (this.doInsert(bracket1 + thetag + bracket2, bracket1 + "/" + thetag + bracket2, doClose) && !tp) {
                    this.opens[thetag + fid] = 1;
                    if (fid) {
                        document.getElementById(fid).value = chtxt + '*';
                    } else {
                        if (thetag === 'code') {
                            eval("document.getElementById('codes" + fi + "').value += '*'");
                        } else {
                            eval("document.getElementById('" + thetag + fi + "').value += '*'");
                        }
                    }
                    this.pushstack(this.bbtags, thetag, fi);
                    this.cstat(fi);
                }
            } else {
                let lastindex = 0;
                for (let i = 0; i < this.bbtags.length; i++) {
                    if (this.bbtags[i] === thetag) {
                        lastindex = i;
                    }
                }

                while (this.bbtags[lastindex]) {
                    let tagRemove = this.popstack(this.bbtags);
                    this.doInsert("[/" + tagRemove + "]", "", false)
                    if ((tagRemove !== 'font') && (tagRemove !== 'size') && (tagRemove !== 'color')) {
                        if (fid) {
                            document.getElementById(fid).value = chtxt;
                        } else {
                            if (thetag === 'code') {
                                eval("document.getElementById('codes" + fi + "').value = '" + tagRemove + "'");
                            } else {
                                eval("document.getElementById('" + tagRemove + fi + "').value = '" + tagRemove + "'");
                            }
                        }
                        this.opens[tagRemove + fid] = 0;
                    }
                }

                this.cstat(fi);
            }
        },
        tag_list() {
            let listvalue = "init";
            let thelist = "";
            while ((listvalue !== "") && (listvalue != null)) {
                listvalue = prompt('List item', "");
                if ((listvalue !== "") && (listvalue != null)) {
                    thelist = thelist + "[*]" + listvalue + "\n";
                }
            }
            if (thelist !== "") {
                this.doInsert("[list]\n" + thelist + "[/list]\n", "", false);
            }
        },
        _tag_list() {
            let listvalue = "init";
            let thelist = "";
            while ((listvalue !== "") && (listvalue != null)) {
                listvalue = prompt('List item', "");
                if ((listvalue !== "") && (listvalue != null)) {
                    thelist = thelist + "<li>" + listvalue + "\n";
                }
            }
            if (thelist !== "") {
                this.doInsert("<ul>\n" + thelist + "</ul>\n", "", false);
            }
        },
        _tag_url() {
            let enterURL = prompt('Site address', "http://");
            let enterTITLE = this.isSelected();
            if (enterTITLE.length === 0) {
                enterTITLE = prompt('Site name', "My WebPage");
            }
            if (!enterURL || enterURL === 'http://') {
                return;
            } else if (!enterTITLE) {
                return;
            }

            this.doInsert('<a href="' + enterURL + '" target="_blank">' + enterTITLE + '</a>', "", false);
        },
        _tag_image() {
            let enterURL = prompt('Image URL', "http://");
            if (!enterURL || enterURL === 'http://') {
                return;
            }
            this.doInsert('<img border="0" align="absmiddle" src="' + enterURL + '">', "", false);
        },
        _tag_email() {
            let emailAddress = prompt('E-mail address', "");

            if (!emailAddress) {
                return;
            }
            let enterTITLE = this.isSelected();
            if (enterTITLE.length > 0) {
                this.doInsert('<a href="mailto:' + emailAddress + '">' + enterTITLE + '</a>', "", false);
            } else {
                this.doInsert('<a href="mailto:' + emailAddress + '">' + emailAddress + '</a>', "", false);
            }
        },
        tag_url() {
            let enterURL = prompt('Site address', "http://");
            let enterTITLE = this.isSelected();
            if (enterTITLE.length === 0) {
                enterTITLE = prompt('Site name', "My WebPage");
            }
            if (!enterURL || enterURL === 'http://') {
                return;
            } else if (!enterTITLE) {
                return;
            }
            this.doInsert("[url=" + enterURL + "]" + enterTITLE + "[/url]", "", false,);
        },
        tag_url2() {
            let enterURL = prompt('Site address', "http://");
            let enterTITLE = this.isSelected();
            if (enterTITLE.length === 0) {
                enterTITLE = prompt('Site name', "My WebPage");
            }
            if (!enterURL || enterURL == 'http://') {
                return;
            } else if (!enterTITLE) {
                return;
            }
            let tag = 'url';
            this.doInsert("[" + tag + "=" + enterURL + "]" + enterTITLE + "[/" + tag + "]", "", false);
        },
        tag_image() {
            let enterURL = prompt('Image URL', "http://");

            if (!enterURL || enterURL === 'http://' || enterURL.length < 6) {
                return;
            }

            this.doInsert("[img]" + enterURL + "[/img]", "", false);
        },
        tag_email() {
            let emailAddress = prompt('E-mail address', "");

            if (!emailAddress) {
                return;
            }
            let enterTITLE = this.isSelected();
            if (enterTITLE.length > 0) {
                this.doInsert("[email=" + emailAddress + "]" + enterTITLE + "[/email]", "", false);
            } else {
                this.doInsert("[email]" + emailAddress + "[/email]", "", false);
            }
        },
        doInsert(ibTag, ibClsTag, isSingle) {
            let wh = this.id;
            let isClose = false;
            let obj_ta = document.getElementById(wh);
            let txtStart = obj_ta.selectionStart;
            let txtEnd = obj_ta.selectionEnd;
            if ((this.myVersion >= 4) && this.is_ie && this.is_win) {
                if (obj_ta.isTextEdit) {
                    obj_ta.focus();
                    let sel = document.selection;
                    let rng = sel.createRange();
                    if ((sel.type === "Text" || sel.type === "None") && rng != null) {
                        if (ibClsTag !== "" && rng.text.length > 0)
                            ibTag += rng.text + ibClsTag;
                        else if (isSingle)
                            isClose = true;
                        rng.text = ibTag;
                    }
                } else {
                    if (isSingle)
                        isClose = true;
                    obj_ta.value += ibTag;
                }
            } else try {
                let scr = obj_ta.scrollTop;
                if (!(txtStart >= 0)) throw 1;
                if (ibClsTag !== "" && obj_ta.value.substring(txtStart, txtEnd).length > 0) {
                    obj_ta.value = obj_ta.value.substring(0, txtStart) + ibTag + obj_ta.value.substring(txtStart, txtEnd) + ibClsTag + obj_ta.value.substring(txtEnd, obj_ta.value.length);
                } else {
                    if (isSingle) isClose = true;
                    if (isSel) {
                        obj_ta.value = obj_ta.value.substring(0, txtStart) + ibTag + obj_ta.value.substring(txtEnd, obj_ta.value.length);
                    } else {
                        obj_ta.value = obj_ta.value.substring(0, txtStart) + ibTag + (isSingle === 3 ? ibClsTag : '') + obj_ta.value.substring(txtStart, obj_ta.value.length);
                    }
                }
                obj_ta.scrollTop = scr;
            } catch (e) {
                if (isSingle) {
                    isClose = true;
                }
                obj_ta.value += ibTag;
            }
            try {
                if (txtStart === undefined) {
                    obj_ta.focus();
                    let range = document.selection.createRange();
                    range.select();
                } else if (txtStart !== txtEnd) {
                    obj_ta.selectionStart = txtStart;
                    obj_ta.selectionEnd = txtEnd + ibTag.length + ibClsTag.length;
                } else {
                    let cursorPosition = txtStart + ibTag.length;
                    obj_ta.selectionStart = cursorPosition;
                    obj_ta.selectionEnd = cursorPosition;
                }
                ;
            } finally {
            }
            obj_ta.focus();
            return isClose;
        },
        isSelected() {
            let wh = this.id;
            let obj_ta = document.getElementById(wh);
            if ((this.myVersion >= 4) && this.is_ie && this.is_win) {
                if (obj_ta.isTextEdit) {
                    obj_ta.focus();
                    let sel = document.selection;
                    let rng = sel.createRange();
                    if ((sel.type === "Text" || sel.type === "None") && rng != null) {
                        if (rng.text.length > 0) {
                            this.isSel = true;
                            return rng.text;
                        }
                    }
                }
                return '';
            }
            try {
                let txtStart = obj_ta.selectionStart;
                if (!(txtStart >= 0)) throw 1;
                let txtEnd = obj_ta.selectionEnd;
                if (obj_ta.value.substring(txtStart, txtEnd).length > 0) {
                    this.isSel = true;
                    return obj_ta.value.substring(txtStart, txtEnd);
                }
            } catch (e) {

            }
            return '';
        },
        getSelection() {
            if (window.getSelection) {
                this.selection = window.getSelection().toString();
            } else if (document.getSelection) {
                this.selection = document.getSelection();
            } else {
                this.selection = document.selection.createRange().text;
            }
        },
        insertQuote(id, user) {
            user = user.replace(/\[/g, '\\[').replace(/\]/g, '\\]');
            if (this.selection && this.selection !== "") {
                let text = "[quote="+user+";"+(id)+"]"+this.selection+"[/quote]\n";
                let input = document.getElementById(this.id);
                input.value += text;
                input.focus();
            }
        }
    }
};
window.bbCodes = bb;
export default bb;