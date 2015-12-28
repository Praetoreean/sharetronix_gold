<?php
    if(!$this->network->id){
        echo 'ERROR';
        return;
    }
    if(!$this->user->is_logged){
        echo 'ERROR';
        return;
    }
    if($this->user->info->is_network_admin != 1){
        echo 'ERROR';
        return;
    }
    if(!isset($_POST['chat_id'])){
        echo 'ERROR';
        return;
    }

    $chat_id = intval($_POST['chat_id']);

    if($chat_id == 0){
        echo 'ERROR';
        return;
    }


    $c = new chat();

    if($c->delete_chat($chat_id)) {
        echo 'OK';
        return;
    }else {
        echo 'ERROR';
        return;
    }
