<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<?php
		require_once 'src/ProductAggregator.php';
		use Horoshop\ProductAggregator;

		$aggr = new ProductAggregator('data.json');

		echo '<pre>';
		print_r($aggr->find('UAH', 1, 15));
		echo '</pre>';
	 ?>
</body>
</html>
