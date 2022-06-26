<?php
//TODO: добавить признак ВИПа, которому будет показываться доп инфа, айпишки без звездочек, например. после оплаты криптой через метамаск
require_once 'helpers.php';
require_once 'config.php';
require_once 'db.php';

$dbconn = Db::getConnection();
if ($dbconn == null) {
    gotoPDF();
}

//TODO: че-то сделать надо с SESSION_DATA. обратно json не получить, кавычки, но влом...
if ($dbconn->query("INSERT INTO stata (session_data,
                    MMDB_ADDR,
                    GEOIP_ADDR,
                    GEOIP_COUNTRY_CODE,
                    GEOIP_COUNTRY_NAME,
                    GEOIP_REGION,
                    GEOIP_CITY,
                    GEOIP_LONGITUDE,
                    GEOIP_LATITUDE,
                    GEOIP_POSTAL_CODE,
                    HTTP_X_SERVER_ADDR,
                    HTTP_X_REAL_IP,
                    HTTP_USER_AGENT) VALUES ('" . json_encode($_SERVER) . "', '" .
                                    $_SERVER['MMDB_ADDR'] . "', '" .
                                    $_SERVER['GEOIP_ADDR'] . "', '" . 
                                    $_SERVER['GEOIP_COUNTRY_CODE'] . "', '" .
                                    $_SERVER['GEOIP_COUNTRY_NAME'] . "', '" .
                                    $_SERVER['GEOIP_REGION'] . "', '" .
                                    $_SERVER['GEOIP_CITY'] . "', '" .
                                    $_SERVER['GEOIP_LONGITUDE'] . "', '" .
                                    $_SERVER['GEOIP_LATITUDE'] . "', '" .
                                    $_SERVER['GEOIP_POSTAL_CODE'] . "', '" .
                                    $_SERVER['HTTP_X_SERVER_ADDR'] . "', '" .
                                    $_SERVER['HTTP_X_REAL_IP'] . "', '" .
                                    $_SERVER['HTTP_USER_AGENT'] . "');") !== true) {
    gotoPDF();
}
$liid = $dbconn->insert_id;
$fyiac = fuckYouIamCat();

// а заходил ли такой юзер уже? в другой день
$result = $dbconn->query("SELECT * FROM stata WHERE (GEOIP_ADDR = '" . $_SERVER['GEOIP_ADDR'] . "') AND (dt <= NOW() - INTERVAL 1 DAY) ORDER BY dt DESC LIMIT 2;");
$user = null;
if ($result->num_rows == 2) {
    $user = mysqli_fetch_assoc($result);
    $user = mysqli_fetch_assoc($result); // TODO: нужна только вторая запись по сути. переделать! не красиво!
}

// подборка юзеров для карты
$result = $dbconn->query("SELECT * FROM stata GROUP BY GEOIP_ADDR;");
//TODO: добавить в выборку доп запросом кто кликнул на резюме и длительность нахождения

$stata_lat = '';
$stata_lon = '';
$stata_ip = '';
$stata_dt = '';
$firstrun = true;

while ($tmp = mysqli_fetch_assoc($result)) {
    if (empty($tmp['GEOIP_ADDR'])) continue;
    list($ip1, $ip2, $ip3, $ip4) = explode('.', $tmp['GEOIP_ADDR']);

    $stata_dt .= ((!$firstrun)? ',': '') . "'" . date('d.m.y', strtotime($tmp['dt'])) . "'";
    if (!$fyiac) $stata_ip .= ((!$firstrun)? ',': '') . "'xxx.xxx.$ip3.$ip4'";
    else $stata_ip .= ((!$firstrun)? ',': '') . "'$ip1.$ip2.$ip3.$ip4'";
    $stata_lat .= ((!$firstrun)? ',': '') . $tmp['GEOIP_LATITUDE'];
    $stata_lon .= ((!$firstrun)? ',': '') . $tmp['GEOIP_LONGITUDE'];

    if ($firstrun) $firstrun = false;
}

// last N
$result = $dbconn->query("SELECT * FROM stata WHERE id != $liid ORDER BY id DESC LIMIT " . lastN);
$lastn = [];
while ($lastn[] = mysqli_fetch_assoc($result));
unset($lastn[lastN]);

