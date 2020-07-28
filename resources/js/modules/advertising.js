window.execOnMounted.push(() => {
    let brandsSelect = $('select[name="commercials_select_brand"]');
    let placeholder = $(this).data('placeholder');
    let searchParams = new URLSearchParams(window.location.search);
    searchParams.delete('brand');
    $(brandsSelect).select2({
        placeholder,
        ajax: {
            method: 'POST',
            url: $(this).data('is-radio') ? '/radio/brands?' + searchParams.toString() : '/video/brands?' + searchParams.toString(),
            dataType: 'json',
            processResults: function (data) {
                let brands = data.data.brands.map(brand => {
                    return {
                        id: brand,
                        text: brand,
                    }
                });
                brands.unshift({
                    id: '',
                    text: 'Все'
                });
                return {
                    results: brands
                };
            },
        }
    });
    $(brandsSelect).on('change', function() {
        let search = window.location.search;
        let brand = $(this).val();

        let searchParams = new URLSearchParams(window.location.search);
        if (brand && brand.length > 0) {
            searchParams.set('brand', brand);
        } else {
            searchParams.delete('brand')
        }
        let url = window.location.pathname + '?' + searchParams.toString();
        $.pjax({url, container: '#pjax-container'});
    })
});
