<?php

	if ( $_SERVER["REQUEST_METHOD"] === "POST" )
	{
		$username = filter_var( $_POST["username"], FILTER_SANITIZE_EMAIL );
		$password = $_POST["password"];
		$remember = $_POST["rememberMe"] ? true : false;

		
		$login = $auth->login($username, $password, $remember);

		if( $login['error'] ) {
			// Something went wrong, display error message
			flash()->error($login['message']);
		} else {
			// Logged in successfully, set cookie, display success message
			$login = setcookie(
				$auth_config->cookie_name,
				$login['hash'],
				$login['expire'],
				$auth_config->cookie_path,
				$auth_config->cookie_domain,
				$auth_config->cookie_secure,
				$auth_config->cookie_http
			);

			flash()->success("welcome");
			redirect("/");
		}
	}
	include_once "_partials/header.php";

?>

	<form method="post" action="" class="box box-auth">
		<h2 class="box-auth-heading">
			Login
		</h2>

		<input type="email" value="" class="form-control" name="username" placeholder="Email Address" required autofocus>
		<input type="password" class="form-control" name="password" placeholder="Password" required>
		<label class="checkbox">
			<input type="checkbox" value="remember-me" id="rememberMe" name="rememberMe" checked>Remember me
		</label>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>

		<p class="alt-action text-center">
			or <a href="<?= BASE_URL ?>/register">register</a>
		</p>
	</form>

<?php include_once "_partials/footer.php" ?>