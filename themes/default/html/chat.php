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
            width            : 330px;
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
                            سیستم چت همکلاسیها(آزمایشی)
                            <span class="online">افراد حاضر:- نفر</span>
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
    <script type="text/javascript">
        $(document).ready(function () {
            $('#chat_message').keypress(function (e) {
                if (e.which == 13) {
                    send_chat_message();
                }
            });

        });

        var this_user_avatar = '<?= $C->IMG_URL ?>avatars/thumbs3/<?= $this->user->info->avatar ?>';
        var this_user_link = '<?= userlink($this->user->info->username) ?>';
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
            html += '<div class="message_box right" id="message_box_' + randNumber + '" style="opacity: 0.4;"><div class="avatar"><img src="';
            html += this_user_avatar;
            html += '"></div><div class="message me">';
            html += message;
            html += '</div></div>حذف<div class="klear"></div>';


            $('#content_chat').append(html);

            scrollTop = parseInt($('#content_chat_total').scrollTop());
            $('#content_chat_total').scrollTop(scrollTop + 50);

            $.post(siteurl + 'ajax/chat/set/r:' + Math.round(Math.random() * 1000), {
                message: message
            }, function (data) {
                if (data == 'OK') {
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
                        html += '<div class="message_box left"><div class="avatar"><img src="';
                        html += obj[i].avatar;
                        html += '"></div><div class="message other">';
                        html += obj[i].message;
                        html += '</div></div><div class="klear"></div>';

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