//TODO: добавить прокрутку к таблице
//TODO: менять цвет метки на карте, если кликнул
//TODO: отображать в несколько колонок, а текст клика-неклика показывать иконкой, клик по гиту. клик по коту для котов показывать
//TODO: на карте показывать красной иконкой юзера в масштабе
//TODO: связать что ли как-то карту с табличкой. по клику переходило бы на метку и показывало доп статистику детальную
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Никачев Максим - резюме программиста</title>

    <link rel="apple-touch-icon" sizes="57x57" href="/../favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/../favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/../favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/../favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/../favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/../favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/../favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/../favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/../favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/../favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/../favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/../favicon/favicon-16x16.png">
    <link rel="manifest" href="/../favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/../favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

    <script src="https://kit.fontawesome.com/0a30e25104.js" crossorigin="anonymous"></script>

    <style>
        html, body {
            min-height: 100%;
        }
        body {
            background-image: url("cat_wide.jpg");
            background-repeat: no-repeat;
            background-position: center bottom;
            background-size: contain;
        }
        @media (min-width: 1120px), (min-height: 630px) {
            body { background-size: auto; }
        }

        .table td, .table th {
            padding: 0.15rem !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center" style="color: #777; font-size: 150%; font-weight: bold; padding-top: 35px;">
            <? if ($user === null): ?>
                Привет! Я подумал, что можно свое резюме обернуть в презанташку :)
            <? else: ?>
                Привет! Даненько тебя не видели... Аж с <?=date('d.m.Y', strtotime($user['dt']))?>! Как дела?
            <? endif; ?>
            <br />
            Тут ты можешь видеть карту уникальных посетителей и некоторую статистику...
            <!--ссылка на гитхаб-->
        </div>
        <center>
            <button class="btn btn-lg btn-success" id="button-resume">Резюме в PDF</button>
            <button class="btn btn-lg btn-dark" id="button-git"><i class="fab fa-github"></i></button>
        </center>
        <br><br>

        <div class="row d-flex justify-content-center" style="color: #777; font-size: 72%; text-align: center;">
            <div class="table-responsive"><table class="table">
                <tbody>
                    <? for ($i = 0; $i < count($lastn); $i++) :
                        list($ip1, $ip2, $ip3, $ip4) = explode('.', $lastn[$i]['GEOIP_ADDR']);?>
                    <tr>
                        <th scope="row">
                            <?
                            if (isMobile($lastn[$i]['HTTP_USER_AGENT'])) : ?>
                                <i class="fas fa-mobile-alt"></i>
                            <? else : ?>
                                <i class="fas fa-desktop"></i>
                            <? endif; ?>
                        </th>
                        <? if (!$fyiac) : ?>
                            <td>xxx.xxx.<?=$ip3?>.<?=$ip4?></td>
                        <? else : ?>
                            <td><?=$ip1?>.<?=$ip2?>.<?=$ip3?>.<?=$ip4?></td>
                        <? endif; ?>
                        <td><?=date('d.m.Y в H:i', strtotime($lastn[$i]['dt']))?></td>
                        <td><?=($lastn[$i]['resume_clicked'] == 1)? 'открыл резюме': 'не открывал резюме'?></td>
                        <td>
                            <? if ($lastn[$i]['resume_clicked'] == 1) {
                                $start_date = new DateTime($lastn[$i]['dt']);
                                $since_start = $start_date->diff(new DateTime($lastn[$i]['clicked_dt']));
                                if (($since_start->i > 3600) && ($since_start->i < 86400))
                                    echo 'долго осматривался';
                                elseif ($since_start->i >= 86400) echo 'поселился здесь';
                                else echo 'осматривался ' . $since_start->i . 'мин ' . $since_start->s . 'сек';
                            } else echo '---'; ?>
                        </td>
                        <td title="git">
                            <? if ($lastn[$i]['git_clicked'] !== null) : ?>
                                <i class="fab fa-github"></i>
                            <? endif; ?>
                        </td>
                    </tr>
                    <? endfor; ?>
                </tbody>
            </table></div>
        </div>

        <div id="map" class="row" style="width: 100%; height: 375px; padding: 0; margin: 0;"></div>
    </div>
<script>
    <? if ($fyiac) : ?>console.log('Hey, cat!');<? endif; ?>
    $(document).ready(function() {
        var myMap;
        var stata_dt = [<?=$stata_dt?>];
        var stata_ip = [<?=$stata_ip?>];
        var stata_lat = [<?=$stata_lat?>];
        var stata_lon = [<?=$stata_lon?>];

        function fillTheData() {
            for (var i = 0; i < stata_dt.length; i++) {
                var mygo = new ymaps.GeoObject({
                    geometry: {
                        type: "Point",
                        coordinates: [stata_lat[i], stata_lon[i]]
                    },
                    properties: {
                        hintContent: stata_ip[i] + '<b>|</b>' + stata_dt[i]
                    }
                }, {
                    preset: 'islands#blueCircleDotIconWithCaption',
                    draggable: false
                });

                myMap.geoObjects.add(mygo);
            }
            
            myMap.setBounds(myMap.geoObjects.getBounds());
        }

        function init () {
            myMap = new ymaps.Map('map', {
                mapStateAutoApply: true,
                center: [55.753215, 37.622504],
                zoom: 7,
                controls: []
            }, {
                buttonMaxWidth: 300
            });
            fillTheData();
        }
        ymaps.ready(init);

        $("#button-resume").click(function() {
            $.ajax({
                type: 'POST',
                url: '<?=backstage_front_url?>',
                data: 'click=<?=$liid?>&btn=resume',
                success: function (data) {
                    window.location.replace('<?=resume_url?>');
                }
            });
        });

        $("#button-git").click(function() {
            $.ajax({
                type: 'POST',
                url: '<?=backstage_front_url?>',
                data: 'click=<?=$liid?>&btn=git',
                success: function (data) {
                    window.location.replace('<?=git_url?>');
                }
            });
        });

        fuckYouIamCat = function() {
            $.ajax({
                type: 'POST',
                url: '<?=backstage_front_url?>',
                data: 'fuckyouiamcat=<?=$liid?>',
                success: function (data) {
                    window.location.reload();
                }
            });
        }
    });
</script>
</body>
</html>