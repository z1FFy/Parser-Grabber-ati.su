
var emailRegex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/;

// Get company id and contact name
var system = require('system');
if (system.args.length < 3) {
    console.log('MESSAGE: Please specify company id and contact name');
    console.log('ERROR');
    phantom.exit();
}
var companyId = system.args[1].trim();
var contactName = system.args[2].trim();


// Check auth
var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36';

page.open('http://ati.su/', function (status) {

    // Check status
    if (status === 'fail') {
        console.log('MESSAGE: Can`t load index page');
        console.log('ERROR');
        phantom.exit();
    }

    if (!isAuthorized(page.content)) {
        console.log('MESSAGE: Not authorized');
        console.log('ERROR');
        phantom.exit();
    }

    // Load company info page
    var url = 'http://ati.su/Tables/Info.aspx?ID=' + companyId + '&print=0&isdeleted=false&WindowMode=Popup';
    console.log('MESSAGE: Loading page: ' + url);

    page.open(url, function (status) {

        // Check status
        if (status === 'fail') {
            console.log('MESSAGE: Can`t load company info page');
            console.log('ERROR');
            phantom.exit();
        }

        // Try to find contact id or email (if already opened)
        console.log('MESSAGE: Trying to find contact id or email');
        var data = page.evaluate(function (contactName, emailRegex){

            var result = {contact_id: '', email: ''};

            // Get spans with user names
            var userNameSpans = $('span[id*=lblUserName]');
            if (userNameSpans.length == 0) {
                return result;
            }

            // For each span
            userNameSpans.each(function (index, span) {

                var spanObj = $(span);

                // Get user name
                var userName = spanObj.text().trim();

                // If user name not equals contact name, then exit
                if (userName !== contactName) {
                    return;
                }

                // Get email anchor
                var emailAnchor = spanObj.parent().parent().parent().find('tr[id*=rowContactEmail] td:nth-child(2) a');
                if (emailAnchor.length == 0) {
                    return;
                }

                // If email anchor consists email
                var eaContent = emailAnchor.text().trim();
                if (emailRegex.test(eaContent)) {

                    // Put email into result
                    result.email = eaContent;
                } else {

                    // Else, put contact id into result
                    result.contact_id = emailAnchor.attr('cid');
                }

            });

            // Return result
            return result;

        }, contactName, emailRegex);

        console.log('MESSAGE: Data found, ' + JSON.stringify(data));

        // If have contact id, then get email
        if (data.email.length == 0 && data.contact_id.length > 0) {

            console.log('MESSAGE: Trying to get email');

            var url = 'http://ati.su/WebServices/Mail/EmailService.asmx/TryGetEmailAddress';
            var postBody = '{contactId:' + data.contact_id + ',firmId:' + companyId + '}';

            console.log('MESSAGE: Loading page: ' + url);
            console.log('MESSAGE: POST body, ' + JSON.stringify(postBody));

            page.customHeaders = {
                'Content-Type': 'application/json; charset=UTF-8'
            };
            page.open(url, 'POST', postBody, function (status) {
                var response = JSON.parse(stripTags(page.content));
                response = JSON.parse(response.d);

                console.log('EMAIL: ' + response.Message);
                phantom.exit();

            });

        } else if (data.email.length > 0) {

            console.log('EMAIL: ' + data.email);
            phantom.exit();

        } else {
            console.log('MESSAGE: Can`t get email');
            console.log('ERROR');
            phantom.exit();
        }

    });

});

function isAuthorized(content) {
    return content.indexOf('extendedLoginUser') > -1;
}

function stripTags(str) {
    return str.replace(/<\/?[^>]+>/gi, '');
}

