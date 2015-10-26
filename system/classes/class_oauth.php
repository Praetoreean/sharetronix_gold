<?php
class OAuth
{
	private $user_id;
	private $nonce;
	private $timestamp;
	private $consumer_key;
	private $callback;
	private $signature;
	private $token_secret;
	private $request_token;
	private $access_token;
	private $verifier;
	private $version;
	private $error;
	private $error_msg;
	private $stage;
	private $stage_url;
	
	
	public function __construct()
	{
		global $C;
		
		if(func_num_args() == 4)
		{
			//this is in get_request_token
			//args: oauth_consumer_key, oauth_nonce, oauth_signature, oauth_timestamp
			$this->consumer_key = urldecode(func_get_arg(0));
			$this->nonce = urldecode(func_get_arg(1));
			$this->signature = urldecode(func_get_arg(2));
			$this->timestamp = urldecode(func_get_arg(3));
			
			$this->stage = 1;
			$this->stage_url = $C->SITE_URL.'oauth/request_token';
		}elseif(func_num_args() == 1)
		{
			//this is in get_auth
			//args: oauth_token
			$this->request_token = urldecode(func_get_arg(0));
			
			$this->stage = 2;
		}elseif(func_num_args() == 6)
		{
			//this is in get_token
			//args: oauth_consumer_key, oauth_nonce, oauth_signature, oauth_timestamp, oauth_token, oauth_verifier
			$this->consumer_key = urldecode(func_get_arg(0));
			$this->nonce = urldecode(func_get_arg(1));
			$this->signature = urldecode(func_get_arg(2));
			$this->timestamp = urldecode(func_get_arg(3));
			$this->request_token = urldecode(func_get_arg(4));
			$this->verifier = urldecode(func_get_arg(5));
			
			$this->stage = 3;
			$this->stage_url = $C->SITE_URL.'oauth/access_token';
		}elseif(func_num_args() == 5)
		{
			//this is in get resource
			//args: oauth_consumer_key, oauth_nonce, oauth_token, oauth_timestamp, oauth_signature
			$this->consumer_key = urldecode(func_get_arg(0));
			$this->nonce = urldecode(func_get_arg(1));
			$this->access_token = urldecode(func_get_arg(2));
			$this->timestamp = urldecode(func_get_arg(3));
			$this->signature = urldecode(func_get_arg(4));
			
			$this->stage = 4;
		}
	}
	public function are_ascii_characters($string)
	{
		for($i = 0; $i< strlen($string); $i++)
			if(ord($string[$i]) > 127) return false;	
		return true;
	}
	public function check_access_type($access_type_requested)
	{
		if($this->check_consumer_key()) return true;
		if($access_type_requested == 'r') return true;
		
		$app_access_type = $this->get_value_in_consumer_key('access'); 
		if($app_access_type != 'rw') return false;
		
		return true;
	}
	public function check_rate_limits($user_id, $rate_num)
	{
		global $db2, $C;
		
		$app_id = $this->get_value_in_consumer_key('app_id');

		$query = 'SELECT rate_limits, rate_limits_date FROM oauth_access_token WHERE app_id = \''.$app_id.'\' AND user_id=\''.$user_id.'\' LIMIT 1';

		$res = $db2->query($query);
		$data = $db2->fetch_object($res);

		if(!$data) return false;		
				
		if( (($data->rate_limits + $rate_num) < 150) || ($data->rate_limits_date != date('G', time())))
		{
			if($data->rate_limits_date != date('G', time()))
			{
				if($this->restart_rate_limits($user_id)) return true;
					else return false;
			}
			
			if($this->update_rate_limits($user_id, $rate_num)) return true;
				else return false;
				
		}else return false;			
	}
	public function restart_rate_limits($user_id)
	{
		global $db2;
		
		$app_id = $this->get_value_in_consumer_key('app_id');
		
		$query = 'UPDATE oauth_access_token SET rate_limits=0, rate_limits_date=\''.intval(date('G', time())).'\'';
		$query .= ' WHERE app_id = \''.$app_id.'\' AND user_id=\''.$user_id.'\' LIMIT 1';
		
		$query = $db2->query($query);
		
		if($db2->affected_rows($query) > 0) return true;	
			else return false;			
	}
	public function update_rate_limits($user_id, $rate_num)
	{
		global $db2;
		
		$app_id = $this->get_value_in_consumer_key('app_id');
		
		$query = 'UPDATE oauth_access_token SET rate_limits=(rate_limits+'.$rate_num.'), rate_limits_date=\''.intval(date('G', time())).'\' WHERE app_id = \''.$app_id.'\' AND user_id=\''.$user_id.'\' LIMIT 1';
		
		$query = $db2->query($query);
		
		if($db2->affected_rows($query) > 0) return true;	
			else return false;			
	}
		
