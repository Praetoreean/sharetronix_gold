<?php
    $this->load_template('header.php');
?>
    <style>
        .chat {
            width  : 400px;
            height : 500px;
        }

        .chat .header_chat {
            width            : 100%;
            height           : 20px;
            font             : 14px Yekan;
            text-align       : right;
            padding          : 10px;
            background-color : #101d35;
            color            : #fff;
        }

        .chat .header_chat .online {
            text-align  : left;
            font        : 12px Yekan;
            float       : left;
            margin-left : 20px;
        }

        .chat .content_chat {
            width            : 100%;
            background-color : #fafafa;
            height           : 420px;
            max-height       : 420px;
            overflow         : auto;
        }

        .chat .footer_chat {

        }

        .chat .footer_chat input {
            width            : 230px;
            float            : right;
            text-align       : right;
            direction        : rtl;
            height           : 30px;
            background-color : #E2E2E2;
            color            : #000;
            resize           : none;
            overflow         : auto;
            padding          : 5px;
            border           : none;
            font             : 14px Yekan;
        }

        .chat .footer_chat .send_chat {
            margin-top       : 1px;
            padding          : 10px 10px 10px 10px;
            width            : 40px;
            text-align       : center;
            font             : 14px Yekan;
            color            : #fff;
            background-color : #059290;
            float:right;
        }

        .chat .footer_chat .send_chat:hover {
            opacity : 0.7;
        }

        .chat .content_chat .message_box {
            display : block;
            height  : auto;
            padding : 5px;
        }

        .chat .content_chat .message_box .avatar {
            width                 : 30px;
            height                : 30px;
            display               : inline-block;
            -webkit-border-radius : 3px;
            border-radius         : 3px;
        }

        .chat .content_chat .message_box .message {
            display               : inline-block;
            background-color      : #003464;
            color                 : #fff;
            padding               : 7px;
            -webkit-border-radius : 3px;
            border-radius         : 3px;
            margin-left           : 5px;
            margin-right          : 5px;
            max-width             : 300px;
            height                : auto;
        }

        .chat .content_chat .message_box .avatar img {
            height : 30px;
            width  : 30px;

        }

        .chat .content_chat .message_box .other {
            background-color : #7aba7b;
            color            : #000;
        }

        .chat .content_chat .message_box .me {
            background-color : #E2E2E2;
            color            : #000;
        }

        .chat .content_chat .message_box .other small {
            text-align : right;
            float      : right;
        }

        .chat .content_chat .message_box .me small {
            text-align : left;
            float      : left;
        }

        .chat .content_chat .left {
            float : left;
        }

        .chat .content_chat .left .avatar {
            float : left;
        }

        .chat .content_chat .left .message {
            float : right
        }

        .chat .content_chat .right {
            float : right;
        }

        .chat .content_chat .right .avatar {
            float : right;
        }

        .chat .content_chat .right .message {
            float : left
        }

        .klear {
            clear : both;
        }
    </style>
    <div align="center">
        <div style="width:440px">
            <div align="right">
                <div class="user_box_info" style="width:400px;border-top:solid 3px #752587">
                    <div class="chat">
                        <div class="header_chat">
                            <?= $C->SITE_TITLE ?>
                            <span class="online"></span>
                        </div>
                        <div class="content_chat" id="content_chat_total">
                            <div id="content_chat">

                                <?php foreach ($D->chats as $key => $k) { ?>
                                    <div id="message_box_chat_<?= $key ?>">
                                        <div class="message_box <?= $k['user_id'] == $this->user->id ? 'right' : 'left' ?>">
                                            <div class="avatar">
                                                <img src="<?= $C->IMG_URL ?>avatars/thumbs3/<?= $k['user_id'] == $this->user->id ? $this->user->info->avatar : $this->network->get_user_by_id($k['user_id'])->avatar ?>">
                                            </div>
                                            <div class="message <?= $k['user_id'] == $this->user->id ? 'me' : 'other' ?>">
                                                <?= ($k['message']) ?><br/>
                                                <small><?= $k['date'] ?></small>
                                            </div>
                                        </div>
                                        <?php if($this->user->info->is_network_admin == 1){ ?>
                                            <a href="javascript:;" onclick="delete_chat_message(<?= $key ?>)" style="float:<?= $k['user_id'] == $this->user->id ? 'right' : 'left' ?>;margin-top:5px;"><img src="<?= $C->SITE_URL . 'themes/' . $C->THEME ?>/imgs/delete_chat_msg.gif"/></a>
                                        <?php } ?>
                                        <div class="klear"></div>
                                    </div>
                                <?php } ?>

                            </div>

                        </div>
                        <div class="footer_chat">
                            <a href="javascript:;" style="width:40px;" onclick="openSmileBox($(this))">
                                <div class="send_chat" style="background-color: #390000;color:#fff;">:)</div>
                            </a>
                            <input type="text" id="chat_message" placeholder="پیام خود را بنویسید"/>
                            <a href="javascript:;" onclick="send_chat_message()">
                                <div class="send_chat">ارسال</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .smile_box{
            width:190px;
            height:180px;
            background-color:#fff;
            border:solod 1px #000;
            position: absolute;
            -webkit-box-shadow : 2px 2px 2px #e1e1e1;
            -moz-box-shadow    : 2px 2px 2px #e1e1e1;
            box-shadow         : 2px 2px 2px #e1e1e1;
            top:100px;
            left:100px;
            display: none;
        }
        .smile_box .content_smile_box{
            padding:5px;
            overflow:auto;
        }
        .smile_box .content_smile_box a{
            width:22px;
            height:20px;
        }
        .smile_box .content_smile_box a img{
            width:18px;
            height:18px;
        }
    </style>
    <div class="smile_box" id="smile_box">
        <div class="content_smile_box">
            <?php $sm = array();foreach($C->POST_ICONS as $k => $v){ if(isset($sm[$v])){continue;}else{$sm[$v] = true;}?>
                <a href="javascript:;" onclick="addSmileTextToBox('chat_message','<?= htmlentities($k) ?>');return false;"><img src="<?= $C->IMG_URL ?>/icons/<?= $v ?>"/></a>
            <?php } ?>
        </div>
    </div>
    <script>
        function addSmileTextToBox(boxid,smiletext){
            var text = $('#'+boxid).val();
            text += smiletext;
            $('#'+boxid).val(text);
        }
        function openSmileBox(btn){
            var top = btn.offset().top - 200;
            var left = btn.offset().left + 150;
            $('#smile_box').css({top:top,left:left});
            $('#smile_box').fadeIn();
        }
        $(function(){
            $('#smile_box').mouseleave(function(){
                $('#smile_box').hide();
            })
        })
    </script>
    <?php if($this->user->info->is_network_admin == 1){ ?>
        <script type="text/javascript">
            var this_user_is_administrator = true;
        </script>
    <?php }else{ ?>
        <script type="text/javascript">
            var this_user_is_administrator = false;
        </script>
    <?php } ?>
    <script type="text/javascript">

        var lastmessagedate = '<?= intval($D->lastdate) ?>';
        var this_user_avatar = '<?= $C->IMG_URL ?>avatars/thumbs3/<?= $this->user->info->avatar ?>';
        var this_user_link = '<?= userlink($this->user->info->username) ?>';
        var smile_url_base = '<?= $C->IMG_URL ?>/icons/';

        /**
         * Smile Array
         */
        var SmileArrays = {};
        <?php foreach($C->POST_ICONS as $k=>$v){ ?>
            SmileArrays['<?= $k ?>'] = '<?= $v ?>';
        <?php } ?>





        $(document).ready(function () {
            $('#chat_message').keypress(function (e) {
                if (e.which == 13) {
                    send_chat_message();
                }
            });

        });
        function delete_chat_message($chat_id) {
            $.post(siteurl + 'ajax/chat/delete/r:' + Math.round(Math.random() * 1000), 'chat_id=' + encodeURIComponent($chat_id), function (data) {
                if (data == 'OK') {
                    $('#message_box_chat_' + $chat_id).hide();
                } else {
                    alert('ERROR at Delete');
                }
            });
        }
        function send_chat_message() {
            var message = trim($('#chat_message').val());
            message     = message.replace(/\r\n|\n|\r/, "");
            message     = message.replace(/^\s+/, "");
            if (message == '' || message == null || message == "\r\n" || message == "\n") {
                $('#chat_message').val('');
                return false;
            }

            $('#chat_message').val('');

            var html   = '';
            randNumber = Math.round(Math.random() * 1000);

            /**
             * Ritht Template For this User Send Messsages
             * @type {string}
             */
            var orig_message = message;
            for (var i in SmileArrays)
            {
                var txt = i;
                message = message.replace(i,'<img src="'+smile_url_base+SmileArrays[i]+'" class="post_smiley" />');
            }

            html += '<div id="message_box_chat_'+randNumber+'">';
            html += '<div class="message_box right" id="message_box_' + randNumber + '" style="opacity: 0.4;"><div class="avatar"><img src="';
            html += this_user_avatar;
            html += '"></div><div class="message me">';
            html += message;
            var _date = new Date();
            html += '<br/><small>'+_date.getHours() + ':'+_date.getMinutes()+'</small></div></div><div class="klear"></div></div>';


            $('#content_chat').append(html);

            scrollTop = parseInt($('#content_chat_total').scrollTop());
            $('#content_chat_total').scrollTop(scrollTop + 50);

            $.post(siteurl + 'ajax/chat/set/r:' + Math.round(Math.random() * 1000), {
                message: orig_message
            }, function (data) {
                if (data.substr(0,3) == 'OK:') {
                    data = parseInt(data.replace(/^OK\:/,""));
                    if(this_user_is_administrator){
                        deleteButton = '<a href="javascript:;" onclick="delete_chat_message('+data+')" style="float:right;margin-top:5px;"><img src="'+siteurl+'themes/default/imgs/delete_chat_msg.gif"/></a>';
                        $('#message_box_chat_'+randNumber).find('.klear').before(deleteButton);
                    }
                    $('#message_box_' + randNumber).css('opacity', 1);
                    get_chat_message()
                } else {
                    $('#message_box_' + randNumber).css('background-color', 'red');
                }
            });
        }
        function get_chat_message() {
            $.post(siteurl + 'ajax/chat/get/r:' + Math.round(Math.random() * 1000), {
                lastdate: lastmessagedate
            }, function (data) {
                if (data.substr(0, 3) == 'OK:') {

                    lastdate = data.match(/^OK\:([0-9]+)\:/g);
                    lastdate = lastdate.toString().match(/([0-9]+)/);
                    lastdate = parseInt(lastdate, 10);

                    if (lastdate != 0) {
                        lastmessagedate = lastdate;
                    }


                    data = data.replace(/^OK\:([0-9]+)\:/, "");
                    obj  = JSON.parse(data);
                    for (i = 0; i < obj.length; i++) {

                        /**
                         * Left Message Box That Recived From database
                         * @type {string}
                         */
                        html = '';
                        html += '<div id="message_box_chat_'+obj[i].chat_id+'">';
                        html += '<div class="message_box left"><div class="avatar"><img src="';
                        html += obj[i].avatar;
                        html += '"></div><div class="message other">';
                        html += obj[i].message;
                        html += '<br/><small>'+obj[i].date+'</small></div></div>';
                        if(this_user_is_administrator){
                            deleteButton = '<a href="javascript:;" onclick="delete_chat_message('+obj[i].chat_id+')" style="float:left;margin-top:5px;"><img src="'+siteurl+'themes/default/imgs/delete_chat_msg.gif"/></a>';
                            html += deleteButton;
                        }
                        html +='<div class="klear"></div></div>';

                        $('#content_chat').append(html);

                        scrollTop = parseInt($('#content_chat_total').scrollTop());
                        $('#content_chat_total').scrollTop(scrollTop + 50);
                    }
                } else {
                    return;
                }
            })
        }


        setInterval("get_chat_message()", 15000);
    </script>
<?php
    $this->load_template('footer.php');
?>