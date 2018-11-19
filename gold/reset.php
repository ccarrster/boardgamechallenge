<?php
require_once('config.php');
	if(isset($_GET['email']) && isset($_GET['time']) && isset($_GET['hash'])){
		$email = $_GET['email'];
		$time = $_GET['time'];
		$hash = $_GET['hash'];
		$newHash = crypt($email.$time, $tempPasswordSalt);
		if($newHash === $hash){
			?>
			<!DOCTYPE html>
			<html>
			<body>
				<script src='jquery-3.3.1.min.js'></script>
				<script>
					function savePassword(){
						var data = {};
						data.email = '<?php echo($_GET['email']); ?>';
						data.time = '<?php echo($_GET['time']); ?>';
						data.hash = '<?php echo($_GET['hash']); ?>';
						data.password = $('#newPassword').val();
						data.confirmpassword = $('#newPasswordConfirm').val();
						$.post('api.php?action=confirmresetpassword', {'data': data}, function(data){
							console.log(data);
						});
					}
				</script>
				New Password <input type='password' id='newPassword'>
				Confirm New Password <input type='password' id='newPasswordConfirm'>
				<input type='button' value='Save New Password' onclick='savePassword()'>
			</body>
			</html>
			<?php
		} else {
			?>
			<!DOCTYPE html>
			<html>
			<body>
				There was an error resetting your password. Try again.
			</body>
			</html>
			<?php
		}
	} else {
			?>
			<!DOCTYPE html>
			<html>
			<body>
				There was an error resetting your password. Try again.
			</body>
			</html>
			<?php
		}
?>
