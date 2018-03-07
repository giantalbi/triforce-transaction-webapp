<!DOCTYPE html>

<!--
  ______       ______       _______       ________
 /      \     /      \     |       \     |        \
|  $$$$$$\   |  $$$$$$\    | $$$$$$$\    | $$$$$$$$
| $$  | $$   | $$ __\$$    | $$__| $$    | $$__
| $$  | $$   | $$|    \    | $$    $$    | $$  \
| $$  | $$   | $$ \$$$$    | $$$$$$$\    | $$$$$
| $$__/ $$ __| $$__| $$ __ | $$  | $$ __ | $$_____
 \$$    $$|  \\$$    $$|  \| $$  | $$|  \| $$     \
  \$$$$$$  \$$ \$$$$$$  \$$ \$$   \$$ \$$ \$$$$$$$$
-->

<html>
    <head>
        <meta charset="utf-8">
        <title>O.G.R.E</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="/public/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="/public/css/jquery-te-1.4.0.css">
        <link rel="stylesheet" type="text/css" href="/public/css/jquery-ui.min.css">
        <link rel="stylesheet" type="text/css" href="/public/css/home-style.css">
        <link rel="icon" type="image/png" href="/public/img/logo.png" />
        <script type="text/javascript" src="/public/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="/public/js/jquery-te-1.4.0.min.js"></script>
        <script type="text/javascript" src="/public/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/public/js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="/public/js/home-index.js"></script>
    </head>
    <body>
            <div id="top" class="container">
                    <div class="row col-sm-offset-1"><img id="logo" class="img-responsive" src="/public/img/logo.png" alt="logo"></div>
            </div>
            <div id="main" class="container main-shadow">
                    <div id="header" class="row">
                            <nav>
                                    <a data-toggle="collapse" id="menu-toggle" class="mobile" href="#nav">
                                            <span class="glyphicon glyphicon-menu-hamburger"></span>
                                    </a>
                                    <div id="nav" class="collapse">
                                            <ul class="col-sm-10 col-sm-offset-1">
                                                    <li><a href="/Acceuil/Index"><span class="mobile glyphicon glyphicon-home"></span>Acceuil</a></li>
                                                    <li><a href="/Inventaire/Index"><span class="mobile glyphicon glyphicon-folder-open"></span>Inventaire</a></li>
                                                    <li><a href="/LevelUp/Index"><span class="mobile glyphicon glyphicon-list-alt"></span>Level Up</a></li>
                                                    <li><a href="/Membre/Index"><span class="mobile glyphicon glyphicon-user"></span>Membre</a></li>
                                                    <li><a href="/Acceuil/Info"><span class="mobile glyphicon glyphicon-question-sign"></span>À propos</a></li>
                                                    <!--Connect-->
<?php
if(isset($_SESSION["username"])){
//If a user is connected
if(isset($controller)){
    $membre = $controller->_uow->MembreRepository->getConnectedMember();
    if($membre === false)
        return;
    if($controller->_uow->MembreRepository->isMemberActive($membre->Email)){
?>
                                                                                    <li class="login-nav">
                                                                                            <span id="login-msg">Bienvenue <?=$membre->Prenom?><br> Niveau: <?=$controller->_uow->NiveauRepository->getNiveauIndexByXP(intval($membre->XP))?> XP: <?=$membre->XP?></span>
                                                                                            <a id="connect-btn" href="/Acceuil/Disconnect/">
                                                                                                    <span class=\"mobile glyphicon glyphicon-off\"></span> Déconnection
                                                                                            </a>
                                                                                    </li>
<?php
    }else{
?>
                                                                                            <li class="login-nav">
                                                                                                    <span id="login-msg">Bienvenue <?=$membre->Prenom?><br></span>
                                                                                                    <a id="connect-btn" href="/Acceuil/Disconnect/">
                                                                                                    <span class="mobile glyphicon glyphicon-off">
                                                                                                    </span> Déconnection
                                                                                                    </a>
                                                                                            </li>
<?php
    }
}
}else{
?>
                                                                    <li class="login-nav"><span id="login-msg">Membre de l'O.G.R.E ? </span><a id="connect-btn" href="/Acceuil/Connection/"><span class="mobile glyphicon glyphicon-question-sign"></span>Connection</a></li>
<?php
}
?>
                                            </ul>
                                    </div>
                            </nav>
                    </div>
                    <div class="row" style="padding-bottom: 10px;">
                    <!--Partial View-->
                    <?php echo $partial_content;?>
                    </div>
            </div>
    </body>
</html>
