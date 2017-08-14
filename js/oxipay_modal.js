///<reference path="../typings/jquery/jquery.d.ts"/>
function oxipay(q) {
    var iframeId = 'oxipay-iframe';
    var data;
    var form = null;
    //var stylesheetUrl = '/Modal/src/css/oxipay.css';
    // @todo fix 
    var stylesheetUrl = php_vars.plugin_url+'/css/oxipay-modal.css';
    var template = '<div class="oxi-modal-overlay"></div>' +
        '<div class="oxi-modal">' +
        '<div class="oxi-modal-content">' +
        '<div class="oxi-modal-header">' +
        '<img src="'+
        php_vars.plugin_url+
        '/images/oxipay.svg" alt="Oxipay"/>' +
        '<button id="oxi-modal-close" type="button">&times;</button>' +
        '</div>' +
        '<div class="oxi-modal-body"></div>' +
        '</div>' +
        '</div>';

        var model = {
        targetUrl: '',
        data: data,
        create: create,
        show: show,
        hide: hide,
        setup: setup,
        form: form
    };

    return model;

    /**
     * Setup Oxipay Checkout
     * @param targetUrl
     * @param keyValue
     */
    function setup(targetUrl, keyValue) {
        targetUrl = targetUrl || '/';
        if (targetUrl.substr(0,4) == "http") {
            // targetUrl is fine and absolute
        } else if (targetUrl.substr(0, 1) !== "/") {
            targetUrl = window.location.pathname + targetUrl;
        }

        model.targetUrl = targetUrl;
        var baseUrl = getBaseUrl(targetUrl);
        
        model.data = keyValue;
        if (stylesheetUrl) {
            stylesheetUrl = stylesheetUrl;
            setStyle(q(document).find('head'));
        }
    }
    /**
     * Show the Oxipay Checkout Modal
     */
    function show() {
        try {
            model.create(); 
            setTimeout(function () { return model.form.submit(); }, 100);
        }
        catch (e) {
            console.error(e);
        }
    }
    function hide() {
        try {
            q("#" + iframeId).remove();
            q(".oxi-modal").remove();
            q(".oxi-modal-overlay").remove();
            model.form.remove();
        }
        catch (e) {
            console.error(e);
        }
    }
    function create() {
        try {
            var body = q('body');
            // Create the Modal Template
            var modal = q(template);
            body.append(modal);
            // Insert iframe inside modal body
            var iframe = q("<iframe id=\"" + iframeId + "\"/>");
            q('.oxi-modal-body').append(iframe);
            var form_1 = q(getForm());
            var iframeBody = iframe.contents().find('body');
            iframeBody.addClass('iframe-body');
            setStyle(iframe.contents().find('head'));
            iframeBody.append(form_1);
            model.form = form_1;
            q('#oxi-modal-close').click(model.hide);
        }
        catch (e) {
            console.error(e);
        }
    }
    function getForm() {
        var target = window.innerWidth <= 500 ? "_top" : "_self";
        var loading = '<div class="splash">' +
            '<div class="splashFrame">' +
            '<div class="splashGraphic">' +
            '<svg class="circular" viewBox="25 25 50 50">' +
            '<circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>' +
            '</svg>' +
            '</div>' +
            '</div>' +
            '</div>';
        var form = "<form id=\"oxi-form\" action='" + model.targetUrl + "' method='POST' style=\"display:none;\" target=\"" + target + "\">";
        for (var key in model.data) {
            var val = model.data[key] || "";
            form += "<input type=\"hidden\" name=\"" + key + "\" value=\"" + val + "\"/>";
        }
        form += "</form>";
        return loading + form;
    }
    function setStyle(head) {
       head.append("<link rel=\"stylesheet\" type=\"text/css\" href=\"" + stylesheetUrl + "\">");
    }
    function getBaseUrl(url) {
        var a = document.createElement('a');
        a.href = url;
        return a.protocol + "//" + a.host;
    }
    ;
}