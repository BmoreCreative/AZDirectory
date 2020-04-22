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
<a name="modazdirectory"></a>
<div class="modazdirectory<?php echo $moduleclass_sfx; ?>">
	<ul class="modazdirectory__list">
    	<li class="modazdirectory__listitem-all"><a class="modazdirectory__link" href="<?php echo JUri::current(); ?>?lastletter=All#modazdirectory" rel="All">All</a></li>
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
			<option value="" selected>Last Name</option>
            <option value="<?php echo JUri::current(); ?>?lastletter=All#modazdirectory">All</option>
			<?php foreach ( $azdirectory[1] as $letter ) : ?>
			<option value="<?php echo JUri::current() . "?lastletter=" . $letter; ?>#modazdirectory"><?php echo $letter; ?></option>
			<?php endforeach; ?>
		</select>
		<noscript><input type="submit" name="modazdirectory__submit" id="modazdirectory__submit" value="Submit" /></noscript>
	</form>
    <div class="modazdirectory__results">
		<?php if ( $lastletter ) : ?>
        <h1><?php echo $lastletter; ?></h1>
        <?php endif; ?>

    	<?php if ( is_array( $contacts ) ) : ?>
			
			<?php foreach ( array_chunk ( $contacts, 2 ) as $contactchunk ) : ?>
            
            <div class="modazdirectory__row">
                
                <?php foreach ( $contactchunk as $contact ) : ?>
            	
                <div class="modazdirectory__result">
                    <?php if ( $show_image == 1 ) : 
                    $contactImage =  JUri::base() . $contact->image;
                    $image_attrs = getimagesize( $contactImage );
                    if( empty( $image_attrs ) ) : ?>
                    <span class="modazdirectory__icon-camera"> </span>
                    <?php else : ?>
                    <img src="<?php echo $contactImage; ?>" alt="<?php echo $contact->name; ?>" class="modazdirectory__image" />
                    <?php endif; endif; ?>

                    <div>
                        <?php if ( modAZDirectoryHelper::azVerify( 'name', $contact ) ): ?>						
                        <h3><?php echo modAZDirectoryHelper::azFormatName($contact->name, $lastname_first); ?></h3>
                        <?php endif; ?>

                        <?php if ( modAZDirectoryHelper::azVerify( 'con_position', $contact ) ): ?>
                        <p><?php echo $contact->con_position; ?></p>
                        <?php endif; ?>

                        <?php if ( modAZDirectoryHelper::azVerify( 'address', $contact ) ) : ?>
                        <p><?php echo $contact->address; ?></p>
                        <?php endif; ?>

                        <p>
                            <?php if ( modAZDirectoryHelper::azVerify( 'suburb', $contact ) ) : ?>
                            <span><?php echo $contact->suburb; ?></span>
                            <?php endif; ?>
                            
                            <?php if ( ( modAZDirectoryHelper::azVerify( 'suburb', $contact ) ) && ( modAZDirectoryHelper::azVerify( 'state', $contact ) ) ) : ?>
                            <span>, <?php echo $contact->state; ?></span>
                            <?php else : if ( modAZDirectoryHelper::azVerify( 'state', $contact ) ) : ?>
                            <span><?php echo $contact->state; ?></span>
                            <?php endif; endif; ?>
                            
                            <?php if( ( ( modAZDirectoryHelper::azVerify( 'suburb', $contact ) ) || ( modAZDirectoryHelper::azVerify( 'state', $contact ) ) ) && modAZDirectoryHelper::azVerify( 'postcode', $contact ) == 1 ) : ?>
                            <span> <?php echo $contact->postcode; ?></span>
                            <?php else : if ( modAZDirectoryHelper::azVerify( 'postcode', $contact ) ) : ?>
                            <span><?php echo $contact->postcode; ?></span>
                            <?php endif; endif; ?>
                        </p>
                        
                        <?php if ( modAZDirectoryHelper::azVerify( 'telephone', $contact ) ): ?>
                        <p>t: <?php echo $contact->telephone; ?></p>
                        <?php endif; ?>
                        
                        <?php if ( modAZDirectoryHelper::azVerify( 'mobile', $contact ) ) : ?>
                        <p>m: <?php echo $contact->mobile; ?></p>
                        <?php endif; ?>
                        
                        <?php if ( modAZDirectoryHelper::azVerify( 'fax', $contact ) ) : ?>
                        <p>f: <?php echo $contact->fax; ?></p>
                        <?php endif; ?>

                        <?php if ( modAZDirectoryHelper::azVerify( 'email_to', $contact ) ) : ?>
                        <p><?php echo $contact->email_to; ?></p>
                        <?php endif; ?>
                        
                        <?php if ( modAZDirectoryHelper::azVerify( 'webpage', $contact ) ) : ?>
                        <p><?php echo $contact->webpage; ?></p>
                        <?php endif; ?>
                    </div>
                </div> <!-- /modazdirectory__result -->
            
				<?php endforeach; ?>
            
            </div> <!-- /modazdirectory__row -->
           
            <?php endforeach; ?>
    
   		<?php endif; ?>

    </div> <!-- /modazdirectory__results -->
</div> <!-- /modazdirectory -->