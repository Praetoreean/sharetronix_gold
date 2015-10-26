<?php	
	if(isset($_POST['oauth_version']) && $_POST['oauth_version'] != '1.0')
	{
		echo 'Not supported oauth version.';
		exit;		
	}

	if(isset($_POST['oauth_consumer_key'], $_POST['oauth_nonce'], $_POST['oauth_signature_method'], 
			$_POST['oauth_signature'], $_POST['oauth_timestamp'], $_POST['oauth_token'], $_POST['oauth_verifier']))
	{	
		require_once( $C->INCPATH.'classes/class_oauth.php' );
		
		$oauth_client = new OAuth($_POST['oauth_consumer_key'], $_POST['oauth_nonce'], $_POST['oauth_signature'], $_POST['oauth_timestamp'],
						$_POST['oauth_token'], $_POST['oauth_verifier']);
		if(isset($_REQUEST['oauth_version'])) $oauth_client->set_variable('version', '1.0');

		if($oauth_client->is_valid_access_token_request() && (strtolower(urldecode($_POST['oauth_signature_method'])) == 'hmac-sha1') 
					&& $oauth_client->decrypt_hmac_sha1() )
		{										
				$oauth_client->set_variable('access_token', $oauth_client->generate_access_token()); 
					$oauth_client->set_variable('user_id', $oauth_client->get_field_in_table('oauth_request_token', 'user_id', 
																'request_token', $_POST['oauth_token']));
																
				if($oauth_client->set_access_table() && $oauth_client->delete_row_in_table('oauth_request_token', 'request_token', 
											  $oauth_client->get_variable('request_token')))
				{																
					echo 'oauth_token_secret='.urlencode($oauth_client->get_variable('token_secret'));
					echo '&oauth_token='.urlencode($oauth_client->get_variable('access_token'));	
					
				}else
				{
					echo $oauth_client->get_variable('error_msg');	
					exit;
				}		
		}else
		{
			echo ($oauth_client->there_is_error()) ? $oauth_client->get_variable('error_msg'): 'Invalid signature method';
			exit;
		}		
	}else
	{
		echo 'Missing OAuth parameters.';
		exit;
	}
?>