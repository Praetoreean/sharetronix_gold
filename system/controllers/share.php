<?php
    if(!$this->network->id){
        $this->redirect('home');
    }
    $this->load_langfile('inside/global.php');
    $D->page_title = 'Share Post | '.$C->SITE_TITLE;

    $title = isset($_GET['title'])  ? (urldecode(trim($_GET['title']))) : false;
    $url = isset($_GET['url'])  ? (urldecode(trim($_GET['url']))) : false;

    $is_complete = true;

    if($title == false || $url == false){
        $is_complete = false;
    }

    $D->error = false;


    if(isset($_POST['sbm'])){
        $D->title = trim($_POST['title']);
        $D->url = trim($_POST['url']);
        $D->detail = trim($_POST['detail']);
        $p = new newpost();
        $p->set_api_id(12);
        $p->set_message($D->title."\n".$D->detail);
        $p->attach_link($D->url);
        $r = $p->save();
        if($r == true){
            $this->redirect($C->SITE_URL .'dashboard');
        }else{
            $D->error = true;
        }

    }

    if($is_complete != false){
        if(!$this->user->is_logged){
            $this->redirect('signin?location=share&title='.base64_encode(urlencode($title)).'&url='.base64_encode(urlencode($url)));
        }
        if(isset($_GET['ref']) && trim($_GET['ref']) == 'signin'){
            $D->title = base64_decode(urldecode(trim($_GET['title'])));
            $D->url = base64_decode(urldecode(trim($_GET['url'])));
        }else{
            $D->title = (urldecode(trim($_GET['title'])));
            $D->url = (urldecode(trim($_GET['url'])));
        }
    }


    $D->is_complete = $is_complete;
    $this->load_template('share.php');
?>