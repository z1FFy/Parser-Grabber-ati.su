/**
 * Authorization on m-ati.su
 */

var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36';

// Load index page
console.log('MESSAGE: Loading index page');
page.open('http://ati.su/', function(status) {
    // Check status
    if (status === 'fail') {
        console.log('ERROR');
        phantom.exit();
    }

    console.log('MESSAGE: Index page loaded');

    // Check authorization
    if (isAuthorized(page.content)) {
        console.log('MESSAGE: Already authorized');
        console.log('OK');
        phantom.exit();
    }

    // Load login page
    console.log('MESSAGE: Loading login page');
    page.open('http://ati.su/Login/Login.aspx?ReturnUrl=%2fDefault.aspx', function(status) {

        // Check status
        if (status === 'fail') {
            console.log('ERROR');
            phantom.exit();
        }

        console.log('MESSAGE: Login page loaded');

        // Submit form
        page.evaluate(function() {

            var login = 'LOGIN';
            var password = 'PASS';

            $('input#ctl00_ctl00_main_PlaceHolderMain_extLogin_ucLoginFormPage_tbLogin').val(login);
            $('input#ctl00_ctl00_main_PlaceHolderMain_extLogin_ucLoginFormPage_tbPassword').val(password);
            $('input#ctl00_ctl00_main_PlaceHolderMain_extLogin_ucLoginFormPage_btnPageLogin').click();
        });

        console.log('MESSAGE: Form submitted');

        // Wait
        setTimeout(function() {

            // Check authorization
            if (isAuthorized(page.content)) {
                console.log('MESSAGE: Successfully authorized');
                console.log('OK');
            } else {
                console.log('MESSAGE: Can`t authorize');
                console.log('ERROR')
            }

            // Exit
            phantom.exit();

        }, 3000);
    });
});

function isAuthorized(content) {
    return content.indexOf('main_extLogin_ucLoginView_pnlLoginView') > -1;
}