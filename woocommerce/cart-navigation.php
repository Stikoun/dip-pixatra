<div class="basket-steps">
	<ul>
		<?php echo is_cart() ? '<li class="active">Košík</li>' : '<li>Košík</li>' ?>
		<?php echo is_checkout() ? '<li class="active">Doprava a platba</li>' : '<li>Doprava a platba</li>' ?>
		<li>Dokončení objednávky</li>
	</ul>
</div>