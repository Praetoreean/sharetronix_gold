function new_activity_check()
{	
	$.post(siteurl+'ajax/new-activity-check/r:'+Math.round(Math.random()*1000), "tab="+encodeURIComponent(about_tab)+"&user_id="+encodeURIComponent(about_user_id)+"&last_post_id="+encodeURIComponent(last_post_id)+"&group_id="+encodeURIComponent(group_id), function(data) {
		var new_posts	= data.match(/^OK\:([0-9]+)/g);
		if( ! new_posts ) { return; }
		new_posts	= new_posts.toString().match(/([0-9]+)/);
		new_posts 	= parseInt(new_posts, 10);
		if( new_posts > 0 ){
			$('#loadnewactivity').html(new_posts+' ارسال جدید').show('slow');
		}else{
			$('#loadnewactivity').html('').hide();
		}
	});
	setTimeout( new_activity_check, 5000 );
}
function new_activity_show()
{	
	$.post(siteurl+'ajax/new-activity-show/r:'+Math.round(Math.random()*1000), "tab="+encodeURIComponent(about_tab)+"&user_id="+encodeURIComponent(about_user_id)+"&last_post_id="+encodeURIComponent(last_post_id)+"&group_id="+encodeURIComponent(group_id), function(data) {
		var get_last_id	= data.match(/^OK\:([0-9]+)\:/); //edit match here
		if( ! get_last_id ) { return; }
		get_last_id	= get_last_id.toString().match(/([0-9]+)/g); //edit
		get_last_id = parseInt(get_last_id, 10);
		
		if( get_last_id != last_post_id ){
			var currentTime = new Date()
			currenttime = currentTime.getFullYear() + '_' + currentTime.getMonth() + '_' + currentTime.getDate() + '_' + currentTime.getHours() + '_' + currentTime.getMinutes() + '_' + currentTime.getSeconds();
			
			$('#loading_posts').hide();
			jQuery('<div/>', {
				id: '"'+currenttime+'"',
				css: {  
					display: 'none',  
					overflow: 'visible'  
				}, 
			}).html(data.replace(/^OK\:([0-9]+)\:/, "")).insertAfter($('#insertAfter')).show('slow');
			last_post_id = get_last_id;	
		}
	});
	$('#loadnewactivity').hide();
	$('#loading_posts').css('display', 'inline');
}