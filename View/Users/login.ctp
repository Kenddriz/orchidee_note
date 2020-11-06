
<?= $this->Form->create("User"); ?>
    <?= $this->Form->input("username", array("label" => "Identifiant", "autocomplete" => "off")); ?>
    <?= $this->Form->input("password", array("label" => "Mot de passe")); ?>
    <?=isset($msg)? '<b style="color: red">'.$msg.'</b>' : '';?>
<?= $this->Form->end("Se connecter"); ?>