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
                            <?= $C->SITE_TITLE ?> همکلاسیها(آزمایشی)
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

        $(document).ready(function () {
            $('#chat_message').keypress(function (e) {
                if (e.which == 13) {
                    send_chat_message();
                }
            });

        });
        
        /**
         * Smile Array
         */
        var SmileArrays = {};
        SmileArrays['B-)'] = 's1.gif';
        SmileArrays['b-)'] = 's1.gif';
        SmileArrays['#:-s'] = 's2.gif';
        SmileArrays['#:-S'] = 's2.gif';
        SmileArrays[':-&'] = 's3.gif';
        SmileArrays[':-$'] = 's4.gif';
        SmileArrays['[-('] = 's5.gif';
        SmileArrays[':D'] = 's31.gif';
        SmileArrays[':-D'] = 's31.gif';
        SmileArrays[':d'] = 's31.gif';
        SmileArrays[':-d'] = 's31.gif';
        SmileArrays['@-)'] = 's8.gif';
        SmileArrays[':-w'] = 's9.gif';
        SmileArrays[':w'] = 's9.gif';
        SmileArrays[':-W'] = 's9.gif';
        SmileArrays[':W'] = 's9.gif';
        SmileArrays[':-L'] = 's10.gif';
        SmileArrays[':-l'] = 's10.gif';
        SmileArrays[';))'] = 's14.gif';
        SmileArrays[':-@'] = 's15.gif';
        SmileArrays['x('] = 's22.gif';
        SmileArrays['X('] = 's22.gif';
        SmileArrays['x-('] = 's22.gif';
        SmileArrays['X-('] = 's22.gif';
        SmileArrays[':-B'] = 's28.gif';
        SmileArrays[':-b'] = 's28.gif';
        SmileArrays[':B'] = 's28.gif';
        SmileArrays[':b'] = 's28.gif';
        SmileArrays[':|'] = 's23.gif';
        SmileArrays[':-o'] = 's20.gif';
        SmileArrays[':-O'] = 's20.gif';
        SmileArrays[':o'] = 's20.gif';
        SmileArrays[':O'] = 's20.gif';
        SmileArrays['^:)^'] = 's16.gif';
        SmileArrays['=))'] = 's29.gif';
        SmileArrays['>:p'] = 's11.gif';
        SmileArrays[':-j'] = 's17.gif';
        SmileArrays['<:-P'] = 's7.gif';
        SmileArrays['<:-p'] = 's7.gif';
        SmileArrays['=;'] = 's24.gif';
        SmileArrays['>:D<'] = 's19.gif';
        SmileArrays['>:d<'] = 's19.gif';
        SmileArrays['$-)'] = 's12.gif';
        SmileArrays['0:-)'] = 's21.gif';
        SmileArrays[';;)'] = 's27.gif';
        SmileArrays[';)'] = 's34.gif';
        SmileArrays[':-??'] = 's18.gif';
        SmileArrays[':)'] = 's33.gif';
        SmileArrays[':-/'] = 's30.gif';
        SmileArrays[':(('] = 's26.gif';
        SmileArrays[':-p'] = 's32.gif';
        SmileArrays[':-P'] = 's32.gif';
        SmileArrays[':p'] = 's32.gif';
        SmileArrays[':-P'] = 's32.gif';
        SmileArrays[':))'] = 's37.gif';
        SmileArrays['>:/'] = 's13.gif';
        SmileArrays['@};-'] = 's43.gif';

        SmileArrays[':-?'] = 's40.gif';
        SmileArrays['=D>'] = 's42.gif';
        SmileArrays['=d>'] = 's42.gif';
        SmileArrays[':-s'] = 's41.gif';
        SmileArrays[':S'] = 's41.gif';
        SmileArrays[':-S'] = 's41.gif';
        SmileArrays[':s'] = 's41.gif';
        SmileArrays['/:)'] = 's38.gif';
        SmileArrays['~x('] = 's36.gif';
        SmileArrays['~X('] = 's36.gif';
        SmileArrays[':x'] = 's39.gif';
        SmileArrays[':X'] = 's39.gif';
        SmileArrays[':-x'] = 's39.gif';
        SmileArrays[':-X'] = 's39.gif';
        SmileArrays['<):)'] = 's35.gif';
        SmileArrays['(:|'] = 's307.gif';
        SmileArrays[':">'] = 's90.gif';
        SmileArrays['\\:D/'] = 's69.gif';
        SmileArrays['\\:d/'] = 's69.gif';
        SmileArrays['b-('] = 's66.gif';
        SmileArrays['B-('] = 's66.gif';
        SmileArrays['[-o<'] = 's63.gif';
        SmileArrays['8-X'] = 's59.gif';
        SmileArrays['8-x'] = 's59.gif';
        SmileArrays[':-*'] = 's101.gif';
        SmileArrays['\\m/'] = 's111.gif';
        SmileArrays['\\M/'] = 's111.gif';
        SmileArrays['#-o'] = 's400.gif';
        SmileArrays['#-O'] = 's400.gif';
        SmileArrays[':-ss'] = 's402.gif';
        SmileArrays[':-SS'] = 's402.gif';
        SmileArrays[':^o'] = 's44.gif';
        SmileArrays[':^O'] = 's44.gif';
        SmileArrays[':-"'] = 's605.gif';
        SmileArrays[':O)'] = 's304.gif';
        SmileArrays[':o)'] = 's304.gif';
        SmileArrays[':)]'] = 's100.gif';
        SmileArrays['8->'] = 's105.gif';
        SmileArrays['%-('] = 's107.gif';
        SmileArrays['x_x'] = 's109.gif';
        SmileArrays['X_X'] = 's109.gif';
        SmileArrays[':('] = 's700.gif';
        // Nipoto Smileys Code
        SmileArrays['{-1-}'] = 's6.gif';
        SmileArrays['{-2-}'] = 's1.gif';
        SmileArrays['{-3-}'] = 's2.gif';
        SmileArrays['{-4-}'] = 's3.gif';
        SmileArrays['{-5-}'] = 's4.gif';
        SmileArrays['{-6-}'] = 's5.gif';
        SmileArrays['{-7-}'] = 's31.gif';
        SmileArrays['{-8-}'] = 's8.gif';
        SmileArrays['{-9-}'] = 's9.gif';
        SmileArrays['{-10-}'] = 's10.gif';
        SmileArrays['{-11-}'] = 's14.gif';
        SmileArrays['{-12-}'] = 's15.gif';
        SmileArrays['{-13-}'] = 's22.gif';
        SmileArrays['{-14-}'] = 's28.gif';
        SmileArrays['{-15-}'] = 's23.gif';
        SmileArrays['{-16-}'] = 's20.gif';
        SmileArrays['{-17-}'] = 's16.gif';
        SmileArrays['{-18-}'] = 's29.gif';
        SmileArrays['{-19-}'] = 's11.gif';
        SmileArrays['{-20-}'] = 's17.gif';
        SmileArrays['{-21-}'] = 's7.gif';
        SmileArrays['{-22-}'] = 's24.gif';
        SmileArrays['{-23-}'] = 's19.gif';
        SmileArrays['{-24-}'] = 's12.gif';
        SmileArrays['{-25-}'] = 's21.gif';
        SmileArrays['{-26-}'] = 's27.gif';
        SmileArrays['{-27-}'] = 's34.gif';
        SmileArrays['{-28-}'] = 's18.gif';
        SmileArrays['{-29-}'] = 's33.gif';
        SmileArrays['{-30-}'] = 's30.gif';
        SmileArrays['{-31-}'] = 's26.gif';
        SmileArrays['{-32-}'] = 's32.gif';
        SmileArrays['{-33-}'] = 's37.gif';
        SmileArrays['{-34-}'] = 's13.gif';
        SmileArrays['{-35-}'] = 's43.gif';

        SmileArrays['{-36-}'] = 's40.gif';
        SmileArrays['{-37-}'] = 's42.gif';
        SmileArrays['{-38-}'] = 's41.gif';
        SmileArrays['{-39-}'] = 's38.gif';
        SmileArrays['{-40-}'] = 's36.gif';
        SmileArrays['{-41-}'] = 's39.gif';
        SmileArrays['{-42-}'] = 's35.gif';
        SmileArrays['{-43-}'] = 's307.gif';
        SmileArrays['{-44-}'] = 's90.gif';
        SmileArrays['{-45-}'] = 's69.gif';
        SmileArrays['{-46-}'] = 's66.gif';
        SmileArrays['{-47-}'] = 's63.gif';
        SmileArrays['{-48-}'] = 's59.gif';
        SmileArrays['{-49-}'] = 's101.gif';
        SmileArrays['{-50-}'] = 's111.gif';
        SmileArrays['{-51-}'] = 's400.gif';
        SmileArrays['{-52-}'] = 's402.gif';
        SmileArrays['{-53-}'] = 's44.gif';
        SmileArrays['{-54-}'] = 's605.gif';
        SmileArrays['{-55-}'] = 's304.gif';
        SmileArrays['{-56-}'] = 's100.gif';
        SmileArrays['{-57-}'] = 's105.gif';
        SmileArrays['{-58-}'] = 's107.gif';
        SmileArrays['{-59-}'] = 's109.gif';
        SmileArrays['{-60-}'] = 's700.gif';



        var this_user_avatar = '<?= $C->IMG_URL ?>avatars/thumbs3/<?= $this->user->info->avatar ?>';
        var this_user_link = '<?= userlink($this->user->info->username) ?>';
        var smile_url_base = '<?= $C->IMG_URL ?>/icons/';
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
        var lastmessagedate = '<?= intval($D->lastdate) ?>';

        setInterval("get_chat_message()", 7500);

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
    </script>
<?php
    $this->load_template('footer.php');
?>