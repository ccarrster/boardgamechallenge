
<!DOCTYPE html>
<html>
<head>
	<script src="jquery-3.3.1.min.js"></script>
</head>
<body>
	<div id='result'>
	</div>
	<?php
	if(isset($_GET['key'])){
		?>
		<script>
			var data = {};
			data.key = '<?php echo($_GET["key"]); ?>';
			$.post('api.php?action=validate', {'data': data}, function(out){
				var result = JSON.parse(out);
				if(result.type === 'success'){
					$('#result').html('Validation was successful. <a href="index.html">Sign In</a>');
				} else {
					$('#result').html('Validation error. <a href="index.html">Back to home page.</a>');
				}
			});
		</script>
		<?php
	} else {
		?>
		<script>
			$('#result').html('Validation error. <a href="index.html">Back to home page.</a>');
		</script>
		<?php
	}
	?>
</body>
</html>
