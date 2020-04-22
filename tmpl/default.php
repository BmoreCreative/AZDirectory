<?php
/**
 * @package		A-Z Directory
 * @subpackage	mod_azdirectory
 * @copyright	Copyright (C) 2016 Bmore Creative, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @website		https://www.bmorecreativeinc.com/joomla/extensions
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<div class="modazdirectory<?php echo $moduleclass_sfx; ?>" id="modazdirectory">
	<a name="modazdirectory"></a>
	<?php if( $show_alphabet == 1 ) : ?>
		<ul class="modazdirectory__list">
			<li class="modazdirectory__listitem-all">
				<a class="modazdirectory__link" href="<?php echo JUri::current(); ?>?lastletter=<?php echo JText::_('JALL'); ?>#modazdirectory" rel="<?php echo JText::_('JALL'); ?>">
					<?php echo JText::_('JALL'); ?>
				</a>
			</li>
			<?php
			foreach ( $azdirectory[0] as $letter ) : 
				if ( in_array( $letter, $azdirectory[1] ) ) : ?>
					<?php $addnClass = ( ( $lastletter ) && ( $lastletter == $letter ) ) ? " selected" : ""; ?>
					<li class="modazdirectory__listitem<?php echo $addnClass; ?>"><a class="modazdirectory__link" href="<?php echo JUri::current() . "?lastletter=" . $letter; ?>#modazdirectory" rel="<?php echo $letter; ?>"><?php echo $letter; ?></a></li>
				<?php else : ?>
					<li class="modazdirectory__listitem"><?php echo $letter; ?></li>
				<?php
				endif;
			endforeach;
			?>
		</ul>
		<form name="modazdirectory__form" class="modazdirectory__form" method="post">
			<select name="modazdirectory__select" id="modazdirectory__select">
				<option value=""><?php echo $az->azFirstOption( $sortorder ); ?></option>
				<option value="<?php echo JUri::current(); ?>?lastletter=<?php echo JText::_('JALL'); ?>#modazdirectory"><?php echo JText::_('JALL'); ?></option>
				<?php foreach ( $azdirectory[1] as $letter ) : ?>
				<option value="<?php echo JUri::current() . "?lastletter=" . $letter; ?>#modazdirectory"><?php echo $letter; ?></option>
				<?php endforeach; ?>
			</select>
			<noscript><input type="submit" name="modazdirectory__submit" id="modazdirectory__submit" value="Submit" /></noscript>
			<?php echo JHtml::_( 'form.token' ); ?>
		</form>
	<?php endif; ?>
    <div class="modazdirectory__results">
		<?php if ( $show_alphabet == 1 ) : ?>
			<?php if ( $lastletter ) : ?>
			<h1><?php echo $lastletter; ?></h1>
			<?php endif; ?>
		<?php endif; ?>
		
        <?php
		$contactcount = count( $contacts );
		$counter = 0;
        ?>
        
    	<?php if ( !empty( $contacts ) ) : ?>
        	<?php foreach ( $contacts as $key => &$contact ) : ?>
            	<?php $rowcount = ( (int) $key % (int) 2 ) + 1; ?>
                <?php if ( $rowcount == 1 ) : ?>
                	<?php $row = $counter / 2; ?>
            		<div class="modazdirectory__row">
              	<?php endif; ?>
				
				<div class="modazdirectory__result">
                    <?php if ( $show_image == 1 ) : ?>
						<?php if ( empty( $contact->image ) ) : ?>
                        <span class="modazdirectory__glyph-camera">
                            <svg class="modazdirectory__icon">
                                <use xlink:href="<?php echo JUri::base() . 'modules/' . $module->module; ?>/assets/symbol-defs.svg#icon-camera"></use>
                            </svg>
                        </span>
						<?php else : ?>
                        <?php echo JHtml::_('image', JUri::base() . $contact->image, $az->azFormatName($contact->name, $lastname_first), array('class' => 'modazdirectory__image', 'itemprop' => 'image')); ?>
                  	<?php endif; endif; ?>
							
                    <div>
                        <?php if ( $az->azVerify( 'name', $contact ) ): ?>
						<?php if ( $name_hyperlink ) : ?>
                        <h3>
							<a
							href="<?php echo JUri::base(); ?>index.php?option=com_contact&view=contact&id=<?php echo $contact->id; ?>" 
							data-toggle="modal" 
							data-target="#modazdirectory__modal" 
							data-remote="<?php echo JUri::base(); ?>index.php?option=com_contact&view=contact&tmpl=component&id=<?php echo $contact->id; ?>
							">
								<?php echo $az->azFormatName($contact->name, $lastname_first); ?>
							</a>
						</h3>
						<?php else : ?>
						<h3><?php echo $az->azFormatName($contact->name, $lastname_first); ?></h3>
						<?php endif; ?>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'con_position', $contact ) ): ?>
                        <p><?php echo $contact->con_position; ?></p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'address', $contact ) ) : ?>
                        <p><?php echo $contact->address; ?></p>
                        <?php endif; ?>

                        <p><?php echo $az->azFormatAddress( $contact, $postcode_first ); ?></p>
                        
                        <?php if ( $az->azVerify( 'telephone', $contact ) ): ?>
                        <p>
							<?php if ( $show_telephone_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo $modAZAssetsPath; ?>symbol-defs.svg#icon-phone"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <span class="modazdirectory__label-phone"><?php echo $telephone_label; ?></span>
							<?php if ( $telephone_hyperlink ) : ?>
                            <a href="tel:+<?php echo $az->azSanitizeTelephone( $contact->telephone ); ?>"><?php echo $contact->telephone; ?></a>
                            <?php else: ?>
							<?php echo $contact->telephone; ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ( $az->azVerify( 'mobile', $contact ) ) : ?>
                        <p>
							<?php if ( $show_mobile_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo JUri::base() . 'modules/' . $module->module; ?>/assets/symbol-defs.svg#icon-mobile"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <span class="modazdirectory__label-mobile"><?php echo $mobile_label; ?></span>
							<?php if ( $mobile_hyperlink ) : ?>
							<a href="tel:+<?php echo $az->azSanitizeTelephone( $contact->mobile ); ?>"><?php echo $contact->mobile; ?></a>
							<?php else: ?>
							<?php echo $contact->mobile; ?>
							<?php endif; ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ( $az->azVerify( 'fax', $contact ) ) : ?>
                        <p>
							<?php if ( $show_fax_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo JUri::base() . 'modules/' . $module->module; ?>/assets/symbol-defs.svg#icon-fax"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <span class="modazdirectory__label-fax"><?php echo $fax_label; ?></span>
							<?php if ( $fax_hyperlink ) : ?>
                            <a href="tel:+<?php echo $az->azSanitizeTelephone( $contact->fax ); ?>"><?php echo $contact->fax; ?></a>
							<?php else : ?>
							<?php echo $contact->fax; ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'email_to', $contact ) ) : ?>
                        <p class="modazdirectory__nowrap">
							<?php if ( $show_email_to_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo JUri::base() . 'modules/' . $module->module; ?>/assets/symbol-defs.svg#icon-envelope"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <?php if ( $email_to_hyperlink ) : ?>
                            <?php echo JHtml::_( 'email.cloak', $contact->email_to, 1 ); ?>
                            <?php else : ?>
							<?php echo JHtml::_( 'email.cloak', $contact->email_to, 0 ); ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ( $az->azVerify( 'webpage', $contact ) ) : ?>
                        <p class="modazdirectory__nowrap">
							<?php if ( $show_webpage_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo JUri::base() . 'modules/' . $module->module; ?>/assets/symbol-defs.svg#icon-sphere"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <?php if ( $webpage_hyperlink ) : ?>
                            <a href="<?php echo $az->azSanitizeURL( $contact->webpage ); ?>" target="_blank" rel="noopener"><?php echo $contact->webpage; ?></a> 
                            <?php else : ?>
							<?php echo $contact->webpage; ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php $counter++ ?>
                </div> <!-- /modazdirectory__result -->

                <?php if ( ( $rowcount == 2 ) or ( $counter == $contactcount ) ) : ?>
            		</div> <!-- /modazdirectory__row -->
                <?php endif; ?>
            <?php endforeach; ?>
   		<?php endif; ?>

    </div> <!-- /modazdirectory__results -->

	<?php if ( $name_hyperlink ) : ?>
	<div id="modazdirectory__modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modazdirectory__label">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content-container">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo JText::_('MOD_AZDIRECTORY_MODAL_CLOSE'); ?>">
						<span  aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="modazdirectory__label"><?php echo JText::_('MOD_AZDIRECTORY_MODAL_CONTACT_DETAILS'); ?></h4>
				</div>
				<div class="modal-body modal-content" id="modazdirectory__modal-body">
					<div class="modal-spinner"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('MOD_AZDIRECTORY_MODAL_CLOSE'); ?></button>
				</div>
			</div> <!-- /modal-content --> 
		</div> <!-- /modal-dialog -->
	</div> <!-- /modazdirectory__modal -->
	<?php endif; ?>

</div>