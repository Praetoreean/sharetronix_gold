<?php
    if(!$this->network->id){
        echo 'ERROR';
        return;
    }
    if(!$this->user->is_logged){
        echo 'ERROR';
        return;
    }
    if(!isset($_POST['lastdate'])){
        echo 'ERROR';
        return;
    }
    $lastdate = (int)($_POST['lastdate']);
    if($lastdate == 0){

        echo 'ERROR';
        return;
    }

    $c = new chat();

    $data = $c->get_message($lastdate,false,true);

    $lastdate = $data[0];
    $chat = json_encode($data[1]);

    echo 'OK:'.$lastdate.':'.$chat;
    return;
