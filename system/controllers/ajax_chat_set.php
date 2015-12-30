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
    $r = $c->insert($message);
    if($r>0) {
        echo 'OK:'.$r;
        return;
    }else {
        echo 'ERROR';
        return;
    }
