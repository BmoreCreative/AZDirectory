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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
use Joomla\Module\Azdirectory\Site\Helper\AzdirectoryHelper;
use Joomla\Registry\Registry;

/**
 * These variables are extracted from the indexed array returned by the Dispatcher's getLayoutData() method.
 *
 * @var stdClass          $module      The module data loaded by Joomla
 * @var SiteApplication   $app         The Joomla administrator application object
 * @var Input             $input       The application input object
 * @var Registry          $params      The module parameters
 * @var stdClass          $template    The site template object
 * @var AzdirectoryHelper $az          The helper object
 * @var array             $azdirectory Information about the index letters to display
 * @var string            $lastletter
 * @var string            $modAZAssetsPath
 */

?>

<div class="modazdirectory<?= $params->get('moduleclass_sfx', '') ?: ''; ?>" id="modazdirectory">
	<a name="modazdirectory"></a>
    <?php if ($params->get('show_alphabet', 1) == 1) : ?>
		<?php foreach( $azdirectory[0] as $alphabet => $letters ) : ?>
			<?php if( sizeof( $azdirectory[0] ) > 1 ) : ?>
			<p class="modazdirectory__label">
                <?= Text::_( 'MOD_AZDIRECTORY_TMPL_CONTACTS_WITH' ) ?>
                <?= Text::_( 'MOD_AZDIRECTORY_' . strtoupper( $alphabet ) ) ?>
                <?= Text::_( 'MOD_AZDIRECTORY_TMPL_NAMES' ) ?>
			</p>
			<?php endif; ?>
			<ul class="modazdirectory__list">
				<li class="modazdirectory__listitem-all">
					<a class="modazdirectory__link" href="<?= Uri::current() ?>?lastletter=<?= Text::_( 'JALL' ) ?>#modazdirectory" rel="<?= Text::_( 'JALL' ) ?>">
                        <?= Text::_( 'JALL' ) ?>
					</a>
				</li>
				<?php foreach( $letters as $letter ): ?>
					<?php if( in_array( $letter, $azdirectory[1] ) ) : ?>
						<?php $addnClass = ( ( $lastletter ) && ( $lastletter == $letter ) ) ? " selected" : ""; ?>
						<li class="modazdirectory__listitem<?= $addnClass ?>"><a class="modazdirectory__link" href="<?= Uri::current() . "?lastletter=" . $letter ?>#modazdirectory" rel="<?= $letter ?>"><?= $letter ?></a></li>
					<?php else : ?>
						<li class="modazdirectory__listitem"><?= $letter ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>

		<form name="modazdirectory__form" class="modazdirectory__form" method="post">
			<select name="modazdirectory__select" id="modazdirectory__select">
				<option value=""><?= $az->azFirstOption( $params->get('sortorder', 'ln') ) ?></option>
				<option value="<?= Uri::current() ?>?lastletter=<?= Text::_('JALL') ?>#modazdirectory"><?= Text::_('JALL') ?></option>
				<?php foreach( $azdirectory[0] as $alphabet => $letters ) : ?>
					<?php if( sizeof( $azdirectory[0] ) > 1 ) : ?>
					<optgroup label="<?= Text::_('MOD_AZDIRECTORY_' . strtoupper( $alphabet ) ) ?>">
					<?php endif; ?>
					<?php foreach( $letters as $letter ) : ?>
						<?php if( in_array( $letter, $azdirectory[1] ) ) : ?>
						<option value="<?= Uri::current() . "?lastletter=" . $letter ?>#modazdirectory"><?= $letter ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if( sizeof( $azdirectory[0] ) > 1 ): ?>
					</optgroup>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
			<noscript><input type="submit" name="modazdirectory__submit" id="modazdirectory__submit" value="Submit" /></noscript>
            <?= HTMLHelper::_( 'form.token' ) ?>
		</form>
	<?php endif; ?>

    <?php if ( !empty( $contacts ) ) : ?>
		<?php if ( $params->get('show_alphabet', 1) == 1 ) : ?>
			<?php if ( $lastletter ) : ?>
			<<?= $params->get('header_tag','h3') ?> class="modazdirectory__heading"><?= $lastletter ?></<?= $params->get('header_tag','h3') ?>>
			<?php endif; ?>
		<?php endif; ?>

		<div class="modazdirectory__results">
        	<?php foreach ( $contacts as $key => $contact ) : ?>
				<div class="modazdirectory__result modazdirectory__layout-misc_<?= ( ($params->get('misc_layout', 0) == 1 ) ? 'on' : 'off' ) ?>">
                    <?php if ( $params->get('show_image', 1) == 1 ) : ?>
						<?php if ( empty( $contact->image ) ) : ?>
                        <span class="modazdirectory__glyph-camera">
                            <svg class="modazdirectory__icon">
                                <use xlink:href="<?= $modAZAssetsPath ?>symbol-defs.svg#icon-camera"></use>
                            </svg>
                        </span>
						<?php else : ?>
                            <?= HTMLHelper::_('image', Uri::base() . $contact->image, $az->azFormatName($contact->name, $params->get('lastname_first', 0)), array('class' => 'modazdirectory__image', 'itemprop' => 'image', 'loading' => 'lazy')) ?>
                  	<?php endif; endif; ?>

                    <div>
                        <?php if ( $az->azVerify( 'name', $contact ) ): ?>
						<?php if ( $params->get('name_hyperlink', 0) ) : ?>
                        <h3>
							<a
							href="#modazdirectory__modal"
							data-bs-toggle="modal"
							data-bs-target="#modazdirectory__modal"
							data-remote="<?= Uri::base() ?>index.php?option=com_contact&view=contact&tmpl=component&id=<?= $contact->id ?>
							">
                                <?= $az->azFormatName($contact->name, $params->get('lastname_first', 0)) ?>
							</a>
						</h3>
						<?php else : ?>
						<h3><?= $az->azFormatName($contact->name, $params->get('lastname_first', 0)) ?></h3>
						<?php endif; ?>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'con_position', $contact ) ): ?>
                        <p class="modazdirectory__field-position"><?= $contact->con_position ?></p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'address', $contact ) ) : ?>
                        <p class="modazdirectory__field-address"><?= $contact->address ?></p>
                        <?php endif; ?>

                        <p class="modazdirectory__field-postcode"><?= $az->azFormatAddress( $contact, $params->get('postcode_first', 0) ) ?></p>

                        <?php if ( $az->azVerify( 'country', $contact ) ) : ?>
                        <p class="modazdirectory__field-country"><?= $contact->country ?></p>
                        <?php endif; ?>

                        <?php if ( $params->get('show_category', 0) == 1 ): ?>
                        <p class="modazdirectory__field-category">
                            <span class="modazdirectory__label-category"><?= htmlspecialchars($params->get('category_label', 'Category: ')) ?></span>
                            <?= $contact->catname ?>
                        </p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'telephone', $contact ) ): ?>
                        <p class="modazdirectory__field-phone">
							<?php if ( $params->get('show_telephone_icon', 0) ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?= $modAZAssetsPath ?>symbol-defs.svg#icon-phone"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <span class="modazdirectory__label-phone"><?= htmlspecialchars($params->get('telephone_label', 't: ')) ?></span>
							<?php if ( $params->get('telephone_hyperlink', 0) ) : ?>
                            <a href="tel:+<?= $az->azSanitizeTelephone( $contact->telephone ) ?>"><?= htmlspecialchars($contact->telephone) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($contact->telephone) ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'mobile', $contact ) ) : ?>
                        <p class="modazdirectory__field-mobile">
							<?php if ( $params->get('show_mobile_icon', 0) ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?= $modAZAssetsPath ?>symbol-defs.svg#icon-mobile"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <span class="modazdirectory__label-mobile"><?= htmlspecialchars($params->get('mobile_label', 'm: ')) ?></span>
							<?php if ( $params->get('mobile_hyperlink', 0) ) : ?>
							<a href="tel:+<?= $az->azSanitizeTelephone( $contact->mobile ) ?>"><?= htmlspecialchars($contact->mobile) ?></a>
							<?php else: ?>
                                <?= htmlspecialchars($contact->mobile) ?>
							<?php endif; ?>
                        </p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'fax', $contact ) ) : ?>
                        <p class="modazdirectory__field-fax">
							<?php if ( $params->get('show_fax_icon', 0) ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?= $modAZAssetsPath ?>symbol-defs.svg#icon-fax"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <span class="modazdirectory__label-fax"><?= htmlspecialchars($params->get('fax_label', 'f: ')) ?></span>
							<?php if ( $params->get('fax_hyperlink', 0) ) : ?>
                            <a href="tel:+<?= $az->azSanitizeTelephone( $contact->fax ) ?>"><?= htmlspecialchars($contact->fax) ?></a>
							<?php else : ?>
                                <?= htmlspecialchars($contact->fax) ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'email_to', $contact ) ) : ?>
                        <p class="modazdirectory__nowrap modazdirectory__field-email">
							<?php if ( $params->get('show_email_to_icon', 0) ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?= $modAZAssetsPath ?>symbol-defs.svg#icon-envelope"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <?php if ( $params->get('email_to_hyperlink', 0) ) : ?>
                                <?= HTMLHelper::_( 'email.cloak', $contact->email_to, 1 ) ?>
                            <?php else : ?>
                                <?= HTMLHelper::_( 'email.cloak', $contact->email_to, 0 ) ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

                        <?php if ( $az->azVerify( 'webpage', $contact ) ) : ?>
                        <p class="modazdirectory__nowrap modazdirectory__field-webpage">
							<?php if ( $params->get('show_webpage_icon', 0) ) : ?>
							<span class="modazdirectory__glyph">
								<svg class="modazdirectory__icon">
									<use xlink:href="<?= $modAZAssetsPath ?>symbol-defs.svg#icon-sphere"></use>
								</svg>
							</span>
                            <?php endif; ?>
                            <?php if ( $params->get('webpage_hyperlink', 0) ) : ?>
                            <a href="<?= $az->azSanitizeURL( $contact->webpage ) ?>" target="_blank" rel="noopener"><?= ( $params->get( 'show_webpage_url', 0 ) ) ? htmlspecialchars( $contact->webpage ) : Text::_( 'JVISIT_LINK' ) ?></a>
                            <?php else : ?>
                                <?= htmlspecialchars( $contact->webpage ) ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

						<?php if ( $params->get( 'show_customfields', 0 ) ) : ?>
							<?php foreach ( $contact->customfields as $azCustomFields ) : ?>
								<p class="modazdirectory__field-<?= $azCustomFields['slug'] ?>"><?= $azCustomFields['title'] ?>: <?= $azCustomFields['value'] ?></p>
							<?php endforeach; ?>
						<?php endif; ?>

                        <?php if ( $az->azVerify( 'misc', $contact ) ): ?>
						<blockquote>
                            <?= $contact->misc ?>
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
					<h5 class="modal-title" id="modazdirectory__label"><?= Text::_( 'MOD_AZDIRECTORY_MODAL_CONTACT_DETAILS' ) ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= Text::_( 'MOD_AZDIRECTORY_MODAL_CLOSE' ) ?>"></button>
				</div>
				<div class="modal-body" id="modazdirectory__modal-body"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Text::_( 'MOD_AZDIRECTORY_MODAL_CLOSE' ) ?></button>
				</div>
			</div> <!-- .modal-content -->
		</div> <!-- .modal-dialog -->
	</div> <!-- .modazdirectory__modal -->
	<?php endif; ?>

</div>