	public function is_unique_field($table, $column, $value)
	{
		global $db2;
		
		$query = $db2->query('SELECT 1 FROM '.$table.' WHERE '.$column.' = \''.$value.'\'');
		if($db2->num_rows($query) > 0) return false;
		return true; 
	}
	public function is_valid_nonce()
	{
		global $db2;
		
		if(empty($this->nonce) || !$this->are_ascii_characters($this->nonce))
		{
			$this->set_error('Invalid nonce. Your nonce must contain only ascii characters.');
			return false;
		}elseif(strlen($this->nonce)>250 || strlen($this->nonce) < 5)
		{
			$this->set_error('Invalid nonce. Your nonce must be between 5 and 250 characters long.');	
			return false;	
		} 

		$query = $db2->query('SELECT 1 FROM oauth_request_token WHERE consumer_key=\''.$this->consumer_key.'\' and nonce=\''.$this->nonce.'\'');
		if($db2->num_rows($query) > 0)
		{
			$this->set_error('Your nonce must be unique.');
			return false;
		}		
		return true;
	}	
	public function is_valid_consumer_key()
	{
		if($this->check_consumer_key()) return true;
		
		if($this->get_field_in_table('applications', 'consumer_key', 'consumer_key', $this->consumer_key)) return true;
		else
		{
			$this->set_error('Invalid consumer key.');
			return false;
		}
	}
	public function is_valid_timestamp()
	{
		if((intval($this->timestamp)) && ($this->timestamp <= time()+600 && $this->timestamp > time()-600))	return true;
		else
		{
			$this->set_error('Invalid timestamp');
			return false;
		}
	}
	public function is_valid_application()
	{
		global $db2;
		
		if($this->check_consumer_key()) return true;
		
		$res = $db2->query('SELECT 1 FROM applications WHERE consumer_key=\''.$this->consumer_key.'\' AND suspended=1 LIMIT 1');

		if($db2->num_rows($res) == 0) return true;
		else
		{
			$this->set_error('This Application is suspended');
			return false;
		}
	}
	public function is_valid_request_token($check_timestamp = NULL)
	{	
		global $db2;
		
		if($this->request_token != '')
		{
			if(!$check_timestamp) $query = 'SELECT 1 FROM oauth_request_token WHERE request_token=\''.$this->request_token.'\'';
			else
			{
				$query = 'SELECT 1 FROM oauth_request_token WHERE request_token=\''.$this->request_token.'\'';
				$query .= ' and time_stamp<=\''.(time()+1000).'\' and time_stamp>=\''.(time()-1000).'\'';
			}			
			$query = $db2->query($query);
			if($db2->num_rows($query) > 0) return true;
			else
			{
				$this->set_error('Invalid request token/timestamp');
				return false;
			}
		}else
		{
			$this->set_error('Invalid request token');
			return false;
		}
	}
	public function get_verifier_request()
	{
		global $db2;
		
		$app_id = $this->get_value_in_consumer_key('app_id');
			
		$query = $db2->query('DELETE FROM oauth_access_token WHERE app_id=\''.$app_id.'\' and user_id=\''.$this->user_id.'\' LIMIT 1');
		
		return $this->generate_verifier();
	}
	public function is_valid_access_token_request()
	{
		global $db2;
		
		$q = 'SELECT 1 FROM oauth_request_token WHERE time_stamp=\''.$this->timestamp.'\' AND nonce=\''.$this->nonce.'\'';
		$res = $db2->query($q);
		if($db2->num_rows($res) > 0) 
		{
			$this->set_error('Invalid nonce/timestamp combination.');
			return false;
		}
		
		$query = 'SELECT 1 FROM oauth_request_token WHERE consumer_key=\''.$this->consumer_key.'\' and time_stamp<=\''.(time()+1000).'\' and time_stamp>=\''.(time()-1000).'\' and request_token=\''.$this->request_token.'\' and verifier=\''.$this->verifier.'\'';

		$query = $db2->query($query);
		if($db2->num_rows($query) > 0) return true;
		else
		{
			$this->delete_row_in_table('oauth_request_token', 'request_token', $this->request_token);
			$this->set_error('Not valid access token request');
			return false;
		}
	}
	public function is_valid_get_resource_request()
	{
		global $db2;
		
		$q = 'SELECT 1 FROM oauth_access_token WHERE time_stamp=\''.$this->timestamp.'\' AND nonce=\''.$this->nonce.'\'';
		$res = $db2->query($q);
		if($db2->num_rows($res) > 0) 
		{
			$this->set_error('Invalid nonce/timestamp combination.');
			return false;
		}
		
		$query = 'SELECT 1 FROM oauth_access_token WHERE consumer_key=\''.$this->consumer_key.'\' and access_token=\''.$this->access_token.'\' and time_stamp<=\''.(time()).'\' and user_verified=1';
		$query = $db2->query($query);
		if($db2->num_rows($query) > 0) return true;
		else
		{
			$this->set_error('Not valid get resource request or revoked by user');
			return false;
		}
	}
	public function log()
	{
		global $db2;
		
		if($this->there_is_error()) return false;
		
		if($this->check_consumer_key()) 
		{
			$q = 'INSERT INTO oauth_log(app_id, user_id, date) VALUES(1, 1, '.(time()).')';
		}else
		{
			$q = 'INSERT INTO oauth_log(app_id, user_id, date) VALUES('.$db2->e($this->get_value_in_consumer_key('app_id'));
			$q .= ', '.$this->user_id.', '.(time()).')';
		}
		$q = $db2->query($q);
		if($q) return true;
		else
		{
			$this->set_error('Server log error.');
			return false;
		}
	}
	public function check_consumer_key()
	{
		$consumer_keys = array(
					'KkrTiBu0hEMJ9dqS3YCxw', //ChromedBird
			);
		if(in_array($this->consumer_key, $consumer_keys)) return true;
			else return false;
	}
	public function check_consumer_secret()
	{
		$consumer_secrets = array(
					'KkrTiBu0hEMJ9dqS3YCxw' => 'MsuvABdvwSn2ezvdQzN4uiRR44JK8jESTIJ1hrhe0U', //ChromedBird
			);

		if(isset($consumer_secrets[$this->consumer_key])) return $consumer_secrets[$this->consumer_key];
			else return false;
		
	}
	public function decrypt_plaintext()
	{
		if($this->stage == 1)
		{
			$sig = explode('&', $this->signature);
			if($sig[0] == $this->get_field_in_table('applications', 'consumer_secret', 'app_id', $this->get_value_in_consumer_key('app_id')))
				return true;
			else
			{
				$this->set_error('Invalid signature.');
				return false;	
			}
		}elseif($this->stage == 3)
		{
			$sig = explode('&', $this->signature);

			if(($sig[0] == $this->get_field_in_table('applications', 'consumer_secret', 'app_id', $this->get_value_in_consumer_key('app_id'))) 
				&& ($sig[1] == $this->get_field_in_table('oauth_request_token', 'token_secret', 'request_token', $this->request_token)))
					return true;
			else
			{
				$this->set_error('Invalid signature.');
				return false;	
			}
		}
	}
	public function decrypt_rsa_sha1()
	{
		return false;
	}
	public function decrypt_hmac_sha1()
	{
		global $db2;
		
		//building the string
		$string_to_encode = '';
		
		//request method
		$request_type = strtoupper(utf8_encode($_SERVER['REQUEST_METHOD']));	
		$string_to_encode .= $request_type.'&';

		//url of the resource
		$url = parse_url($this->stage_url);
		$string_to_encode .= urlencode(utf8_encode(strtolower($url['scheme'].'://'.$url['host']).$url['path'])).'&';
		
		//building the parameters
		$parameters = '';
		
		//weird parameters of the resource
		if(isset($_GET['count'])) $parameters .= urlencode(utf8_encode('count')).'='.urlencode(utf8_encode($_GET['count'])).'&';
		if(isset($_GET['cursor'])) $parameters .= urlencode(utf8_encode('cursor')).'='.urlencode(utf8_encode($_GET['cursor'])).'&';
		if(isset($_GET['include_entities'])) $parameters .= urlencode(utf8_encode('include_entities')).'='.urlencode(utf8_encode($_GET['include_entities'])).'&';
		if(isset($_GET['max_id'])) $parameters .= urlencode(utf8_encode('max_id')).'='.urlencode(utf8_encode($_GET['max_id'])).'&';
		
		if($request_type == 'POST')
		{
			if(isset($_POST['in_reply_to_status_id'])) $parameters .= urlencode(utf8_encode('in_reply_to_status_id')).'='.urlencode(utf8_encode($_POST['in_reply_to_status_id'])).'&';
		}
		
		//oauth parameters of the resource
		$parameters .= urlencode(utf8_encode('oauth_consumer_key')).'='.urlencode(utf8_encode($this->consumer_key)).'&';
		$parameters .= urlencode(utf8_encode('oauth_nonce')).'='.urlencode(utf8_encode($this->nonce)).'&';
		$parameters .= urlencode(utf8_encode('oauth_signature_method')).'='.urlencode(utf8_encode('HMAC-SHA1')).'&';
		$parameters .= urlencode(utf8_encode('oauth_timestamp')).'='.urlencode(utf8_encode($this->timestamp));
		
		if($this->stage>=3)$parameters .= '&'.urlencode(utf8_encode('oauth_token')).'=';
		
		if($this->stage==3) $parameters .= urlencode(utf8_encode($this->request_token));
			elseif($this->stage > 3) $parameters .= urlencode(utf8_encode($this->access_token));
		
		if($this->stage==3) $parameters .= '&'.urlencode(utf8_encode('oauth_verifier')).'='.urlencode(utf8_encode($this->verifier));
		
		if(isset($this->version)) $parameters .= '&'.urlencode(utf8_encode('oauth_version')).'='.urlencode(utf8_encode('1.0'));

		//another weird parameters of the resource
		if(isset($_GET['page'])) $parameters .= '&'.urlencode(utf8_encode('page')).'='.urlencode(utf8_encode($_GET['page']));
		if(isset($_GET['per_page'])) $parameters .= '&'.urlencode(utf8_encode('per_page')).'='.urlencode(utf8_encode($_GET['per_page']));
		if(isset($_GET['screen_name'])) $parameters .= '&'.urlencode(utf8_encode('screen_name')).'='.urlencode(utf8_encode($_GET['screen_name']));
		if(isset($_GET['since_id'])) $parameters .= '&'.urlencode(utf8_encode('since_id')).'='.urlencode(utf8_encode($_GET['since_id']));
		if(isset($_GET['source_id'])) $parameters .= '&'.urlencode(utf8_encode('source_id')).'='.urlencode(utf8_encode($_GET['source_id']));
		if(isset($_GET['source_screen_name'])) $parameters .= '&'.urlencode(utf8_encode('source_screen_name')).'='.urlencode(utf8_encode($_GET['source_screen_name']));
		if(isset($_GET['target_id'])) $parameters .= '&'.urlencode(utf8_encode('target_id')).'='.urlencode(utf8_encode($_GET['target_id']));
		if(isset($_GET['target_screen_name'])) $parameters .= '&'.urlencode(utf8_encode('target_screen_name')).'='.urlencode(utf8_encode($_GET['target_screen_name']));
		if(isset($_GET['trim_user'])) $parameters .= '&'.urlencode(utf8_encode('trim_user')).'='.urlencode(utf8_encode($_GET['trim_user']));
		if(isset($_GET['user_a'])) $parameters .= '&'.urlencode(utf8_encode('user_a')).'='.urlencode(utf8_encode($_GET['user_a']));
		if(isset($_GET['user_b'])) $parameters .= '&'.urlencode(utf8_encode('user_b')).'='.urlencode(utf8_encode($_GET['user_b']));
		if(isset($_GET['user_id'])) $parameters .= '&'.urlencode(utf8_encode('user_id')).'='.urlencode(utf8_encode($_GET['user_id']));
		
		if($request_type == 'POST')
		{
			if(isset($_POST['status'])){
				//$_POST['status'] = str_replace(" ", "%20", $_POST['status']);
				$parameters .= '&'.utf8_encode('status').'='.rawurlencode(utf8_encode($_POST['status']));
				}
			if(isset($_POST['text'])){
				//$_POST['status'] = str_replace(" ", "%20", $_POST['status']);
				$parameters .= '&'.utf8_encode('text').'='.rawurlencode(utf8_encode($_POST['text']));
				}
		}
		$string_to_encode .= urlencode($parameters);
		
		//get the consumer secret and token secret to build the signature
		if($this->stage == 3) $get_from = 'oauth_request_token WHERE request_token =\''.$this->request_token.'\'';
			elseif($this->stage == 4) $get_from = 'oauth_access_token WHERE access_token=\''.$this->access_token.'\'';

		if(!$this->check_consumer_secret())
		{
			$app_id = $this->get_value_in_consumer_key('app_id'); 
			$c_secret = $this->get_field_in_table('applications', 'consumer_secret', 'app_id', $app_id);
		}else $c_secret = $this->check_consumer_secret();	

		if($this->stage == 1) $signature = urlencode($c_secret).'&';
		else 
		{	
			$query = $db2->query('SELECT token_secret FROM '.$get_from); 
			$t_secret = $db2->fetch_object($query);
				
			if($t_secret && $c_secret)
			{
				$t_secret = $t_secret->token_secret;
				
			}else
			{
				$this->set_error('Internal error (123)');
				return false;
			}
			
			$signature = urlencode($c_secret).'&'.urlencode($t_secret);
		}

		$res = base64_encode(hash_hmac('sha1', $string_to_encode, $signature, true));
		$this->signature = str_replace(" ", "+", $this->signature);

		if($res == $this->signature) return true;
		else
		{
			$this->set_error('Invalid signature.');
			return false;
		}	
	}	
	public function generate_request_token()
	{
		while(1)
		{
			$this->request_token = substr(md5(rand().time().rand()), 0, 7);
			if($this->is_unique_field('oauth_request_token', 'request_token', $this->request_token)) break;
		}
		return $this->request_token;	
	}
	public function generate_access_token()
	{
		while(1)
		{
			$this->access_token = md5(rand().time().rand());
			if($this->is_unique_field('oauth_access_token', 'access_token', $this->access_token)) break;
		}
		return $this->access_token;	
	}
	public function generate_verifier()
	{
		$this->verifier = substr(md5(rand().time().rand()), 0, 6);
		if($this->update_field_in_table('oauth_request_token', 'verifier', $this->verifier, 'request_token', $this->request_token))
		{
			return $this->verifier;
		}else 
		{
			$this->set_error('Could not generate verifier.');
			return false;
		}		
	}
	public function generate_random_value($size = NULL)
	{
		if(!$size)	return md5(rand().time().rand());
			else return substr(md5(rand().time().rand()), 0, $size);
	}	
	public function get_value_in_consumer_key($value)
	{
		$str = base64_decode($this->consumer_key);
		parse_str($str, $str);
		if(isset($str[$value])) return $str[$value];
		
		return 4;
	}	
	public function set_variable($var_name, $var_value)
	{
		$this->{$var_name} = $var_value;
	}
	public function get_variable($var_name)
	{
		return $this->{$var_name};
	}
	public function set_request_table()
	{
		global $db2;
		
		if($this->there_is_error()) return false;
		
		$query = 'INSERT INTO oauth_request_token(consumer_key, nonce, time_stamp, version, token_secret, request_token, verifier, user_id) ';
		$query .= 'VALUES(\''.$this->consumer_key.'\', \''.$this->nonce.'\', \''.$this->timestamp.'\', \'1.0\',';
		$query .= ' \''.$this->token_secret.'\', \''.$this->request_token.'\', \'0\', 0)';

		$query = $db2->query($query);
		if($query) return true;
		else
		{
			$this->set_error('Could not set request table.');
			return false;
		}
	}
	public function set_access_table()
	{
		global $db2;
		
		if($this->there_is_error()) return false;
		
		$this->token_secret = $this->get_field_in_table('oauth_request_token', 'token_secret', 'request_token', $this->request_token);
		
		$query = 'INSERT INTO oauth_access_token(app_id, consumer_key, nonce, time_stamp, version, token_secret,';
		$query .= ' access_token, user_id, user_verified) VALUES(\''.$this->get_value_in_consumer_key('app_id').'\', ';
		$query .= '\''.$this->consumer_key.'\', \''.$this->nonce.'\', ';
		$query .= '\''.$this->timestamp.'\', \'1.0\', \''.$this->token_secret.'\', ';
		$query .= '\''.$this->access_token.'\', \''.$this->user_id.'\', 1)';
		
		$query = $db2->query($query);
		if($query) return true;
		else
		{
			$this->set_error('Could not set access table.');
			return false;
		}
	}
	public function delete_row_in_table($table_name, $column, $value)
	{
		global $db2;
		
		if($this->there_is_error()) return false;

		$query = $db2->query('DELETE FROM '.$table_name.' WHERE '.$column.'=\''.$value.'\' LIMIT 1');
		if($query) return true;
		else
		{
			$this->set_error('Server database error (afs).');
			return false;
		}
	}
	public function get_field_in_table($table_name, $field, $column, $value)
	{
		global $db2;

		$query = $db2->query('SELECT '.$field.' FROM '.$table_name.' WHERE '.$column.'=\''.$value.'\'');
		if($db2->num_rows($query) > 0)
		{
			$result = $db2->fetch_object($query);
			return $result->$field;
		}
		else return false;
	}
	public function update_field_in_table($table_name, $set_column_name, $set_column_value, $where_column_name, $where_column_value)
	{	
		global $db2;
		
		if($this->there_is_error()) return false;
		
		$query = 'UPDATE '.$table_name.' SET '.$set_column_name.'=\''.$set_column_value.'\' WHERE ';
		$query .= $where_column_name.'=\''.$where_column_value.'\' LIMIT 1';
		$query = $db2->query($query);
			
		if($query) return true;
		else return false;
	}	
	public function set_error($err)
	{
		$this->error = true;
		$this->error_msg = $err;
	}
	public function clean_error()
	{
		$this->error = false;
		$this->error_msg = '';
	}
	public function there_is_error()
	{
		if($this->error) return true;
		
		return false;
	}	
}
?>