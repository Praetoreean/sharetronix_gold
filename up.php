<?
// Upload Center - Persian Sharetronix
// Edited by Nipoto - www.Sharetronix.ir

// Uploader Information - Edit This:
$websitename="Upload Center"; //The name of your website
$max_file_size="2048"; //Max size PER file in KB
$max_combined_size="2048"; // Max size for all files COMBINED in KB
$file_uploads="7"; //Maximum file uploades at one time
$full_url="./i/upload/1/"; // File Upload folder Address with / character
$folder="./i/upload/1/"; // Path to store files on your server If this fails use $fullpath below.
$random_name=true; // Use Random file name true=yes
$allow_types=array("jpg","gif","png","zip","rar","txt","doc"); // allowed file format
$fullpath=""; // Only use this variable if you wish to use full server paths.
$password=""; //Use this only if you want to password protect your upload form.

// Initialize variables - Don't Edit:
$password_hash=md5($password);
$error="";
$success="";
$display_message="";
$file_ext=array();
$password_form="";

// Function to get the extension a file.
function get_ext($key) { 
	$key=strtolower(substr(strrchr($key, "."), 1));
	$key=str_replace("jpeg","jpg",$key);
	return $key;
}

// Filename security cleaning. Do not modify.
function cln_file_name($string) {
	$cln_filename_find=array("/\.[^\.]+$/", "/[^\d\w\s-]/", "/\s\s+/", "/[-]+/", "/[_]+/");
	$cln_filename_repl=array("", ""," ", "-", "_");
	$string=preg_replace($cln_filename_find, $cln_filename_repl, $string);
	return trim($string);
}

// If a password is set, they must login to upload files.
If($password) {
	
	//Verify the credentials.
	If($_POST['verify_password']==true) {
		If(md5($_POST['check_password'])==$password_hash) {
			setcookie("phUploader",$password_hash);
			sleep(1); //seems to help some people.
			header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
			exit;
		}
	}

	//Show the authentication form
	If($_COOKIE['phUploader']!=$password_hash) {
		$password_form="<form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\">\n";
		$password_form.="<table align=\"center\" class=\"table\" style=\"direction:rtl;\">\n";
		$password_form.="<tr>\n";
		$password_form.="<td width=\"100%\" colspan=\"2\"><br><br><br>درج رمز عبور:</td>\n";
		$password_form.="</tr>\n";
		$password_form.="<tr>\n";
		$password_form.="<td width=\"65%\" align=\"center\" class=\"table_body\" ><input type=\"password\" name=\"check_password\" /></td>\n";
		$password_form.="<td colspan=\"2\" align=\"center\" class=\"table_body\">\n";
		$password_form.="<input type=\"hidden\" name=\"verify_password\" value=\"true\">\n";
		$password_form.="<input type=\"submit\" id=\"signin_submit\" value=\" ورود به آپلودسنتر \" />\n";
		$password_form.="</td>\n";
		$password_form.="</tr>\n";
		$password_form.="</table>\n";
		$password_form.="</form>\n";
	}
	
} // If Password

// Dont allow submit if $password_form has been populated
If(($_POST['submit']==true) AND ($password_form=="")) {

	//Tally the size of all the files uploaded, check if it's over the ammount.	
	If(array_sum($_FILES['file']['size']) > $max_combined_size*1024) {
		
		$error.="<b>FAILED:</b> Combined file size is to large<br />";
		
	// Loop though, verify and upload files.
	} Else {

		// Loop through all the files.
		For($i=0; $i <= $file_uploads-1; $i++) {
			
			// If a file actually exists in this key
			If($_FILES['file']['name'][$i]) {

				//Get the file extension
				$file_ext[$i]=get_ext($_FILES['file']['name'][$i]);
				
				// Randomize file names
				If($random_name){
					$file_name[$i]=time()+rand(0,100000);
				} Else {
					$file_name[$i]=cln_file_name($_FILES['file']['name'][$i]);
				}
	
				// Check for blank file name
				If(str_replace(" ", "", $file_name[$i])=="") {
					
					$error.= "<b>FAILED:</b> Blank file name detected<br />";
				
				//Check if the file type uploaded is a valid file type. 
				}	ElseIf(!in_array($file_ext[$i], $allow_types)) {
								
					$error.= "<b>FAILED:</b> Invalide file type<br />";
								
				//Check the size of each file
				} Elseif($_FILES['file']['size'][$i] > ($max_file_size*1024)) {
					
					$error.= "<b>FAILED:</b> File to large<br />";
					
				// Check if the file already exists on the server..
				} Elseif(file_exists($folder.$file_name[$i].".".$file_ext[$i])) {
	
					$error.= "<b>FAILED:</b> File already exists<br />";
					
				} Else {
					
					If(move_uploaded_file($_FILES['file']['tmp_name'][$i],$folder.$file_name[$i].".".$file_ext[$i])) {
						
	$success.="<b>انجام شد !!</b> <a href=\"".$full_url.$file_name[$i].".".$file_ext[$i]."\" target=\"_blank\">[لینک مستقیم]</a><br>";
						
					} Else {
						$error.="<b>FAILED:</b> General upload failure<br />";
					}
					
				}
							
			} // If Files
		
		} // For
		
	} // Else Total Size
	
	If(($error=="") AND ($success=="")) {
		$error.="<b>FAILED:</b> No files selected<br />";
	}

	$display_message=$success.$error;

} // $_POST AND !$password_form


