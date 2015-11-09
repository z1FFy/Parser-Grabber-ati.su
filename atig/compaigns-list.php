<html>
<head>
	<title>ati.su Compaigns</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" integrity="sha384-aUGj/X2zp5rLCbBxumKTCw2Z50WgIr1vs/PFN4praOTvYXWlVyh2UtNUU0KAUhAX" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
	<style> body {  padding: 5%;padding-top: 2%;  } </style>
</head>
<body>
<header>
	<h1>ati.su Compaign List</h1>
</header>
<?php
$file = file_get_contents('data/idlist.json');
$list = json_decode($file,true);
$pageCount = count($list);

if (isset($_GET['page']) && $_GET['page']>=1 && $_GET['page']<=$pageCount) {
	$pageNumber = $_GET['page'];
} else {
	$pageNumber = 1;
}
$list = $list[$pageNumber];
?>
<ul class="pagination pagination-lg">
	<li><a href="?page=<?=$pageNumber-1 ?>" aria-label="Previous"><span aria-hidden="true">«</span></a></li>
	<? for ($i=1;$i<=$pageCount;$i++) { ?>
		<li>
			<a href="?page=<?=$i?>">
				<?=$i?>
			</a>
		</li> <?} ?>
	<li><a href="?page=<?=$pageNumber+1 ?>" aria-label="Next"><span aria-hidden="true">»</span></a></li>
</ul>
<table class="table table-hover table-bordered">
 <tbody>
	<tr><td>Компания</td>
 <td>Город</td>
 <td>Деятельность</td>
 <td>ID</td></tr>
 <?
	foreach ($list as $key => $item) {
		echo '<tr><td><a target="_blank" href="data/cards/'.$key.'.html"><button type="button" class="btn btn-primary">Открыть</button></a>  '. $item['name'] .'</td>';
		echo '<td>'. $item['city'] .'</td>';
		echo '<td>'. $item['profile'] .'</td>';
		echo '<td>'. $key .'</td></tr>';
	}
 ?>
</tbody>
</table>

</body>
</html>