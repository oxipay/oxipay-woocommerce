///<reference path="../typings/jquery/jquery.d.ts"/>
function ezipay(q) {
    var initialised = false;
    var iframeId = 'ezipay-iframe';
    var data;
    var form = null;
    var stylesheetUrl = '/Modal/src/css/ezipay.css';
    var template = '<div class="ezi-modal-overlay"></div>' +
        '<div class="ezi-modal">' +
        '<div class="ezi-modal-content">' +
        '<div class="ezi-modal-body">' +
        '<div class="ezi-modal-splash">' +
        '<div class="ezi-modal-header">' +
        '<div class="ezi-modal-logo"></div>' +
        '</div>' +
        '<div class="splash">' +
        '<div class="splashFrame">' +
        '<div class="splashGraphic">' +
        '<svg class="circular" viewBox="25 25 50 50">' +
        '<circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>' +
        '</svg>' +
        '<h4 class="splashText">Loading...</h4>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="ezi-modal-iframe"></div>' +
        '</div>' +
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
     * Setup Ezipay Checkout
     * @param targetUrl
     * @param keyValue
     */
    function setup(targetUrl, keyValue, stylesheetURL) {
        targetUrl = targetUrl || '/';
        if (targetUrl.substr(0,4) == "http") {
            // targetUrl is fine and absolute
        } else if (targetUrl.substr(0, 1) !== "/") {
            targetUrl = window.location.pathname + targetUrl;
        }

        model.targetUrl = targetUrl;
        var baseUrl = getBaseUrl(targetUrl);
        
        model.data = keyValue;

        stylesheetUrl = baseUrl + stylesheetUrl;
        if (stylesheetURL) {
            stylesheetUrl = stylesheetURL;
        }
        setStyle(q(document).find('head'));
    }
    
    /**
     * Show the Ezipay Checkout Modal
     */
    function show() {
        try {
            model.create();
            model.form.submit();
            setTimeout(function () { return message(null); }, 5000);
        }
        catch (e) {
            console.error(e);
        }
    }
    function hide() {
        try {
            q("#" + iframeId).remove();
            q(".ezi-modal").remove();
            q(".ezi-modal-overlay").remove();
            model.form.remove();
        }
        catch (e) {
            console.error(e);
        }
    }
    function message(e) {
        try {
            q('.ezi-modal-splash').addClass('animated fadeOut');
            setTimeout(function () { return q('.ezi-modal-splash').remove(); }, 2000);
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
            q('.ezi-modal-iframe').append(iframe);
            var form_1 = q(getForm());
            var iframeBody = iframe.contents().find('body');
            iframeBody.append(form_1);
            model.form = form_1;
            iFrameResize({ log: true, checkOrigin: false, closedCallback: hide, messageCallback: message }, iframe[0]);
        }
        catch (e) {
            console.error(e);
        }
    }
    function getForm() {
        var target = window.innerWidth <= 650 ? "_top" : "_self";
        var form = "<form id=\"ezi-form\" action='" + model.targetUrl + "' method='POST' style=\"display:none;\" target=\"" + target + "\">";
        for (var key in model.data) {
            var val = model.data[key] || "";
            form += "<input type=\"hidden\" name=\"" + key + "\" value=\"" + val + "\"/>";
        }
        form += "</form>";
        return form;
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
