window.execOnMounted.push(() => {
    if ($('.share-buttons').length > 0) {
        let share = Ya.share2($('.share-buttons')[0], {
            content: {}
        });
    }
});
