/**
 * Ati.su get html content company page
 * Script 4 Phantom
 * D.Kuschenko
 * ziffyweb@gmail.com
 */


var system = require('system');

var companyId = system.args[1];
// Check auth
var page = require('webpage').create();

page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36';
// Load company info page
var url = 'http://ati.su/Tables/Info.aspx?ID=' + companyId + '&print=0&isdeleted=false&WindowMode=Popup';

page.open(url, function (status) {
	// Check status

	if (status === 'fail') {
		console.log('MESSAGE: Can`t load company info page');
		console.log('ERROR');
		phantom.exit();
	}
	contactData = page.evaluate(function () {

		return $("div[itemtype='http://schema.org/Organization']").html()
	});

	console.log(contactData);
	phantom.exit();

});