/*
//================================================================================
* Start the form layout
//================================================================================
:- Please know what your doing before editing below. Sorry for the stop and start php.. people requested that I use only html for the form..
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Language" content="en-us" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $websitename; ?></title>
<style type="text/css">
	body{
		font-family: tahoma, Verdana, Arial, sans-serif;
		font-size: 9pt;
	}
	.message {
		font-family: tahoma, Verdana, Arial, sans-serif;
		font-size: 9pt;
		direction: rtl;
		text-align: justify;
	}
	a:link, a:visited {
		text-decoration:none;
		color: #000000;
	}
	a:hover {
		text-decoration:none;
		color: #000000;
	}
	.table {
		border-collapse:collapse;
		width:240px;
	}
	.table_header {
		font-family: tahoma, Verdana, Arial, sans-serif;
		font-size: 9pt;
		font-weight:bold;
		text-align:center;
		padding:0px;
	}
	
	.upload_info {
		font-family: tahoma, Verdana, Arial, sans-serif;
		font-size: 8pt;
	}

	.table_body {
		font-family: tahoma, Verdana, Arial, sans-serif;
		font-size: 9pt;
		color: #000000;
		padding:0px;
	}
	input,select,textarea {
		font-family: tahoma, Verdana, Arial, sans-serif;
		font-size: 9pt;
		color: #000000;
		background-color:#AFAEAE;
		border:1px solid #000000;
	}
	form {
		padding:0px;
		margin:0px;
	}
	#signin_submit {
		-moz-border-radius:4px;
		-webkit-border-radius:4px;
		background:#39d url('../i/icons/bg-btn-blue.png') repeat-x scroll 0 0;
		border:1px solid #39D;
		color:#fff;
		text-shadow:0 -1px 0 #39d;
		padding:4px 10px 5px;
		font-size:11px;
		margin:0 5px 0 0;
		font-weight:bold;
	}
	#signin_submit::-moz-focus-inner {
		padding:0;
		border:0;
	}
	#signin_submit:hover, #signin_submit:focus {
		background-position:0 -5px;
		cursor:pointer;
	}
</style>

<?
If($password_form) {
	
	Echo $password_form;

} Else {
?>

<form action="<?=$_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data" name="phuploader">

<table class="table" align="center"><br>
	<?For($i=0;$i <= $file_uploads-1;$i++) {?>
		<tr>
			<td class="table_body" width="5%" style="direction:rtl; text-align:justify;"><input type="file" name="file[]" size="30" /></td>
		</tr>
	<?}?>
	<tr>
		<td colspan="2" class="upload_info" style="direction:rtl; text-align:justify; padding-top:5px; padding-bottom:5px;" align="center">
			<b>فرمت‌ مجاز:</b> <?=implode($allow_types, ", ");?><br />
			<b>حجم مجاز:</b> <?=$max_file_size?> کیلوبایت 
	<?If($display_message){?>
	<tr>
		<td colspan="2" class="message" style="padding-top:0px; padding-bottom:5px;" align="center">
		<?=$display_message;?>
		</td>
	</tr>
	<?}?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center" class="table_footer">
			<input type="hidden" name="submit" value="true" />
			<input type="reset" id="signin_submit" name="reset" value=" پاکسازی " onclick="window.location.reload(true);" /> 
			<input type="submit" id="signin_submit" value=" آپلود فایل " />
		</td>
	</tr>
</table>
</form>

<?} ?>
</body>
</html>