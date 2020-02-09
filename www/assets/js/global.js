function URL(url) {
    return BASE_PATH + '/' + url;
}

function notify(message, status, timeout, position) {
    if (!message) {
        message = '';
    }

    if (!status || !(status == 'success' || status == 'warning' || status == 'danger')) {
        status = 'info';
    }

    if (!timeout || timeout < 0) {
        timeout = 5000;
    }

    if (!position) {
        position = 'top-center';
    }

    UIkit.notify({
        message: message,
        status: status,
        timeout: timeout,
        pos: position
    });
}

function modalConfirm(text, ok, cancel, okLabel, cancelLabel) {
    if (!ok) {
        ok = function() {};
    }

    if (!cancel) {
        cancel = function() {};
    }

    if (!okLabel) {
        okLabel = 'Ano';
    }

    if (!cancelLabel) {
        cancelLabel = 'Ne';
    }

    UIkit.modal.confirm(text, ok, cancel, {
        labels: {
            'Ok': okLabel,
            'Cancel': cancelLabel
        }
    });
}

function createSpinner(size) {
    if (size == 'small' || size == 'medium' || size == 'large') {
        size = ' uk-icon-' + size;
    }
    else {
        size = '';
    }

    return $('<i>', {
        class: 'uk-icon-spinner uk-icon-spin spinner' + size
    });
}

function isMobile() {
    return window.innerWidth <= 767;
}

function transformEmptyLinks(parent) {
    var selector = (!parent) ? 'a' : (parent + ' ' + 'a');

    $(selector).each(function() {
        var $this = $(this);
        var href = $this.attr('href');

        if ((typeof href === 'undefined' || href == '') && $this.html() != '') {
            $this.attr('href', $this.html());
        }
    });
}