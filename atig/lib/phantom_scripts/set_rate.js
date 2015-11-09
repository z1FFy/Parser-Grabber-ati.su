// Get item id, firm id and rate value
var system = require('system');
if (system.args.length < 4) {
    console.log('MESSAGE: Please specify item id and rate value');
    console.log('ERROR');
    phantom.exit();
}

var itemId = system.args[1].trim();
var firmId = system.args[2].trim();
var rateValue = system.args[3].trim();

// Load index page
var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36';
page.open('http://ati.su/', function (status) {

    // Check status
    if (status === 'fail') {
        console.log('MESSAGE: Can`t load index page');
        console.log('ERROR');
        phantom.exit();
    }

    // Check authorization
    if (!isAuthorized(page.content)) {
        console.log('MESSAGE: Not authorized');
        console.log('ERROR');
        phantom.exit();
    }

    // Load set rate page
    var url = 'http://ati.su/Tables/Responses.aspx?EntityType=Load&ID=' + itemId
        + '&FirmID=' + firmId
        + '&RespType=TruePrice&PriceBtnId=item_rRate_2_hlkTruePrice_2&PriceElemId=item_rRate_2_lblOwnerPrice_2&ComplaintBtnId=item_cRate_2_hlkComplaint_2&OfferBtnId=item_rRate_2_hlkCounteroffer_2&WindowMode=Popup';
    console.log('MESSAGE: Loading page ' + url);

    page.open(url, function (status) {

        // Check status
        if (status === 'fail') {
            console.log('MESSAGE: Can`t load set rate page');
            console.log('ERROR');
            phantom.exit();
        }

        // Set rate and submit form
        console.log('MESSAGE: Rate value, ' + rateValue);
        page.evaluate(function (rateValue) {

            var input = $('input#txtTruePrice');
            input.val(rateValue);

            $('input#btnSubmit').click();

        }, rateValue);
        console.log('MESSAGE: Form submitted');
        page.render('page.png');

        // Wait some time
        setTimeout(function() {

            // Exit
            console.log('SUCCESS');
            phantom.exit();

        }, 3000);

    });

});


function isAuthorized(content) {
    return content.indexOf('main_extLogin_ucLoginView_pnlLoginView') > -1;
}