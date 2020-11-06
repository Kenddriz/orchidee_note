<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="<?php echo $this->request->base; ?>/img/logo.ico" type="image/x-icon" />
    <title>Gestion des notes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $this->html->css('user'); ?>
</head>
<body>
    <img class="image1" src="<?php echo $this->request->base.'/img/undraw_back_to_school_inwc.svg' ;?>">
    <div class="flex-container">
        <div class="menu_accueil">
            <span>&#9776;&nbsp; &nbsp;Menu</span>
            <a href="<?php echo $this->request->base; ?>/Users/index"><button>Accueil</button></a>
            <a href="<?php echo $this->request->base; ?>/Users/inscription"><button>Inscription</button></a>
            <a href="<?php echo $this->request->base; ?>/Users/login"><button>Connexion</button></a>
        </div>
        <span style="width: 20px;"></span>
        <?= $this->fetch('content'); ?> 
    </div> 
     <img class="image2" src="<?php echo $this->request->base.'/img/undraw_studying_s3l7.svg' ;?>"> 
</body>
</html>