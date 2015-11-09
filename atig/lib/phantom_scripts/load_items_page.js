/**
 * Load items page
 */

// Prepare GET variables
var system = require('system');
var variables = [];
if (system.args.length > 1) {

    system.args.forEach(function(entry, i) {

        if (i < 1) {
            return;
        }

        if (entry.indexOf('=') == -1) {
            return;
        }

        variables.push(utf8_decode(entry));
    });

}
var queryString = variables.join('&');

// Load page
var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36';

console.log('MESSAGE: Loading page, variables: ' + queryString);
page.open('http://ati.su/Tables/Default.aspx?' + queryString, function(status) {

    // Check status
    if (status === 'fail') {
        console.log('ERROR');
        phantom.exit();
    }

    console.log('MESSAGE: Page loaded');

    // Check authorization
    if (!isAuthorized(page.content)) {
        console.log('MESSAGE: Not authorized');
        console.log('ERROR');
        phantom.exit();
    }

    // Process captcha
    var captchaId = page.evaluate(function() {

        if (!ValidateCaptcha()) {

            if (window.CaptchaObjctlCaptcha && typeof(CaptchaObjctlCaptcha.Process) == "function") {
                var captcha = CaptchaObjctlCaptcha.Process();
                return captcha.GUID;
            }

            return '';
        }

        return true;
    });

    if (captchaId !== true) {

        console.log('MESSAGE: Can`t validate captcha');
        if (captchaId.length > 0) {

            page.clipRect = {
                top: 550,
                left: 15,
                width: 90,
                height: 32
            };
            var captchaImageData = page.renderBase64('PNG');

            var captchaData = {
                'captcha_id': captchaId,
                'captcha_image_data_base64': captchaImageData
            };

            console.log('CAPTCHA_DATA: ' + JSON.stringify(captchaData));
        }
        console.log('ERROR');

        phantom.exit();
    }

    console.log('CONTENT:' + page.content);
    phantom.exit();
});

function isAuthorized(content) {
    return content.indexOf('main_extLogin_ucLoginView_pnlLoginView') > -1;
}

function utf8_decode (aa) {
    var bb = '', c = 0;
    for (var i = 0; i < aa.length; i++) {
        c = aa.charCodeAt(i);
        if (c > 127) {
            if (c > 1024) {
                if (c == 1025) {
                    c = 1016;
                } else if (c == 1105) {
                    c = 1032;
                }
                bb += String.fromCharCode(c - 848);
            }
        } else {
            bb += aa.charAt(i);
        }
    }
    return bb;
}