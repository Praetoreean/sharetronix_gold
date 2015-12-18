<?php

    if (!$this->network->id) {
        $this->redirect('home');
    }
    if (!$this->user->is_logged) {
        $this->redirect('signin');
    }


    $emtga = $db2->query('SELECT user_id FROM `groups_admins` WHERE user_id=' . $this->user->id . '');
    $D->tedadegorup = $db2->num_rows($emtga);
    $flowed_grouppselect = $db2->query('SELECT user_id FROM `groups_followed` WHERE user_id=' . $this->user->id . '');
    $D->flowed_groupp = $db2->num_rows($flowed_grouppselect);
    $numlikethisselect = $db2->query('SELECT user_id FROM `post_likes` WHERE user_id=' . $this->user->id . '');
    $D->numlikethis = $db2->num_rows($numlikethisselect);
    $selectidthisresh = $db2->query('SELECT reshares FROM `posts` WHERE user_id =' . $this->user->id . '');
    $resharesnum = 0;
    while ($numidthisresh = $db2->fetch_object($selectidthisresh)) {
        $resh = $numidthisresh->reshares;
        $resharesnum = $resharesnum + $resh;
    }

    $D->numreshgiven = $resharesnum;
    $selectidpostthis = $db2->query('SELECT id FROM `posts` WHERE user_id =' . $this->user->id . '');
    $numpostthis = $db2->num_rows($selectidpostthis);
    $D->numpostthiss = $numpostthis;
    $floweerss = $db2->query('SELECT who FROM `users_followed` WHERE who =' . $this->user->id . '');
    $numfloweers = $db2->num_rows($floweerss);
    $D->numflww = $numfloweers;
    $floweedd = $db2->query('SELECT who FROM `users_followed` WHERE whom =' . $this->user->id . '');
    $numfloweedd = $db2->num_rows($floweedd);
    $D->numflwwdd = $numfloweedd;
    $commentmee = $db2->query('SELECT user_id FROM `posts_comments` WHERE user_id =' . $this->user->id . '');
    $numcommentmee = $db2->num_rows($commentmee);
    $D->numcomment = $numcommentmee;
    $selectidthiscomm = $db2->query('SELECT comments FROM `posts` WHERE user_id =' . $this->user->id . '');
    $commentsnum = 0;
    while ($numidthiscomm = $db2->fetch_object($selectidthiscomm)) {
        $comm = $numidthiscomm->comments;
        $commentsnum = $commentsnum + $comm;
    }

    $D->numcommgiven = $commentsnum;

    $selectreshares = $db2->query('SELECT user_id FROM `posts_reshares` WHERE user_id =' . $this->user->id . '');
    $numresharess = $db2->num_rows($selectreshares);
    $D->numresh = $numresharess;


    $num_mg = $D->tedadegorup;
    $emtyaz_mg = $num_mg * 1.5;
    ////////////////

    ////////////////
    $num_reshoff = $D->numreshgiven;
    $numreshtomee = $num_reshoff * 1.5;
    ////////////////
    ////////////////
    $num_commoff = $D->numcommgiven;
    $numcommtomee = $num_commoff * 1.5;
    ////////////////
    ////////////////
    $num_joing = $D->flowed_groupp;
    $emtyaz_joing = $num_joing * 0.6;
    ////////////////
    ////////////////
    $num_liketo = $D->numlikethis;
    $emtyaz_liketo = $num_liketo * 0.1;
    ////////////////
    ////////////////
    $num_postme = $D->numpostthiss;
    $emtyaz_postme = $num_postme * 1.5;
    ////////////////
    ////////////////

    $num_flowedd = $D->numflwwdd;
    $emtyaz_floweer = $num_flowedd * 1.2;

    ////////////////
    $num_floweer = $D->numflww;
    $emtyaz_flower = $num_floweer * 0.4;
    ////////////////
    $num_comm = $D->numcomment;
    $emtyaz_comm = $num_comm * 1.2;

    ////////////////
    $num_reshh = $D->numresh;
    $emtyaz_resh = $num_reshh * 0.5;
    ////////////////



    ///////////////////////////////////مجموع امتيازات///////////////////////////////////////////////////
    $total_emtyaz = $emtyaz_mg + $numreshtomee + $numcommtomee
        + $emtyaz_joing + $emtyaz_liketo + $emtyaz_postme + $emtyaz_floweer + $emtyaz_flower + $emtyaz_comm + $emtyaz_resh;
    $D->totalemtyazz = @round($total_emtyaz);

    ///////////////////////////////////مجموع امتيازات///////////////////////////////////////////////////
    if (0 <
        $total_emtyaz && $total_emtyaz < 50
    ) {
        $D->vaziat = 'تازه وارد';
    } elseif ($total_emtyaz == 0) {
        $D->vaziat = 'تازه وارد';
    } elseif (50 < $total_emtyaz && $total_emtyaz < 100) {
        $D->vaziat = 'تازه کار';
    } elseif (100 < $total_emtyaz && $total_emtyaz < 200) {
        $D->vaziat = 'خودمونی';
    } elseif (200 < $total_emtyaz && $total_emtyaz < 400) {
        $D->vaziat = 'معمولی';
    } elseif (400 < $total_emtyaz && $total_emtyaz < 600) {
        $D->vaziat = 'پرکار';
    } elseif (600 < $total_emtyaz && $total_emtyaz < 1000) {
        $D->vaziat = 'نیمه فعال';
    } elseif (1000 < $total_emtyaz && $total_emtyaz < 1800) {
        $D->vaziat = 'فعال';
    } elseif (1800 < $total_emtyaz && $total_emtyaz < 2500) {
        $D->vaziat = 'نیمه حرفه ای';
    } elseif (2500 < $total_emtyaz && $total_emtyaz < 4000) {
        $D->vaziat = 'حرفه ای';
    } elseif (4000 < $total_emtyaz && $total_emtyaz < 6000) {
        $D->vaziat = 'نیمه پیشرفته';
    } elseif (6000 < $total_emtyaz && $total_emtyaz < 9500) {
        $D->vaziat = 'پیشرفته';
    } elseif (9500 < $total_emtyaz && $total_emtyaz < 11000) {
        $D->vaziat = 'از بهترین ها';
    } elseif (11000 < $total_emtyaz && $total_emtyaz < 5000000000) {
        $D->vaziat = 'The God Of Site';
    }


    $u = $this->network->get_user_by_id(($this->params->user));
    $D->usr = &$u;
    $D->modes = '';
    if (($D->modes == '')) {
        $db2->query('UPDATE `users` SET `rate` = "' . $db2->e($D->totalemtyazz) . '",`vaziat` = "' . $db2->e($D->vaziat) . '"  WHERE id="' . $u->id . '" LIMIT 1');

    }


    $db2->query('SELECT * FROM users WHERE username="' . $D->usr->username . '" LIMIT 1 ');
    if ($tmp = $db2->fetch_object()) {
        $D->rate = stripslashes($tmp->rate);
        $D->vaziat = stripslashes($tmp->vaziat);

    }


    $this->load_template('rate.php');

?>