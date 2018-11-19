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
						$('#spinner').show();
						var data = {};
						data.email = '<?php echo($_GET['email']); ?>';
						data.time = '<?php echo($_GET['time']); ?>';
						data.hash = '<?php echo($_GET['hash']); ?>';
						data.password = $('#newPassword').val();
						data.confirmpassword = $('#newPasswordConfirm').val();
						$.post('api.php?action=confirmresetpassword', {'data': data}, function(data){
							$('#spinner').hide();
							var result = JSON.parse(data);
							if(result.type == 'success'){
									$('#reset').hide();
									$('#message').html('Password updated.');
									$('#message').show();
								} else {
									alert('Error saving new password.');
								}
						});
					}
				</script>
				<div id='spinner' style="display:none;"><img src='../images/Ajax-loader.gif'></div>
				<div id='message'></div>
				<div id='reset'>
				<div>
				New Password <input type='password' id='newPassword'>
				</div>
				<div>
				Confirm New Password <input type='password' id='newPasswordConfirm'>
				</div>
				<div>
				<input type='button' value='Save New Password' onclick='savePassword()'>
				</div>
				</div>
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
