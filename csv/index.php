<!doctype html>
<?php
// Замерим скорость работы
$time_start = microtime(true);

include_once 'class.php';

SQL::connect();

$catalog = Catalog::start();

?>
<html>
<head>
<meta charset="utf-8">
<title>Каталог в CSV</title>
</head>
<style>
	body{
		background-color: #0101010a;
		margin: 0;
		font-family: Inter, -apple-system, BlinkMacSystemFont, "segoe ui", Roboto, Helvetica, Arial, sans-serif;
		font-size: 13px;
	}

	/* Блок , ссылки как кнопки */
	.button {
		display: inline-block;
		font-family: arial,sans-serif;
		font-size: 14px;
		font-weight: bold;
		color: rgb(68,68,68);
		text-decoration: none;
		user-select: none;
		padding: .2em 0.8em;
		outline: none;
		border: 1px solid rgba(0,0,0,.1);
		border-radius: 2px;
		background: rgb(245,245,245) linear-gradient(#f4f4f4, #f1f1f1);
		transition: all .218s ease 0s;
		margin: 5px;
		cursor: pointer;
	}
	.button:hover {
		color: rgb(24,24,24);
		border: 1px solid rgb(198,198,198);
		background: #f7f7f7 linear-gradient(#f7f7f7, #f1f1f1);
		box-shadow: 0 1px 2px rgba(0,0,0,.1);
	}
	.button:active {
		color: rgb(51,51,51);
		border: 1px solid rgb(204,204,204);
		background: rgb(238,238,238) linear-gradient(rgb(238,238,238), rgb(224,224,224));
		box-shadow: 0 1px 2px rgba(0,0,0,.1) inset;
	}
	.img_button{
		width: 16px;
		margin-bottom: -3px;
		cursor: pointer;
	}
	.img_icon{
		width: 16px;
		margin-bottom: -3px;
		cursor: pointer;
	}
	.td_center{
		text-align: center;
		margin: 3px;
		padding: 3px 5px;
	}
	.table_block{
		border: 1px solid #aeaeae;
		border-radius: 6px;
		border-spacing: 0;
		margin: 12px;
		padding: 8px;
		min-width: 400px;
		box-shadow: 0 0 10px rgb(0, 0, 0, 0.4);
	}
</style>
	
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script>

</script>
<body>
	<?php //print_r($catalog);?>
	<center>
		<div id="list_catalog">
			<table class=table_block>
				<tr>
					<td class=td_center>#</td>
					<td><b>Путь категории</b></td>
					<td class=td_center></td>
					<td class=td_center></td>
					<td></td>
				</tr>
				<?php
				$n=1;
				$max_item = 100;
				foreach ($catalog as $key => $row) {
				
					echo "
				<tr>
					<td class=td_center>{$n}</td>
					<td>{$row['name']} (<b>{$row['count']}</b>)</td>
					<td class=td_center>
						<a href='csv.php?catalog={$key}'><img src='img/csv.png' class=img_icon></a> 
						<a href='csv.php?catalog={$key}&only_price'><img src='img/price.png' class=img_icon></a>
					</td>
					<td class=td_center></td>
					<td ></td>
				</tr>
					";
					$n++;

					if($max_item<$row['count']){
						if(count($row['entry'])>0){
							foreach ($row['entry'] as $key2 => $row2) {
				
								echo "
				<tr>
					<td class=td_center>{$n}</td>
					<td><img src='img/down.png' class=img_icon> {$row['name']}/{$row2['name']} (<b>{$row2['count']}</b>)</td>
					<td class=td_center>
						<a href='csv.php?catalog={$key2}'><img src='img/csv.png' class=img_icon></a>
						<a href='csv.php?catalog={$key2}&only_price'><img src='img/price.png' class=img_icon></a>
					</td>
					<td class=td_center></td>
					<td ></td>
				</tr>
								";
								$n++;
								if($max_item<$row2['count']){
									if(count($row2['entry'])>0){
										foreach ($row2['entry'] as $key3 => $row3) {
							
											echo "
				<tr>
					<td class=td_center>{$n}</td>
					<td>&nbsp;&nbsp;&nbsp;<img src='img/down.png' class=img_icon> {$row['name']}/{$row2['name']}/{$row3['name']} (<b>{$row3['count']}</b>)</td>
					<td class=td_center>
						<a href='csv.php?catalog={$key3}'><img src='img/csv.png' class=img_icon></a>
						<a href='csv.php?catalog={$key3}&only_price'><img src='img/price.png' class=img_icon></a>
					</td>
					<td class=td_center></td>
					<td ></td>
				</tr>
											";
											$n++;
											if($max_item<$row3['count']){
												if(count($row3['entry'])>0){
													foreach ($row3['entry'] as $key4 => $row4) {
										
														echo "
				<tr>
					<td class=td_center>{$n}</td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='img/down.png' class=img_icon> {$row['name']}/{$row2['name']}/{$row3['name']}/{$row4['name']} (<b>{$row4['count']}</b>)</td>
					<td class=td_center>
						<a href='csv.php?catalog={$key4}'><img src='img/csv.png' class=img_icon></a>
						<a href='csv.php?catalog={$key4}&only_price'><img src='img/price.png' class=img_icon></a>
					</td>
					<td class=td_center></td>
					<td ></td>
				</tr>
														";
														$n++;
													}
												}
											}
										}
									}
								}
							}
						}
					}
					echo "
				<tr>
					<td class=td_center></td>
					<td></td>
					<td class=td_center></td>
					<td class=td_center></td>
					<td ></td>
				</tr>
					";
				}
				?>
				<tr>
					<td colspan=5 class=td_center>Всего в базе <b><span id="time_load"><?php echo Catalog::count(0);?></span></b> товаров</td>
				</tr>
			</table>
		</div>
		<div id="time_execution">
			<?php
			echo "Время выполнения скрипта ".round(microtime(true) - $time_start,4)." сек.";
			?>
		</div>
	</center>
</body>
</html>