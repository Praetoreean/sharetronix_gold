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
    $_reply_to = false;
    if(isset($_POST['reply_to']) && intval($_POST['reply_to']) > 0){
        $_reply_to = intval($_POST['reply_to']);
    }

    $c = new chat();
    $r = $c->insert($message,false,false,$_reply_to);
    if($r>0) {
        echo 'OK:'.$r;
        return;
    }else {
        echo 'ERROR';
        return;
    }
