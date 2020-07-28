function replaceDom(dom) {
    dom.forEach(replacement => {
        if (replacement.prepend_to) {
            $(replacement.prepend_to).prepend(replacement.html);
        }
        if (replacement.append_to) {
            $(replacement.append_to).append(replacement.html);
        }
        if (replacement.replace) {
            $(replacement.replace).html(replacement.html);
        }
    })
}

module.exports = replaceDom;