<?php
if(isset($_POST['phrase'])) {
$phrase = $_POST['phrase'];
    preg_match_all('/\{(.*?)\}/', $_POST['phrase'], $matches, PREG_SET_ORDER);
    $count = sizeof($matches);
    $tabword = array();
foreach($matches as $match) {
    $tabString = explode("|", $match[1]);
    $indice = array_rand($tabString);
    array_push($tabword, $tabString[$indice]);
}
    foreach($tabword as $word) {
        preg_match('/\{(.*?)\}/', $phrase, $matches);

        $pattern = "/".$matches[0]."/";
        $pattern = str_replace("|","\\|",$pattern);
        $phrase = preg_replace($pattern,$word,$phrase);
    }
}
    ?>
    <!DOCTYPE HTML>
    <html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    </head>
    <body>
    <h2>Exercice n°1</h2>

    <form action="index.php" method="post">
        <label for="phrase">Phrase en entrée : </label>
        <input type="text" name="phrase" id="phrase" size="100" value="<?php echo $_POST['phrase']; ?>">
        <button type="submit" class="btn btn-primary">Valider</button>
    </form>
<h3>Résultats : <?php echo isset($phrase) ? $phrase : ""; ?> </h3>
    </body>
    </html>


