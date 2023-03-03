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

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
?>

<div class="modazdirectory<?php echo $moduleclass_sfx; ?>" id="modazdirectory">
	<a name="modazdirectory"></a>
	<?php if( $show_alphabet == 1 ) : ?>
		<?php foreach( $azdirectory[0] as $alphabet => $letters ) : ?>
			<?php if( sizeof( $azdirectory[0] ) > 1 ) : ?>
			<p class="modazdirectory__label">
				<?php echo Text::_( 'MOD_AZDIRECTORY_TMPL_CONTACTS_WITH' ); ?>
				<?php echo Text::_( 'MOD_AZDIRECTORY_' . strtoupper( $alphabet ) ); ?>
				<?php echo Text::_( 'MOD_AZDIRECTORY_TMPL_NAMES' ); ?>
			</p>
			<?php endif; ?>
			<ul class="modazdirectory__list">
				<li class="modazdirectory__listitem-all">
					<a class="modazdirectory__link" href="<?php echo Uri::current(); ?>?lastletter=<?php echo Text::_( 'JALL' ); ?>#modazdirectory" rel="<?php echo Text::_( 'JALL' ); ?>">
						<?php echo Text::_( 'JALL' ); ?>
					</a>
				</li>
				<?php foreach( $letters as $letter ): ?>
					<?php if( in_array( $letter, $azdirectory[1] ) ) : ?>
						<?php $addnClass = ( ( $lastletter ) && ( $lastletter == $letter ) ) ? " selected" : ""; ?>
						<li class="modazdirectory__listitem<?php echo $addnClass; ?>"><a class="modazdirectory__link" href="<?php echo Uri::current() . "?lastletter=" . $letter; ?>#modazdirectory" rel="<?php echo $letter; ?>"><?php echo $letter; ?></a></li>
					<?php else : ?>
						<li class="modazdirectory__listitem"><?php echo $letter; ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
		
		<form name="modazdirectory__form" class="modazdirectory__form" method="post">
			<select name="modazdirectory__select" id="modazdirectory__select">
				<option value=""><?php echo $az->azFirstOption( $sortorder ); ?></option>
				<option value="<?php echo Uri::current(); ?>?lastletter=<?php echo Text::_('JALL'); ?>#modazdirectory"><?php echo Text::_('JALL'); ?></option>
				<?php foreach( $azdirectory[0] as $alphabet => $letters ) : ?>
					<?php if( sizeof( $azdirectory[0] ) > 1 ) : ?>
					<optgroup label="<?php echo Text::_( 'MOD_AZDIRECTORY_' . strtoupper( $alphabet ) ); ?>">
					<?php endif; ?>
					<?php foreach( $letters as $letter ) : ?>
						<?php if( in_array( $letter, $azdirectory[1] ) ) : ?>
						<option value="<?php echo Uri::current() . "?lastletter=" . $letter; ?>#modazdirectory"><?php echo $letter; ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if( sizeof( $azdirectory[0] ) > 1 ): ?>
					</optgroup>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
			<noscript><input type="submit" name="modazdirectory__submit" id="modazdirectory__submit" value="Submit" /></noscript>
			<?php echo HTMLHelper::_( 'form.token' ); ?>
		</form>
	<?php endif; ?>
	
    <?php if ( !empty( $contacts ) ) : ?>
		<?php if ( $show_alphabet == 1 ) : ?>
			<?php if ( $lastletter ) : ?>
			<<?php echo $header_tag; ?> class="modazdirectory__heading"><?php echo $lastletter; ?></<?php echo $header_tag; ?>>
			<?php endif; ?>
		<?php endif; ?>

		<div class="modazdirectory__results">
        	<?php foreach ( $contacts as $key => $contact ) : ?>
				<div class="modazdirectory__result modazdirectory__layout-misc_<?php echo ( ( $misc_layout == 1 ) ? 'on' : 'off' ); ?>">
                    <?php if ( $show_image == 1 ) : ?>
						<?php if ( empty( $contact->image ) ) : ?>
                        <span class="modazdirectory__glyph-camera">
                            <svg class="modazdirectory__icon">
                                <use xlink:href="<?php echo $modAZAssetsPath; ?>symbol-defs.svg#icon-camera"></use>
                            </svg>
                        </span>
						<?php else : ?>
                        <?php echo HTMLHelper::_('image', Uri::base() . $contact->image, $az->azFormatName($contact->name, $lastname_first), array('class' => 'modazdirectory__image', 'itemprop' => 'image', 'loading' => 'lazy')); ?>
                  	<?php endif; endif; ?>

                    <div>
                        <?php if ( $az->azVerify( 'name', $contact ) ): ?>
						<?php if ( $name_hyperlink ) : ?>
                        <h3>
							<a
							href="#modazdirectory__modal" 
							data-bs-toggle="modal" 
							data-bs-target="#modazdirectory__modal" 
							data-remote="<?php echo Uri::base(); ?>index.php?option=com_contact&view=contact&tmpl=component&id=<?php echo $contact->id; ?>
							">
								<?php echo $az->azFormatName($contact->name, $lastname_first); ?>
							</a>
						</h3>
						<?php else : ?>
						<h3><?php echo $az->azFormatName($contact->name, $lastname_first); ?></h3>
						<?php endif; ?>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'con_position', $contact ) ): ?>
                        <p class="modazdirectory__field-position"><?php echo $contact->con_position; ?></p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'address', $contact ) ) : ?>
                        <p class="modazdirectory__field-address"><?php echo $contact->address; ?></p>
                        <?php endif; ?>

                        <p class="modazdirectory__field-postcode"><?php echo $az->azFormatAddress( $contact, $postcode_first ); ?></p>

                        <?php if ( $az->azVerify( 'country', $contact ) ) : ?>
                        <p class="modazdirectory__field-country"><?php echo $contact->country; ?></p>
                        <?php endif; ?>

                        <?php if ( $show_category == 1 ): ?>
                        <p class="modazdirectory__field-category">
                            <span class="modazdirectory__label-category"><?php echo $category_label; ?></span>
							<?php echo $contact->catname; ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ( $az->azVerify( 'telephone', $contact ) ): ?>
                        <p class="modazdirectory__field-phone">
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
                        <p class="modazdirectory__field-mobile">
							<?php if ( $show_mobile_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo $modAZAssetsPath; ?>symbol-defs.svg#icon-mobile"></use>
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
                        <p class="modazdirectory__field-fax">
							<?php if ( $show_fax_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo $modAZAssetsPath; ?>symbol-defs.svg#icon-fax"></use>
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
                        <p class="modazdirectory__nowrap modazdirectory__field-email">
							<?php if ( $show_email_to_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo $modAZAssetsPath; ?>symbol-defs.svg#icon-envelope"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <?php if ( $email_to_hyperlink ) : ?>
                            <?php echo HTMLHelper::_( 'email.cloak', $contact->email_to, 1 ); ?>
                            <?php else : ?>
							<?php echo HTMLHelper::_( 'email.cloak', $contact->email_to, 0 ); ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ( $az->azVerify( 'webpage', $contact ) ) : ?>
                        <p class="modazdirectory__nowrap modazdirectory__field-webpage">
							<?php if ( $show_webpage_icon ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?php echo $modAZAssetsPath; ?>symbol-defs.svg#icon-sphere"></use>
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

						<?php if ( $show_customfields ) : ?>
						<?php foreach ( $contact->customfields as $azCustomK => $azCustomV ) : ?>
						<p class="modazdirectory__field-<?php echo OutputFilter::stringURLSafe( $azCustomK ); ?>"><?php echo $azCustomK . ': ' . $azCustomV; ?></p>
						<?php endforeach; ?>
						<?php endif; ?>

                        <?php if ( $az->azVerify( 'misc', $contact ) ): ?>
						<blockquote>
                        <?php echo $contact->misc; ?>
						</blockquote>
                        <?php endif; ?>
                    </div>
                </div> <!-- .modazdirectory__result -->
            <?php endforeach; ?>
		</div> <!-- .modazdirectory__results -->
	<?php endif; ?>

	<nav class="modazdirectory__pagination">
		<?php
		$azPagination = new Pagination( $total, $start, $pagination );
		$azPagination->setAdditionalUrlParam( 'lastletter', $lastletter );
		echo $azPagination->getPagesLinks();
		?>
	</nav>
	
	<?php if ( $name_hyperlink ) : ?>
	<div id="modazdirectory__modal" class="modal fade" tabindex="-1" aria-labelledby="modazdirectory__label">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modazdirectory__label"><?php echo Text::_( 'MOD_AZDIRECTORY_MODAL_CONTACT_DETAILS' ); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo Text::_( 'MOD_AZDIRECTORY_MODAL_CLOSE' ); ?>"></button>
				</div>
				<div class="modal-body" id="modazdirectory__modal-body"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_( 'MOD_AZDIRECTORY_MODAL_CLOSE' ); ?></button>
				</div>
			</div> <!-- .modal-content --> 
		</div> <!-- .modal-dialog -->
	</div> <!-- .modazdirectory__modal -->
	<?php endif; ?>

</div>