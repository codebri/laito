$(function () {

    // Sintax Highlighting
    hljs.configure({
        languages: ['bash', 'http', 'php', 'json', 'html', 'css', 'javascript']
    });
    $('pre code').each(function(i, block) {
        hljs.highlightBlock(block);
    });
});

$(function() {

    $('.panel-heading').trigger('click');

    $('.panel').on('shown.bs.collapse', function () {
        $(this).find('input').first().focus();
    });

    $(document).on('submit', 'form', function(e) {
        var form = $(this),
            ajaxTime,
            token,
            tokenInput = $('[name=token]'),
            url = form.attr('action'),
            method = form.attr('method'),
            data = form.serialize(),
            button = form.find('.btn'),
            result = form.find('.result'),
            plain = form.find('.plain'),
            status = form.find('.status'),
            icon = form.find('.status-icon');

        e.preventDefault();
        button.button('loading');
        ajaxTime = Date.now();

        $.ajax({
            type: method,
            dataType: 'json',
            url: url,
            data: data,
            success: function (response, textStatus, xhr) {
                if (response !== undefined) {
                    plain.JSONView(response);
                }
                if (xhr.status !== undefined) {
                    status.text(xhr.status);
                }
                /*
                if ((res.result.data !== undefined) && (res.result.data.token !== undefined)) {
                    token = res.result.data.token;
                    tokenInput.val(token);
                }
                */
                icon.addClass('fa-check-circle');
                button.button('reset');
                result.show();
            },
            error: function (xhr, textStatus) {
                icon.addClass('fa-times-circle');
                status.text(xhr.status);
            }
        });
    });

    $(document).on('keyup', '[data-urlParam]', function(e) {
        var field = $(this),
            val = field.val(),
            name = field.attr('name'),
            call = field.closest('[data-call]'),
            form = call.find('form'),
            urlSpan = call.find('[data-url]'),
            urlBase = urlSpan.data('base'),
            urlSplat = urlSpan.data('url'),
            updatedSplat = urlSplat.replace(':' + name, val),
            action = urlBase + updatedSplat;
        form.attr('action', action);
        urlSpan.text(updatedSplat);
    });

});