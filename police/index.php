<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<html dir="rtl">

<head>
	<title>پلیسِ سایت</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript" src="js/functionAddEvent.js"></script>
	<script type="text/javascript" src="js/contact.js"></script>
	<script type="text/javascript" src="js/xmlHttp.js"></script>
	<style type='text/css' media='screen,projection'>
	<!--
	fieldset { border:0;margin:0;padding:0; }
	label { display:block; }
	input.text,textarea { width:300px;font:8px/8px 'courier new',courier,monospace;color:#333;padding:3px;margin:1px 0;border:1px solid #ccc; }
	input.submit { padding:2px 5px;font:bold 10px/10px Tahoma; }
	
	-->
	</style>
</head>
<body>
<div align="center">
<div style="width:320px;padding:20px;border:1px solid #ccc;background:#fff;font-family:Tahoma; text-align:right">
	<h2><span style="font-size: 8pt">فرم گزارشِ ارسالهای خلاف قوانین</span></h2>
	<p id="loadBar" style="display:none;">
		<strong><span style="font-size: 8pt">در حال ارسال گزارش ... چند لحظه صبر 
		کنید !</span></strong><span style="font-size: 8pt">
		<img src="img/loading.gif" alt="Loading..." title="Sending Email" />
		</span>
	</p>
	<p id="emailSuccess" style="display:none;">
		<span style="font-size: 8pt"><strong><span style="color: #008000">گزارش 
		شما با موفقیت ارسال شد !</span></strong> </span>
	</p>
	<div id="contactFormArea">
		<form action="scripts/contact.php" method="post" id="cForm">
			<fieldset>
				<label for="posName"><span style="font-size: 8pt">نام کاربر خاطی :</span></label><span style="font-size: 8pt">
				<font face="Tahoma">
				<input class="text" type="text" size="8" name="posName" id="posName" style="font-size: 8pt; font-family: Tahoma; color: #000000" /></font>
				<label for="posEmail">ایمیل شما :</label>
				<font face="Tahoma">
				<input class="text" type="text" size="8" name="posEmail" id="posEmail" style="font-size: 8pt; font-family: Tahoma; color: #000000; text-align:left" /></font>
				<label for="posRegard">آدرس صفحه‌ی پیام :</label>
				<font face="Tahoma">
				<input class="text" type="text" size="8" name="posRegard" id="posRegard" style="font-family: Tahoma; text-align: left; font-size: 8pt; color: #000000" /></font>
				<label for="posText">متن گزارش :</label>
				<font face="Tahoma">
				<textarea cols="50" rows="5" name="posText" id="posText" style="font-family: Tahoma; font-size: 8pt; color: #000000"></textarea></font>
				</span>
				<label for="selfCC">
					<span style="font-size: 8pt"><font face="Tahoma">
					<input type="checkbox" name="selfCC" id="selfCC" value="send" /></font> 
				فرستادن یک کپی از گزارش به شما </span>
				</label>
				<label>
					<span style="font-size: 8pt">
					<input class="submit" type="submit" name="sendContactEmail" id="sendContactEmail" value="ارسال گزارش" style="font-size: 8pt; font-family: Tahoma" />
				</span>
				</label>
	
			</fieldset>
		</form>
	</div>
	<div class='note'></div>
</div>
</div>
</body>
</html>