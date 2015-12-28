<?php
    if(!$this->network->id){
        $this->redirect('home');
    }
    if(!$this->user->is_logged){
        $this->redirect('signin');
    }

    $this->load_langfile('inside/global.php');
    $this->load_langfile('inside/chat.php');

    $D->page_title = $this->lang('chat_room_page_title');


    $lastdate = time() - 24*60*60;

    $c = new chat();

    $data = $c->get_message($lastdate,50);

    $chat = $data[1];
    $D->lastdate = $data[0];
    $D->chats = $chat;

    $this->load_template('chat.php');
?>


