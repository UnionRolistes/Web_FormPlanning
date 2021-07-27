<?php
//Fenetre popup sur un clic d'une partie. Affiche tous les détails de la partie ainsi que 2 liens vers le message Discord et pour télécharger le Ics de l'événement
if (!isset($_GET['ID']) || !is_numeric($_GET['ID'])){
    header('Location:../index.php');
}

$ID=$_GET['ID'];


//On ouvre le Xml :        
if (!file_exists("../data/events.xml")) {
    exit('Echec lors de la récupération des parties');
}
$xml = simplexml_load_file('../data/events.xml');

//(Pas trouvé de fonction "find by id" qui fonctionne bien. Et dans les 2 cas ca revient à un parcours de xml, donc on ne perd pas en optimisation)
$trouve=false;
foreach ($xml->partie as $partie) {

    if ($partie->attributes()==$ID){
        $trouve=true;

        $titre=$partie->titre;
        $capacite=$partie->capacite;
        $minimum=$partie->minimum;
        $inscrits=$partie->inscrits;
        $date=new DateTime($partie->date); //On choisit DateTime face à DateTimeImmutable pour un ajout des heures plus simples
        $heure=$partie->heure;
        $duree=$partie->duree;
        $type=$partie->type;
        $MJ=$partie->mj;
        $systeme=$partie->systeme;
        $pjMineur=$partie->pjMineur;
        $plateformes=$partie->plateformes; 
        $details=$partie->details;
        $lienWeb=$partie->lien;
    
        $splitLienWeb = explode("/channels", $lienWeb); //Sépare dans un tableau la partie avant et après /channels 
        $splitLienWeb[0]="discord://discordapp.com";//On remplace le début pour avoir un lien vers l'appli de bureau
        $lienDesktop=$splitLienWeb[0]."/channels".$splitLienWeb[1];

        break;
    }
}
if(!$trouve){
    echo 'Erreur, partie introuvable';
    exit;
}

?>


<!--Partie affichage : -->
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$titre?></title>

    <link rel="stylesheet" href="../../css/master.css">
    <link rel="stylesheet" href="../../css/styleDark.css">
    <link rel="stylesheet" href="../css/stylePopup.css">

    <link rel="icon" type="image/png" href="../../img/ur-bl2.png">
</head>
<body>
    
    <header>
        <h1 class="titleCenter"><?=$titre?></h2>
    </header>
    <section id="URform">
   
        <label><strong>Type : </strong></label>
        <div class="right"><?=$type?></div>

        <label><strong>Systeme : </strong></label>
        <div class="right"><?=$systeme?></div>
              
        <label><strong>Date et heure : </strong></label>
        <div class="right">Le <?=$date->format("d/m")?> à <?=$heure?></div>

        <label><strong>Durée : </strong></label>
        <div class="right"><?=$duree?></div>

        <label><strong>Capacité : </strong></label>
        <div class="right">Entre <strong><?=$minimum?> et <?=$capacite?></strong> joueurs <!--- <=$inscrits?> joueurs inscrits --></div>

        <label><strong>Mineurs : </strong></label>
        <div class="right"><?=$pjMineur?></div>

        <label><strong>MJ de la partie : </strong></label>
        <div class="right"><?=$MJ?></div>

        <label><strong>Description : </strong></label>
        <div class="right"><?=$details?></div>

        <label><strong>Plateformes : </strong></label>
        <div class="right"><?=$plateformes?></div>


        <fieldset>
            <legend>Inscriptions</legend>

<!--Optimisable plus tard en faisant des jolis boutons au lieu de passer par 2 form inutiles ici (les boutons héritant du css du formulaire, ils ont design qui ne convient pas ici. Il faudrait que cette page ait son propre css unique-->
            <form method="post" id="firstButton" action="<?=$lienWeb?>">
                <input type="submit" value="M'inscrire (Discord Web)">
            </form>

            <form method="post" action="<?=$lienDesktop?>">
                <input type="submit" value="M'inscrire (Discord Bureau)">
            </form>
            
        <?php  
            $heure=explode("h", "$partie->heure");
            if($heure[1]==""){
                $heure[1]=0;
            }
            $duree=explode("h", "$partie->duree");
            if($duree[1]==""){
                $duree[1]=0;
            }
            $date->setTime($heure[0], $heure[1]);

            //GMT -2 pour la France. Autres GMT à gérer
            $date->add(date_interval_create_from_date_string('-2 hours'));

            //echo $date->format("Y-m-d H:i"); ?>

            <form method="post" action="../php/download-ics.php">
                <input type="hidden" name="date_start" value="<?=$date->format("Y-m-d H:i")?>">
                <?php $dateFin=$date->add(date_interval_create_from_date_string('+'.$duree[0].' hours '.$duree[1].' minutes'));//echo $dateFin->format("Y-m-d H:i");?>
                
                <input type="hidden" name="date_end" value="<?=$dateFin->format("Y-m-d H:i")?>">
                <input type="hidden" name="location" value="Union des Rôlistes">
                <input type="hidden" name="description" value="<?=$partie->details?>">
                <input type="hidden" name="summary" value="<?=$partie->titre?>">
                <input type="submit" value="Ajouter à mon agenda">
            </form>
        </fieldset>
    </section>

</body>
<?php include('../../php/footer.php'); ?>
</html>