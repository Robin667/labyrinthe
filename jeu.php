<?php   //connection à la BDD  en session pour pouvoir eviter de la re ecrire dans chaque fonction
session_start();
$_SESSION['mysqli'] = new mysqli("localhost", "labyrinthe", "labyrinthe", "labyrinthe");

if ($_SESSION['mysqli']->connect_errno) {
    printf("Échec de la connexion : %s\n", $_SESSION['mysqli']->connect_error);
    exit();
}

if(isset($_POST['pseudo']) AND !empty($_POST['pseudo'])) {
    $_SESSION['pseudo'] = $_POST['pseudo'];
}
// Recuperation du fichier labyrinthe.txt puis mise en tableau multidimensionnel pour affichage html
$tabLab = [];
$labyrinthe = fopen('labyrinthe.txt', 'r+');
if ($labyrinthe) {
    $a = 0;
    $b = 0;
    while (!feof($labyrinthe)) {
        $ligne = fgets($labyrinthe);
        for ($i = 0; $i < strlen($ligne); $i++) {
            if($ligne[$i] != "\n" && $ligne[$i] != " ") {
                $tabLab[$a][$b] = $ligne[$i];
                $b++;
            }
        }
        $a++;
        $b = 0;
    }
    // var_dump($tabLab);
    fclose($labyrinthe);
}

if ($reponse = $_SESSION['mysqli']->query("SELECT ligne FROM jeu")) {
    $tabLabBDD = [];
    while ($row = $reponse->fetch_assoc()) {
        $tabLabBDD[] = str_split($row['ligne']);
    }
}

function modifBDD($tabLabBDD)
{
    $y = 1;
    foreach ($tabLabBDD as $ligne) {
        $query = "TRUNCATE TABLE jeu";
        $_SESSION['mysqli']->query($query);
    }

    foreach ($tabLabBDD as $ligne) {
        $lignestr = implode($ligne);
        $query = "INSERT INTO jeu (y, ligne) VALUES (?, ?)";
        $stmt = $_SESSION['mysqli']->prepare($query);
        $stmt->bind_param("ss", $y, $lignestr);
        $stmt->execute();
        $y++;
    }
}
modifBDD($tabLabBDD);

?>

<html lang="fr">

<head>
    <meta charset="utf-8" />
    <title>Le Labyrinthe</title>
    <link href="style.css" rel="stylesheet" type="text/css" />

</head>

<body class="page">
    <div class="title">
        <h1>Salut <?php echo $_SESSION['pseudo']; ?> ! Echappe toi si tu peux ...</h1>
        <form action="" method="POST">
            <input type="text" name="pseudo" placeholder="Changer de Pseudo">
            <input type="submit" name="nickname" value="Valider le changement">
        </form>
    </div>
    <div class="restart">
        <a href="index.php">
            <button type="submit">Recommencer</button>
        </a>
    </div>
    <div class="laby">
    <table>
            <?php foreach ($tabLabBDD as $ligne) : ?>
                <tr>
                    <?php foreach ($ligne as $case) : ?>
                        <?php if ($case == 8) : ?>
                            <td class="mur">
                            <?php endif; ?>
                            <?php if ($case == 1) : ?>
                            <td class="chemin">
                            <?php endif; ?>
                            <?php if ($case == 2) : ?>
                            <td class="sortie">
                            <?php endif; ?>
                            <?php if ($case == 3) : ?>
                            <td class="perso">
                            <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="direction">
        <form method="post">
            <div class="flecheHaut"> <input type="submit" name="haut" class="button" value="haut" /></div>
            <input type="submit" name="gauche" class="button" value="gauche" />
            <input type="submit" name="bas" class="button" value="bas" />
            <input type="submit" name="droite" class="button" value="droite" />
        </form>
    </div>
</body>

</html>