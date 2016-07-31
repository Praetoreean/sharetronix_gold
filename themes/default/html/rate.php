<!doctype html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>user rate</title>
</head>
<body>
<style>
    table.tbl1 {
        margin    : -7px 15px 0 15px;
        direction : rtl
    }

    table.tbl1 td {
        border-bottom : 1px solid #dddddd;
        padding       : 4px;
        min-width     : 40px;
        font          : 11px tahoma;
        color         : #6B6B6B;
        text-align    : justify;
    }

    table.tbl1 tr:hover td {
        background-color : #dddddd;
    }

    table.tbl1 tr th {
        color   : #FFB800;
        padding : 5px 0px;
    }

    #div_rate {
        overflow   : auto;
        max-height : 380px;
        direction  : ltr;
    }

    #user_rate_info b {
        font : normal 11px tahoma;
    }
</style>

<td>
    <div style="width: 715px; height: 300px;">
        <div style="direction:rtl; text-align:right; font: 11px tahoma;">
            <table>
                <tbody>
                <tr>
                    <td>
                        <div style="width: 340px;margin-left: 10px;font:11px tahoma">
                            <img src="<?= $C->SITE_URL . 'themes/' . $C->THEME ?>/imgs/user_rate/amar.png" style="width: 250px; height: 201px;" align="middle"><br><br><br>

                            <div id="user_rate_info" align="center">
                                امتياز کاربري : <b style="color:red"><?= $this->user->get_rate() ?></b><br>

                                رتبه کاربري : <font color="green"><b><font color="red">
                                            <? if (0 <
                                                $total_emtyaz && $total_emtyaz < 50
                                            ) {
                                                echo "تازه وارد";
                                            } elseif (50 < $total_emtyaz && $total_emtyaz < 100) {
                                                echo "تازه کار";
                                            } elseif (100 < $total_emtyaz && $total_emtyaz < 200) {
                                                echo "خودموني";
                                            } elseif (200 < $total_emtyaz && $total_emtyaz < 400) {
                                                echo "معمولي";
                                            } elseif (400 < $total_emtyaz && $total_emtyaz < 600) {
                                                echo "پرکار";
                                            } elseif (600 < $total_emtyaz && $total_emtyaz < 1000) {
                                                echo "نيمه فعال";

                                            } elseif (1000 < $total_emtyaz && $total_emtyaz < 1800) {
                                                echo "فعال";
                                            } elseif (1800 < $total_emtyaz && $total_emtyaz < 2500) {
                                                echo "نيمه حرفه اي";
                                            } elseif (2500 < $total_emtyaz && $total_emtyaz < 4000) {
                                                echo "حرفه اي";
                                            } elseif (4000 < $total_emtyaz && $total_emtyaz < 6000) {
                                                echo "نيمه پيشرفته";
                                            } elseif (6000 < $total_emtyaz && $total_emtyaz < 9500) {
                                                echo "پيشرفته";
                                            } elseif (9500 < $total_emtyaz && $total_emtyaz < 11000) {
                                                echo "از بهترين ها";
                                            } elseif (11000 < $total_emtyaz && $total_emtyaz < 5000000000) {
                                                echo "The God Of Site";
                                            } ?>
                                        </font></b></font><br>


                                <style>#shop_news ul li {
                                        padding : 5px 30px 5px 5px;
                                    }</style>


                            </div>
                        </div>
                    </td>
                    <td>
                        <div id="div_rate">
                            <table class="tbl1">
                                <tbody>
                                <tr>
                                    <th style="width: 250px;"><b>عملکرد</b></th>
                                    <th><b>تعداد</b></th>
                                    <th><b>امتياز</b></th>
                                </tr>
                                <tr>
                                    <td>ارسال پست در شبکه</td>
                                    <td style="text-align:left"><?php echo $num_postme = $D->numpostthiss; ?></td>
                                    <td style="text-align:left"><?php echo $num_postme = $D->numpostthiss * 1.5; ?></td>
                                </tr>
                                <tr>
                                    <td>دنبال کردن کاربران</td>
                                    <td style="text-align:left"><?php echo $num_floweer = $D->numflww; ?></td>
                                    <td style="text-align:left"><?php echo $num_floweer = $D->numflww * 0.4; ?></td>
                                </tr>
                                <tr>
                                    <td>دنبال شدن توسط کاربران</td>
                                    <td style="text-align:left"><?php echo $num_flowedd = $D->numflwwdd; ?></td>
                                    <td style="text-align:left"><?php echo $num_flowedd = $D->numflwwdd * 1.2; ?></td>
                                </tr>
                                <tr>
                                    <td>مديريت گروه</td>
                                    <td style="text-align:left"><?php echo $num_mg = $D->tedadegorup ?></td>
                                    <td style="text-align:left"><?php echo $num_mg = $D->tedadegorup * 1.5 ?></td>
                                </tr>
                                <tr>
                                    <td>عضويت در گروه</td>
                                    <td style="text-align:left"><?php echo $num_joing = $D->flowed_groupp ?></td>
                                    <td style="text-align:left"><?php echo $num_joing = $D->flowed_groupp * 0.6 ?></td>
                                </tr>
                                <tr>
                                    <td>نظرات ثبت شده در پست هاي شما</td>
                                    <td style="text-align:left"><?php echo $num_commoff = $D->numcommgiven; ?></td>
                                    <td style="text-align:left"><?php echo $num_commoff = $D->numcommgiven * 1.5; ?></td>
                                </tr>
                                <tr>
                                    <td>نظرات ارسالي توسط شما</td>
                                    <td style="text-align:left"><?php echo $num_comm = $D->numcomment; ?></td>
                                    <td style="text-align:left"><?php echo $num_comm = $D->numcomment * 1.2; ?></td>
                                </tr>

                                <tr>
                                    <td>تعداد لايک هاي ارسالي توسط شما</td>
                                    <td style="text-align:left"><?php echo $num_liketo = $D->numlikethis; ?></td>
                                    <td style="text-align:left"><?php echo $num_liketo = $D->numlikethis * 0.1; ?></td>
                                </tr>
                                <tr>
                                    <td>بازنشر پست‌هاي شما</td>
                                    <td style="text-align:left"><?php echo $num_reshoff = $D->numreshgiven; ?></td>
                                    <td style="text-align:left"><?php echo $num_reshoff = $D->numreshgiven * 1.5; ?></td>
                                </tr>
                                <tr>
                                    <td>بازنشر هاي شما</td>
                                    <td style="text-align:left"><?php echo $num_reshh = $D->numresh; ?></td>
                                    <td style="text-align:left"><?php echo $num_reshh = $D->numresh * 0.5; ?></td>
                                </tr>
                                <tr>
                                    <td>امتیاز ارسالی</td>
                                    <td style="text-align:left">-</td>
                                    <td style="text-align:left"><?= $this->user->info->rate_send ?></td>
                                </tr>
                                <tr>
                                    <td>امتیاز دریافتی</td>
                                    <td style="text-align:left">-</td>
                                    <td style="text-align:left"><?= $this->user->info->rate_get ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>