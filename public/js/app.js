$(document).ready(function() {
    $('.modal-link').click(function() {
        var resource;
        resource = $(this).attr('data-resource');
        return $.post(resource.replace(/_/g, "/"), {
            id: $(this).attr('data-id')
        }, function(data) {
            var form, messages, submit;
            $('#modal').html(data);
            $('#' + resource).modal('show');
            form = $('#' + resource + '_form');
            submit = $('#' + resource + '_submit');
            messages = $('#' + resource + '_messages');
            submit.click(function() {
                return form.submit();
            });
            return form.submit(function() {
                $.post(form.attr('action'), form.serialize(), function(data) {
                    messages.html(data.messages);
                    if (data.status === "OK") {
                        $('#' + resource).modal('hide');
                        return window.location.reload();
                    }
                }, "json");
                return false;
            });
        });
    });

    // http://dimsemenov.com/plugins/magnific-popup/
    $('.image-popup-no-margins').magnificPopup({
        type: 'image',
        closeOnContentClick: true,
        closeBtnInside: false,
        fixedContentPos: true,
        mainClass: 'mfp-no-margins mfp-with-zoom',
        image: {
            verticalFit: true
        },
        zoom: {
            enabled: true,
            duration: 300
        }
    });

    $('.popup-gallery').magnificPopup({
        delegate: 'a',
        type: 'image',
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0,1] // Will preload 0 - before current, and 1 after the current image
        },
        image: {
            tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
            titleSrc: function(item) {
                return item.el.attr('title');
            }
        },
        zoom: {
            enabled: true,
            duration: 300
        }
    });
});