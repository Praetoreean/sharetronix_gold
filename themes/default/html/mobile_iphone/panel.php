<?php

    $this->load_template('mobile_iphone/header.php');

?>
    <style>
        .single-row{
            width:100%;
            background-color: #fff;
            border-bottom:solid 2px #e1e1e1;
            height:60px;
            -webkit-transition : all 0.3s;
            -moz-transition    : all 0.3s;
            -ms-transition     : all 0.3s;
            -o-transition      : all 0.3s;
            transition         : all 0.3s;
        }
        .single-row:hover{
            background-color: #f1f1f1;
            -webkit-transition : all 0.3s;
            -moz-transition    : all 0.3s;
            -ms-transition     : all 0.3s;
            -o-transition      : all 0.3s;
            transition         : all 0.3s;
        }
        .single-row .row-icon{
            float:right;
            padding:5px 0px 0px 0px;
            width:20%;
            max-width: 20%;
        }
        .single-row .row-icon i{
            font-size:35px;
            color:#390000;
            margin-top:5px;
        }
        .single-row .row-detail{
            float:right;
            text-align: right;
            max-width: 60%;
            overflow: hidden;
            margin: 12px 0px 0px 0px;
        }
        .single-row .row-detail .row-title{
            text-align: right;
            float:right;
            font-family: tahoma, Arial, Helvetica, sans-serif;
            font-size:14px;
            margin:0px;
            width:3000px;
        }
        .single-row .row-detail .row-description{
            font-family: tahoma, Arial, Helvetica, sans-serif;
            font-size: 11px;
            width:3000px;
            float:right;
        }
        .single-row .row-number{
            float:right;
            max-width: 20%;
            background-color: #440011;
            height:100%;
            width:100%;

        }
        .single-row .row-number .badge-number{
            width:100%;
            height:100%;
            margin-right:40%;
            background-color: #440011;
            margin-top:20px;
            color:#fff;
        }
    </style>
    <div class="content">

        <a href="<?= $C->SITE_URL ?>dashboard">
            <div class="single-row">
                <div class="row-icon">
                    <i class="fa fa-pencil"></i>
                </div>
                <div class="row-detail">
                    <h4 class="row-title">پست های دوستان</h4>
                    <p class="row-description">مشاهده پست های ارسالی توسط افرادی که دنبال کرده اید</p>
                </div>
                <?php if ($D->tabnums['all'] > 0 ){ ?>
                    <div class="row-number" >
                        <div align="middle">
                            <div class="badge-number"><?= $D->tabnums['all'] ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </a>
        <a href="<?= $C->SITE_URL ?>dashboard/show:@me">
            <div class="single-row">
                <div class="row-icon">
                    <i class="fa fa-user"></i>
                </div>
                <div class="row-detail">
                    <h4 class="row-title">خطاب به من</h4>
                    <p class="row-description">پست هایی که شما مورد خطاب واقع شدید</p>
                </div>
                <?php if ( $D->tabnums['@me'] > 0 ){ ?>
                    <div class="row-number" >
                        <div align="middle">
                            <div class="badge-number"><?= $D->tabnums['@me'] ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </a>
        <a href="<?= $C->SITE_URL ?>dashboard/show:private">
            <div class="single-row">
                <div class="row-icon">
                    <i class="fa fa-envelope"></i>
                </div>
                <div class="row-detail">
                    <h4 class="row-title">پیام خصوصی</h4>
                    <p class="row-description">پیام های خصوصی ارسالی برای شما</p>
                </div>
                <?php if ($D->tabnums['private'] > 0){ ?>
                    <div class="row-number" >
                        <div align="middle">
                            <div class="badge-number"><?= $D->tabnums['private'] ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </a>
        <a href="<?= $C->SITE_URL ?>dashboard/show:commented">
            <div class="single-row">
                <div class="row-icon">
                    <i class="fa fa-comment-o"></i>
                </div>
                <div class="row-detail">
                    <h4 class="row-title">نظرات</h4>
                    <p class="row-description">نظرات ارسال شده برای شما</p>
                </div>
                <?php if ($D->tabnums['commented'] > 0){ ?>
                    <div class="row-number" >
                        <div align="middle">
                            <div class="badge-number"><?= $D->tabnums['commented'] ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </a>
        <a href="<?= $C->SITE_URL ?>dashboard/show:bookmarks">
            <div class="single-row">
                <div class="row-icon">
                    <i class="fa fa-star"></i>
                </div>
                <div class="row-detail">
                    <h4 class="row-title">مورد علاقه ها</h4>
                    <p class="row-description">پست های مورد علاقه شما</p>
                </div>

            </div>
        </a>
        <a href="<?= $C->SITE_URL ?>dashboard/show:everybody">
            <div class="single-row">
                <div class="row-icon">
                    <i class="fa fa-sun-o"></i>
                </div>
                <div class="row-detail">
                    <h4 class="row-title">همه پست ها</h4>
                    <p class="row-description">مشاهده تمامی پست های ارسالی</p>
                </div>
            </div>
        </a>
        <style>
            .group_title_section{
                text-align: right;
                background-color: #e1e1e1;
                padding-top:10px;
                padding-bottom:10px;
                font-family: tahoma, Arial, Helvetica, sans-serif;
                padding-right:5px;
                font-size:12px;
                margin:0px;
            }
        </style>
        <h4 class="group_title_section">گروه های شما</h4>
        <?php foreach($D->group_detail as $v) { ?>
            <a href="<?= $C->SITE_URL.$v->group->groupname ?>">
                <div class="single-row">
                    <div class="row-icon">
                        <img src="<?= $C->IMG_URL ?>avatars/thumbs1/<?= $v->group->avatar ?>" alt="<?= $v->group->groupname ?>">
                    </div>
                    <div class="row-detail">
                        <h4 class="row-title"><?= htmlspecialchars($v->group->title) ?></h4>
                        <p class="row-description"><?= htmlspecialchars($v->group->about_me) ?></p>
                    </div>
                    <?php if ($v->num_new_post > 0){ ?>
                        <div class="row-number" >
                            <div align="middle">
                                <div class="badge-number"><?= $v->num_new_post ?></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </a>
        <?php } ?>

    </div>
</div>
<?php

	$this->load_template('mobile_iphone/footer.php');

?>