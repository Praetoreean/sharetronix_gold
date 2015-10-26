<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="rtl">
<head>
<meta http-equiv="Content-Language" content="fa" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
.style2 {
	text-align: justify;
	font-family: Tahoma;
	font-size: 11px;
	font-color: black;
	float:right;
	direction: rtl;
}
</style>
</head>
<body>
<div align="center">
<table border="0" width="100%" style="background-color: #5599BC">
	<tr>
		<td style="width: 100px">&nbsp;</td>
		<td style="width: 500px">
		<p class="style1">
		<a href="<?= $C->SITE_URL ?>">
		<img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/logo.png" align="center" style="border-width: 0;" />
		</a></p>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>
<table border="0" width="100%" style="background-color: #c0deed">
	<tr>
		<td style="width: 100px">&nbsp;</td>
		<td style="width: 500px">
		<p class="style2">
		<br><br>
		<?= nl2br($this->lang('os_invite_email_hello', $D->lang_keys)) ?><br>
		<?= nl2br($this->lang('os_invite_email_message', $D->lang_keys)) ?><br><br>
		<?= nl2br($this->lang('os_invite_email_regtext', $D->lang_keys)) ?><br />
		<a href="<?= $D->registration_link ?>" target="_blank"><?= $D->registration_link ?><br><br>
		</p>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>
<table border="0" width="100%" style="background-color: #DBDBDB">
	<tr>
		<td style="width: 100px">&nbsp;</td>
		<td style="width: 500px">
		<!-- Powered by www.Sharetronix.ir -->
		<p class="style2"><?= nl2br($this->lang('os_invite_email_signature', $D->lang_keys)) ?></p>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>
</div>
</body>
</html>