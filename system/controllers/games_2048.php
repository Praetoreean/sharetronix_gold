<?php
    if(!$this->network->id){
        redirect('home');
    }

    $this->load_template('games_2048.php');
