<?php
$username=trim($_POST['name']??"");
$par = array(
    'input_user' => '"'.$username.'"',
);
$curlSES = curl_init();
curl_setopt($curlSES, CURLOPT_URL, "http://datascience.maths.unitn.it/ocpu/library/doexercises/R/renderResults");
curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curlSES, CURLOPT_HEADER, false);
curl_setopt($curlSES, CURLOPT_POST, true);
curl_setopt($curlSES, CURLOPT_POSTFIELDS, $par);
curl_setopt($curlSES, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($curlSES, CURLOPT_TIMEOUT, 30);

$result = curl_exec($curlSES);
$parsed = explode("\n", $result);
$flag=(sizeof($parsed)!=12);
curl_close($curlSES);
?>
<!DOCTYPE HTML>
<html lang="it">
<head>
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
    <link rel="manifest" href="images/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="images/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doexercises Graph</title>
    <link rel="stylesheet" href="css/simplegraph.min.css">
</head>
<body class="bg-primary">
<nav class="navbar fixed-top navbar-dark bg-dark mb-5 shadow pl-4">
    <a class="navbar-brand" href="#"><img src="images/favicon-32x32.png" class="pl-3 pr-4">Media e andamento voti</a>
</nav>
<div class="modal fade" id="login" tabindex="-1" role="dialog" aria-labelledby="logIn" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered " role="document">
        <div class="modal-content bg-dark rounded-0">
            <form class="d-block text-center" method="post" action="/graph">
                <div class="modal-header d-block text-center">
                    <h4 class="modal-title text-white font-weight-bolder mt-3" id="exampleModalCenterTitle">Login</h4>
                    <?=($flag && !empty($username))?'<div class="alert-danger m-2 pt-2 pb-2">Qualcosa è andato storto!</div>':''?>
                </div>
                <div class="modal-body">
                    <input type="text" id="user" name="name" placeholder="Email" required>
                    <p class="text-secondary disabled pt-2" style="font-size: 10px;">Inserisci solo il tuo username (es. mario.rossi-1)</p>
                </div>
                <div class="modal-footer d-block text-center mb-3">
                    <input type="submit" class="btn btn-outline-light" value="Log In">
                </div>
            </form>
        </div>
    </div>
</div>
<?php
if(!$flag){
    $curlSES = curl_init();
    curl_setopt($curlSES, CURLOPT_URL, "http://datascience.maths.unitn.it" . $parsed[9]);
    curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlSES, CURLOPT_HEADER, false);
    curl_setopt($curlSES, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curlSES, CURLOPT_TIMEOUT, 30);
    $result = curl_exec($curlSES);
    curl_close($curlSES);

    $dom = new DOMDocument();
    $dom->loadHtml($result);
    $x = new DOMXpath($dom);
    $i = 0;
    $arr = array();
    $id = 0;
    foreach ($x->query('//td') as $td) {
        switch ($i) {
            case 0:
                $i = 1;
                $id = (int)preg_replace('/\s/', '', $td->textContent);
                $arr['id'][$id] = $id;
                break;
            case 1:
                $i = 2;
                $data = preg_replace('/\s/', '', $td->textContent);
                $arr['data'][$id] = $data;
                break;
            case 2:
                $i = 0;
                $voto = preg_replace('/\s/', '', $td->textContent);
                $arr['voto'][$id] = round($voto,2);
                break;
        }
    }
    $media = array();
    for ($i = 1; $i <= sizeof($arr['voto']); $i++) {
        $sum = 0;
        for ($j = 1; $j <= $i; $j++) {
            $sum += $arr['voto'][$j];
        }
        $media[$i] = $sum / $i;
    }
?>
<div class="container-fluid container-md mt-5">
    <div class="row pt-4">
        <?php
        $k = 1;
        for ($j = 1; $j <= 4; $j++) {
            ?>
            <div class="col-12 col-sm-6 col-lg-3 ">
                <table class="table table-striped table-bordered table-hover table-dark mb-1 <?= ($j == 1) ? "" : "m-49" ?>">
                    <?php
                    if ($j == 1){
                    ?>
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Data</th>
                        <th scope="col">Voto</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    } else{
                    ?>
                    <tbody>
                    <?php
                    }
                    $n = $j * round(sizeof($arr['id']) / 4);
                    for ($i = $k; $i <= $n && $i <= sizeof($arr['id']); $i++) {
                        ?>
                        <tr>
                            <th scope="row" class="w-25"><?= ($arr['id'][$i])??$i; ?></th>
                            <td><?= ($arr['data'][$i])??"0"; ?></td>
                            <td class="w-25"><?= ($arr['voto'][$i])??"0"; ?></td>
                        </tr>
                        <?php
                    }
                    $k = $i;
                    ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="row mt-4">
        <div class="col-12">
            <p class="bg-dark p-3 text-center text-white font-weight-bold">Media attuale: <?=round(end($media),2)?></p>
        </div>
        <div class="col-12 mb-4">
            <canvas id="myChart"></canvas>
        </div>
    </div>
</div>
<script src="js/Chart.min.js"></script>
<script>
    var ctx = document.getElementById('myChart').getContext('2d');
    Chart.defaults.global.defaultFontColor = 'white';
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?=json_encode(array_reverse(array_values($arr['id']??null), FALSE))?>,
            datasets: [{
                label: 'Andamento Voti',
                backgroundColor: 'rgba(255, 255, 255, 0.1)',
                borderColor: 'rgb(2,121,255)',
                data: <?=json_encode(array_reverse(array_values($arr['voto']??null), FALSE))?>,
                hidden: true,
            }, {
                label: 'Media',
                backgroundColor: 'rgba(255, 255, 255, 0.1)',
                borderColor: 'rgb(73,220,0)',
                data: <?=json_encode(array_values($media??null))?>,
            }]
        },
        options: {
            legend:{
                labels:{
                    fontSize: 16,
                    fontStyle: 'bold',
                },
            },
            scales: {
                yAxes: [{
                    ticks: {
                        max: 32,
                        min: 0
                    },
                    gridLines:{
                        color: '#4e4e4e'
                    }
                }],
                xAxes: [{
                    ticks: {
                        stepSize: 2
                    },
                    gridLines:{
                        color: '#383838'
                    }
                }],

            }
        }
    });
</script>
<?php } ?>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="js/bootstrap.bundle.min.js" ></script>
<script type="text/javascript">
    <?php
    if (sizeof($parsed) != 12) {
        echo "$(window).on('load',function(){
        $('#login').modal('show');
    });";
    }?>
</script>
</body>
</html>
<?php
