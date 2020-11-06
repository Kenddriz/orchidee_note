<div class="d-flex justify-content-around">
	<div>			
		<form method="post" id="niveau_ajout">
			<table class="table">
				<thead>
					<tr><th colspan="2" class="text-center text-secondary">Ajouter un niveau</th></tr>
					<tr id="msg_ajout"></tr>
				</thead>
				<tbody>
					<!--<tr>
						<td>Code:</td>
						<td>
							<input type="number" name="code_niv" class="form-control" min='1'>	
						</td>
					</tr>-->
					<tr>
						<td>Désignation:</td>
						<td>
							<input type="text" name="libelle_niv" class="form-control" autocomplete="off" placeholder='exemple: Lycée'>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="submit" value="Valider" class="form-control btn-outline-secondary">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<div>
		<div class="jumbotron jumbotron-fluid table-responsive">
			<table class="text-center table small" id="table_niv">
				<thead>
					<!--<tr>
						<th colspan="3" class="text-secondary">
							<input type="text" placeholder="filtrer" autocomplete="off" class="form-control form-control-sm ifilter">
						</th>
					</tr>-->
					<tr id="msg_mis_a_jour"></tr>
					<tr>
						<td></td>
						<td>Numéro</td>
						<td>
						Libellé<?php echo $this->Html->image('../img/edit-class-.ico',
								array('width'=> 20, 'height'=> 20)); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($preparation[0] as $code => $libelle):;?>
						<tr>
							<td>
								<a href="javaScript:Void(0)" class="text-success lien_supp">supprimer</a>
							</td>
							<td><?= $code;?></td>
							<td contenteditable="true"><?= $libelle;?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>			
	</div>
</div>
<script type="text/javascript">

	//ajout niveau
$("#niveau_ajout").submit(function(event){
    event.preventDefault(); //prevent default action 
    $.ajax({
        url : '<?php echo $this->request->base; ?>/Niveaus/ajouter',
        type: 'POST',
        data : new FormData(this),
		contentType: false,
		cache: false,
		processData:false
    }).done(function(response){ //
        $("#msg_ajout").html(response);
         window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#niveau');
        setTimeout(function(){location.reload()}, 2000);
        //alert(response);
    });
});
//Mettre à jour niveau
$('#table_niv tbody td').on('keypress', function(event){

	if(event.which == 13){
		//alert($(this).html());

		$.post("<?php echo $this->request->base; ?>/Niveaus/mettre_jour",
			{
				code_niv: $(this).closest('tr').find('td:eq(1)').text(),
				libelle_niv: $(this).html()
			}, 
			function(data){
				$('#msg_mis_a_jour').html(data);
				window.location.replace('<?php echo $this->request->base; ?>/Preparations/index#niveau');
		        setTimeout(function(){location.reload()}, 2000);
		});
		return false;
	}
});
//Suppression
$('#table_niv tbody').on('click', '.lien_supp', function(){
	//alert($(this).closest('tr').find('td:eq(1)').html().trim());
	var x = $(this).closest('tr');
	$.post("<?php echo $this->request->base; ?>/Niveaus/supprimer",
			{
				code_niv: $(this).closest('tr').find('td:eq(1)').text().trim(),
			}, 
			function(data){
				if(data == 1)x.remove();
				else alert(data);
		});
})
</script>