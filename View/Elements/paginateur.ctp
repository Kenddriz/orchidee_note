<ul class="pagination">
	<?php echo $this->paginator->first('premier',array('tag'=>'li', 'class' => 'page-item page-link',
				'style' => 'text-decoration: none;'),null, 
				 array('tag'=>'li'));
		  if($this->paginator->hasPrev())
		  	echo $this->paginator->prev(' Précédent',array('tag'=>'li','class' => 'page-item page-link',
		  		'style' => 'text-decoration: none;'),null, 
				 array('tag'=>'li'));
		  echo $this->paginator->numbers(array('separator' => '','tag'=>'li','class' => 'page-item page-link'));
		  if($this->paginator->hasNext())
		  	echo $this->paginator->next('Suivant', array('tag'=>'li','class' => 'page-item page-link', 
		  		'style' => 'text-decoration: none;'), null, 
				array('tag'=>'li'));
		  echo $this->paginator->last('Dernier',array('tag'=>'li',"class" => "page-item page-link",
				'style' => 'text-decoration: none;'),null, 
				 array('tag'=>'li'));
	?>
</ul>