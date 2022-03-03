<?php

	function logged_in()
	{
		global $auth;

		return $auth->isLogged();
	}
	/**
	 * Do Logout
	 *
	 * Log the user out
	 *
	 * @return bool
	 */
	function do_logout()
	{
		global $auth, $auth_config;

		if ( logged_in() ){
			$logout = $auth->logout( $_COOKIE[$auth_config->cookie_name] );
		}
		return $logout;
	}

	function get_user($user_id = 0)
	{
		global $auth, $auth_config;

		if ( logged_in() ){
			$user_id = $auth->getSessionUID($_COOKIE[$auth_config->cookie_name]);
		}
		
		return (object) $auth->getUser($user_id);
	}
	
	function can_edit( $post )
	{
		// must be logged in
		// pokud nejsem přihlášen - nemůžů editovat
		if ( !logged_in() ){
			return false;
		}
		
		// potřebuji zjistit id usera, který napsal tento post
		if ( is_object( $post ) ){
			// víme, že $post může být array nebo objekt (podle toho či je nebo není naformátovaný)
			$post_user_id = (int) $post->user_id;
		}
		else{
			$post_user_id = (int) $post["user_id"];
		}
		// zjistíme jaký user je přhlášený
		$user = get_user();
		// porovnáme usera, co napsal článek s přihlášeným userem - pokud ano, vrátí true (můžu editovat)
		return $post_user_id === $user->uid;
	}