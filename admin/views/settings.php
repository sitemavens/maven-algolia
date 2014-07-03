<?php

use MavenAlgolia\Admin\Controllers\Settings;
use MavenAlgolia\Core;

$registry = Core\Registry::instance();
$langDomain = $registry->getPluginShortName();
?>
<div id="mvnAlgoliaSettings" class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Maven Algolia Settings</h2>
	<form action="" method="post">
		<div id="mvnAlgtabs">
			<ul>
				<li><a class="nav-tab nav-tab-active" href="#tab-general"><?php esc_html_e('Account', $langDomain); ?></a></li>
				<li><a class="nav-tab" href="#tab-customization"><?php esc_html_e('Cutomization', $langDomain); ?></a></li>
			</ul>
			<div id="tab-customization">
				<table class="wrap">
					<tbody>
						<tr>
							<td style="width: 45%" valign="top">
								<table class="widefat">
									<thead>
										<tr>
											<th class="row-title" colspan="2"><strong><?php esc_html_e('Taxonomies', $langDomain); ?></strong></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<label for="mvnAlg_indexTaxonomies"><?php esc_html_e('Check if you want to index taxonomies', $langDomain); ?></label>
												<input type="hidden" value="0" name="<?php echo Settings::settingsField; ?>[indexTaxonomies]">
												<input type="checkbox" class="checkbox" <?php checked($registry->indexTaxonomies()); ?> value="1" id="mvnAlg_indexTaxonomies" name="<?php echo Settings::settingsField; ?>[indexTaxonomies]">
											</td>
										</tr>
										<tr>
											<th scope="row">
												<?php esc_html_e('This is the list of Taxonomies that would be indexed, please remember that each taxonomy will have its own index name and they will appear separately in the "suggestions search" popup.', $langDomain); ?><br>
												<?php printf( '<strong>%s:</strong> %s', esc_html__('Important', $langDomain), esc_html__('when you enable index tanomies option you should reindex all the content.', $langDomain) ); ?><br>
												<ul><?php
													$taxonomiesToIndex = Core\FieldsHelper::getTaxonomyObjects();
													if ($taxonomiesToIndex):
														$taxonomiesLabels = Core\FieldsHelper::getTaxonomyLabels();
														foreach ($taxonomiesToIndex as $taxKey => $tax) :
															?>
															<li><?php echo sprintf('<strong>%s</strong>: %s <br> <strong>%s</strong>: %s', __('Taxonomy'), $taxonomiesLabels[$taxKey], __('Index Name'), $tax->getIndexName()); ?></li>
															<?php
														endforeach;
													endif;
													?>
												</ul>
											</th>
										</tr>
									</tbody>
								</table>
							</td>
							<td style="width: 45%" valign="top">
								<table class="widefat">
									<thead>
										<tr>
											<th class="row-title" colspan="2"><strong><?php esc_html_e('General', $langDomain); ?></strong></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<label for="mvnAlg_showPostCategoriesInPopup"><?php esc_html_e('Show Post Categories in search results', $langDomain); ?></label>
											</td>
											<td>
												<input type="hidden" value="0" name="<?php echo Settings::settingsField; ?>[showPostCategoriesInPopup]">
												<input type="checkbox" class="checkbox" <?php checked($registry->showPostCategoriesInPopup()); ?> value="1" id="mvnAlg_showPostCategoriesInPopup" name="<?php echo Settings::settingsField; ?>[showPostCategoriesInPopup]">
											</td>
										</tr>
										<tr>
											<td>
												<label for="mvnAlg_showExcerptInPopup"><?php esc_html_e('Show Excerpt in search results', $langDomain); ?></label>
											</td>
											<td>
												<input type="hidden" value="0" name="<?php echo Settings::settingsField; ?>[showExcerptInPopup]">
												<input type="checkbox" class="checkbox" <?php checked($registry->showExcerptInPopup()); ?> value="1" id="mvnAlg_showExcerptInPopup" name="<?php echo Settings::settingsField; ?>[showExcerptInPopup]">
											</td>
										</tr>
										<tr id="escerptSize">
											<td>
												<?php esc_html_e('Max number of characters to show in excerpt', $langDomain); ?>
											</td>
											<td>
												<input type="text" class="" value="<?php echo esc_attr($registry->getExcerptMaxChars()); ?>" id="mvnAlg_excerptMaxChars" name="<?php echo Settings::settingsField; ?>[excerptMaxChars]"> <br> <em><?php esc_html_e( '0 to show it entirely', $langDomain ) ?></em>
											</td>
										</tr>
										<tr>
											<td>
												<label for="mvnAlg_showThumbInPopup"><?php esc_html_e('Show Thumbnails in search results', $langDomain); ?></label>
											</td>
											<td>
												<input type="hidden" value="0" name="<?php echo Settings::settingsField; ?>[showThumbInPopup]">
												<input type="checkbox" class="checkbox" <?php checked($registry->showThumbInPopup()); ?> value="1" id="mvnAlg_showThumbInPopup" name="<?php echo Settings::settingsField; ?>[showThumbInPopup]">
											</td>
										</tr>
										<tr id="thumbSizes">
											<td>
												<?php esc_html_e('Thumbnail Width', $langDomain); ?>
												<br>
												<?php esc_html_e('Thumbnail Height', $langDomain); ?>
											</td>
											<td>
												<?php 
												$popupThumbArgs = $registry->getPopupThumbnailArgs();
												?>
												<input type="text" class="" value="<?php echo esc_attr($popupThumbArgs['w']); ?>" id="mvnAlg_popupThumbWidth" name="<?php echo Settings::settingsField; ?>[popupThumbnailArgs][w]">px
												<br>
												<input type="text" class="" value="<?php echo esc_attr($popupThumbArgs['h']); ?>" id="mvnAlg_popupThumbHeight" name="<?php echo Settings::settingsField; ?>[popupThumbnailArgs][h]">px
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>

						<tr>
							<td>
								<p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes', $langDomain); ?>" class="button button-primary" id="submit" name="submit"></p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="tab-general">
				<input type="hidden" value="<?php echo Settings::updateAction; ?>" name="mvnAlg_action">
				<?php wp_nonce_field(Settings::updateAction); ?>
				<table style="width: 50%" class="widefat">
					<thead>
						<tr>
							<th class="row-title" colspan="2"><strong><?php esc_html_e('Configure your App Credentials', $langDomain); ?></strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th scope="row"><label for="mvnAlg_appId"><?php esc_html_e('APP ID', $langDomain); ?></label></th>
							<td><input type="text" class="regular-text" value="<?php echo esc_attr($registry->getAppId()); ?>" id="mvnAlg_appId" name="<?php echo Settings::settingsField; ?>[appId]"></td>
						</tr>
						<tr>
							<th scope="row"><label for="mvnAlg_apiKey"><?php esc_html_e('API Key', $langDomain); ?></label></th>
							<td><input type="text" class="regular-text" value="<?php echo esc_attr($registry->getApiKey()); ?>" id="mvnAlg_apiKey" name="<?php echo Settings::settingsField; ?>[apiKey]"></td>
						</tr>
						<tr>
							<th scope="row"><label for="mvnAlg_apiKeySearch"><?php esc_html_e('API Key for Search Only', $langDomain); ?></label></th>
							<td><input type="text" class="regular-text" value="<?php echo esc_attr($registry->getApiKeySearch()); ?>" id="mvnAlg_apiKeySearch" name="<?php echo Settings::settingsField; ?>[apiKeySearch]"></td>
						</tr>
						<tr>
							<td>
								<p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes', $langDomain); ?>" class="button button-primary" id="submit" name="submit"></p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php if (Core\UtilsAlgolia::readyToIndex()): ?>
					<table class="widefat" style="margin-top: 30px; width: 50%; ">
						<thead>
							<tr>
								<th class="row-title" colspan="2"><strong><?php esc_html_e('Index Content', $langDomain); ?></strong></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th scope="row"><label for="mvnAlg_defaultIndex"><?php esc_html_e('Index Name', $langDomain); ?></label></th>
								<td><input type="text" class="regular-text" value="<?php echo esc_attr($registry->getDefaultIndex()); ?>" id="mvnAlg_defaultIndex" name="<?php echo Settings::settingsField; ?>[defaultIndex]"></td>
							</tr>
							<?php if ($registry->getDefaultIndex()): ?>

								<tr class="index-action-row index-action-button">
									<th scope="row"><label for="mvnAlg_index"><?php esc_html_e('Click to index content', $langDomain); ?></label></th>
									<td>
										<div class="algolia-action-button" style="width:50%;">
											<button type="button" class="button button-secondary"  id="mvnAlg_index" name="mvnAlg_index"><?php esc_html_e('Index Content', $langDomain); ?></button>
											<span class="spinner algolia-index-spinner"></span>
										</div>
									</td>
								</tr>
								<tr class="index-action-row index-messages">
									<th>&nbsp;</th>
									<td>
										<div class="success"><ul id="mvn-alg-index-result"></ul></div>
										<div class="error error-message" style="display: none;"><p id="mvn-alg-index-error" ></p></div>
									</td>
								</tr>
							<?php else: ?>
								<tr>
									<td colspan="2">
										<p><?php _e('Please set an "Index Name" and then update the settings to start indexing content.', $langDomain) ?></p>
									</td>
								</tr>
							<?php endif; ?>
							<tr>
								<td>
									<p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes', $langDomain); ?>" class="button button-primary" id="submit" name="submit"></p>
								</td>
							</tr>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
	</form>
</div>