<script>


	var companyId = 221243;

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

			console.log('MESSAGE: url ' + url);
			return page.content;

		});

	});

	function isAuthorized(content) {
		return content.indexOf('extendedLoginUser') > -1;
	}

	function stripTags(str) {
		return str.replace(/<\/?[^>]+>/gi, '');
	}


</script>
<?php
/**
 * Created by PhpStorm.
 * User: denis.kushenko
 * Date: 29.10.2015
 * Time: 11:55
 */