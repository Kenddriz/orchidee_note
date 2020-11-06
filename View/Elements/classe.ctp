<div class="d-flex justify-content-around">
	<form id="ajout_classe">
		<table class="table">
			<thead>
				<tr><th colspan="2" class="text-center text-secondary">Nouvelle classe</th></tr>
				<tr id="msg_ajout_classe"></tr>
			</thead>
			<tbody>
				<tr>
					<td>Désignation:</td>
					<td>
						<input type="text" name="des_classe" autocomplete="off" 
						placeholder="Ex: 11ème" class="form-control form-control-sm">	
					</td>
				</tr>
				<tr>
					<td>Niveau:</td>
					<td>
						<select class="form-control form-control-sm recherche_select" name="code_niv">
							<?php foreach($preparation[0] as $code => $libelle):;?>
								<option value="<?= $code;?>"><?= $libelle;?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" value="Enregistrer" class="form-control btn-outline-secondary form-control-sm">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<!--Liste---->
	<div>
		<table class="table small bg-primary">
			<thead>
				<tr>
					<th>Action</th>
					<th>Classe</th>
				</tr>
			</thead>
		</table>
		<div style="max-height: 45vh; overflow: auto;">
			<table class="table small tfilter">
			<tbody>
				<?php foreach($preparation[2] as $classe):;?>
					<tr>
						<td>
							<a href="#classe" class="lien_supp_classe" data-toggle='tooltip' title='supprimer'>
								<img src="../img/annee_scolaire.ico" height="10" width="10">
							</a>
						</td>
						<td class="<?= $couleur[rand(0,4)];?>"><?= $classe;?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			</table>	
		</div>
		<table>
			<tfoot>
				<form>
					<tr>
						<td>
							<input type="text" placeholder="recherche" autocomplete="off"
							class="form-control form-control-sm ifilter">
						</td>
					</tr>
				</form>
			</tfoot>
		</table>
	</div>
</div>
<script type="text/javascript">
	//ajout niveau
$("#ajout_classe").submit(function(event){
    event.preventDefault(); //prevent default action 
    $.ajax({
        url : '<?php echo $this->request->base; ?>/Classers/ajouter',
        type: 'POST',
        data : new FormData(this),
		contentType: false,
		cache: false,
		processData:false
    }).done(function(response){ //
        $("#msg_ajout_classe").html(response);
        window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#classe');
        setTimeout(function(){location.reload()}, 500);
        //alert(response);
    });
});

//Suppression
$('.tfilter tbody').on('click', '.lien_supp_classe', function(e){
	e.preventDefault();
	
	var x = $(this).closest('tr');

	$.post("<?php echo $this->request->base; ?>/Classers/supprimer",
			{
				des_classe: $(this).closest('tr').find('td:eq(1)').text().trim(),
			}, 
			function(data){
				if(data == 1)x.remove();
				else alert(data);
				window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#classe');
			});
})
</script>