<?php

    if( !$this->user->is_logged ) {
        $this->redirect('home');
    }
    if( $C->MOBI_DISABLED ) {
        $this->redirect('mobidisabled');
    }

    $this->load_langfile('mobile/global.php');
    $this->load_langfile('mobile/dashboard.php');

    $shows		= array('all', '@me', 'private', 'commented', 'bookmarks', 'everybody');
    $tabnums		= array('all', '@me', 'private', 'commented');

    $tabnums	= $this->network->get_dashboard_tabstate($this->user->id, $tabnums);
    $D->tabnums	= $tabnums;


    $D->group_detail = array();
    $r = $db2->query('SELECT group_id,new_post FROM groups_followed WHERE user_id = "'.$this->user->id.'"');
    while($o = $db2->fetch_object($r)){
        $tmp = new stdClass();
        $tmp->id = $o->group_id;
        $tmp->group = $this->network->get_group_by_id($o->group_id);
        $tmp->num_new_post = $o->new_post;
        $D->group_detail[] = $tmp;
    }


    $this->load_template('mobile_iphone/panel.php');

?>