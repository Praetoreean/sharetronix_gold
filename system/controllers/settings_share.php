<?php

    if( !$this->network->id ) {
        $this->redirect('home');
    }
    if( !$this->user->is_logged ) {
        $this->redirect('signin');
    }

    $this->load_langfile('inside/global.php');
    $this->load_langfile('inside/settings.php');


    $D->page_title	= "Share Settings " . $C->SITE_TITLE;



    $this->load_template('settings_share.php');

?>