<?php
/**
 * @package        solo
 * @copyright      2014-2017 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU GPL version 3 or later
 */

use Awf\Text\Text;

/** @var \Solo\View\Dbfilters\Html $this */

$router = $this->container->router;

$js = <<< JS

Solo.loadScripts.push(function() {
    Solo.Dbfilters.render(akeeba_dbfilter_data);	
});

JS;

$this->getContainer()->application->getDocument()->addScriptDeclaration($js);

echo $this->loadAnyTemplate('Common/error_modal');
?>

<?php if (!AKEEBABACKUP_PRO && (rand(0, 9) == 0)): ?>
	<div style="border: thick solid green; border-radius: 10pt; padding: 1em; background-color: #f0f0ff; color: #333; font-weight: bold; text-align: center; margin: 1em 0">
		<p><?php echo Text::_('SOLO_MAIN_LBL_SUBSCRIBE_TEXT') ?></p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="text-align: center; margin: 0px;">
			<input type="hidden" name="cmd" value="_s-xclick" />
			<input type="hidden" name="hosted_button_id" value="3NTKQ3M2DYPYW" />
			<button onclick="this.form.submit(); return false;" class="btn btn-success">
				<img src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0">
				Donate via PayPal
			</button>
			<a class="small" style="font-weight: normal; color: #666" href="https://www.akeebabackup.com/subscribe/new/backupwp.html?layout=default">
				<?php echo Text::_('SOLO_MAIN_BTN_SUBSCRIBE_UNOBTRUSIVE'); ?>
			</a>
		</form>
	</div>
<?php endif; ?>

<div class="alert alert-info">
	<strong><?php echo Text::_('COM_AKEEBA_CPANEL_PROFILE_TITLE'); ?></strong>
	#<?php echo $this->profileid; ?> <?php echo $this->profilename; ?>
</div>

<div class="form-inline well">
	<div>
		<label><?php echo Text::_('COM_AKEEBA_DBFILTER_LABEL_ROOTDIR') ?></label>
		<span><?php echo $this->root_select; ?></span>
		<button class="btn btn-danger" onclick="Solo.Dbfilters.nuke(); return false;">
			<span class="glyphicon glyphicon-trash"></span>
			<?php echo Text::_('COM_AKEEBA_DBFILTER_LABEL_NUKEFILTERS'); ?>
		</button>
		<button class="btn btn-warning btn-sm" onclick="Solo.Dbfilters.excludeNonCMS(); return false;">
			<span class="glyphicon glyphicon-fire"></span>
			<?php echo Text::_('COM_AKEEBA_DBFILTER_LABEL_EXCLUDENONCORE'); ?>
		</button>
		<a class="btn btn-sm btn-default" href="<?php echo $router->route('index.php?view=dbfilters&task=tabular')?>">
			<span class="glyphicon glyphicon-list-alt"></span>
			<?php echo Text::_('COM_AKEEBA_FILEFILTERS_LABEL_VIEWALL')?>
		</a>
	</div>
</div>

<fieldset>
	<legend><?php echo Text::_('COM_AKEEBA_DBFILTER_LABEL_TABLES'); ?></legend>
	<div id="tables"></div>
</fieldset>