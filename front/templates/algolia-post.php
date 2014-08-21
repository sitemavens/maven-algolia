<?php

use \MavenAlgolia\Core\Registry;
?>
<a class="mvn-alg-item-link" href="{permalink}">
	<?php
	if ( Registry::instance()->showThumbInPopup() ) {
		?>
		<span class="mvn-alg-item-thumbnail">
			<img class="mvn-alg-item-thumbnail-img" src="{imgSrc}" width="{imgWidth}" height="{imgHeight}" />
		</span>

		<?php
	}
	?>
	<span class="mvn-alg-item-title">{title}</span>
	<?php
	if ( Registry::instance()->showPostCategoriesInPopup() ) {
		?>
		<span class = "mvn-alg-item-cats">{categories}</span>
		<?php
	}
	if ( Registry::instance()->showExcerptInPopup() ) {
		?>
		<span class="mvn-alg-item-excerpt">{excerpt}</span>
		<?php
	}
	?>
</a>
