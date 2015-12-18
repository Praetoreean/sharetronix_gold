<?php

    if( !$this->network->id ) {
        $this->redirect('home');
    }
    if( !$this->user->is_logged ) {
        $this->redirect('signin');
    }

    $this->load_langfile('inside/global.php');
    $this->load_langfile('inside/settings.php');


    $D->error = false;
    $D->submit = false;
    $D->errmsg = '';
    $D->page_title	= $this->lang('settings_transfer_pagetitle', array('#SITE_TITLE#'=>$C->SITE_TITLE));


    if(isset($_POST['sbm'])){
        $D->rate_much = (int)($_POST['rate_much']);
        $D->username = trim($_POST['username']);


        if($D->rate_much > $this->user->info->rate){
            $D->error =  true;
            $D->errmsg = $this->lang('settings_transfer_rate_too_much');//rate too much
        }
        if(!$D->error){
            $D->user_rate = $db2->fetch_field('SELECT rate FROM users WHERE id="'.$this->user->id.'" LIMIT 1');

            if($D->rate_much > $D->user_rate){
                $D->error =  true;
                $D->errmsg = $this->lang('settings_transfer_rate_too_much');//rate too much
            }
        }
        if(!$D->error){
            $D->uesrd = $this->network->get_user_by_username($D->username);
            if(!$D->userd){
                $D->error = true;
                $D->errmsg = $this->lang('settings_transfer_user_not_exist');//user not exist
            }
        }
        if(!$D->error){

            $db2->query('UPDATE users SET rate_send="'.$D->rate_much.'" WHERE id="'.$this->user->id.'" LIMIT 1');
            $db2->query('UPDATE users SET rate_get="'.$D->rate_get.'" WHERE id="'.$D->userd->id.'" LIMIT 1');
            $this->user->sess['LOGGED_USER']	= $this->network->get_user_by_id($this->user->id, TRUE);
            $this->user->info	= & $this->user->sess['LOGGED_USER'];
            $D->submit = true;

        }


    }


    $this->load_template('settings_transfer.php');

?>