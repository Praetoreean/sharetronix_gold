<?php
    if(!$this->network->id){
        echo 'ERROR';
        return;
    }
    if(!$this->user->is_logged){
        echo 'ERROR';
        return;
    }
    if(!isset($_POST['message'])){
        echo 'ERROR';
        return;
    }

    $message = trim($_POST['message']);

    if($message == '' || empty($message)) {
        echo 'ERROR';
        return;
    }

    $c = new chat();

    if($c->insert($message)>0) {
        echo 'OK';
        return;
    }else {
        echo 'ERROR';
        return;
    }
    