/**
 * Ati.su get compaign data
 * Script 4 Phantom
 * D.Kuschenko
 * ziffyweb@gmail.com
 */

var system = require('system');
var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36';

url = 'http://ati.su/Reliability/ViewFirmReliabilities.aspx';
FirmData = {};
j=0;
last = 5;

page.onConsoleMessage = function (msg) { console.log(msg); };

page.onLoadFinished = function(status) {

	j++;
	FirmData[j] = page.evaluate(function () {
		var links = document.getElementsByClassName('FirmHyperLink');
		var arr = {};
		var id = '';
		var str = '';
		var start = '';
		var end = '';
		for (var i = 0; i <= links.length; i++) {
			if (links[i]) {
				str = links[i].outerHTML;
				start = str.indexOf("{'FirmID' : '");
				str = str.slice(start + 13, str.length);
				end = str.indexOf('IsDeleted');
				id = str.slice(0, end - 4);
				arr[id] = {};
				arr[id]['name'] = $(links[i]).find('#ShortenedLbl').text().replace(/^([a-zа-яё]+|\d+)$/i,"");
				arr[id]['city'] = $(links[i]).parent().parent().parent().parent().next().find('span').text();
				arr[id]['profile'] = $(links[i]).parent().parent().parent().parent().next().next().find('span').text();
			}
		}
		return arr;
	});

	if (last == j-1) {
		console.log(JSON.stringify(FirmData));
		clearInterval(interv);
		phantom.exit();
	}


};
page.open(url, function (status) {

	if (status === 'fail') {
		console.log('MESSAGE: Can`t load company info page');
		console.log('ERROR');
		phantom.exit();
	}

	//interv = setTimeout(function() {
	//	var set300items = page.evaluate(function () {
	//		$('#ctl00_ctl00_main_PlaceHolderMain_ddlFirmsPerPage option:last').attr('selected','selected');
	//		setTimeout('__doPostBack(\'ctl00$ctl00$main$PlaceHolderMain$ddlFirmsPerPage\',\'\')', 0)
	//	});
	//}, 5);

	//setTimeout(function() {
	var i=1;
	interv = setInterval(function() {
		if (j==i || i==1) {
			i++;
			nextjs = page.evaluate(function () {
				if (typeof($("a[title='Перейти на следующую страницу']")) != "undefined") {
					return $("a[title='Перейти на следующую страницу']").attr('href').slice(11); //Получить функцию перехода на след страницу
				} else {
					return false;
				}
			});
			if (nextjs != false) {
				var next = page.evaluateJavaScript('function(){return ' + nextjs + ';}');
			}
		}
	}, 1130);
	//}, 5230);

});
