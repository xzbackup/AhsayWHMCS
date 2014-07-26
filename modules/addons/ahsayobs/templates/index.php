<script type="text/javascript">


jQuery(document).ready(function(){
	$('#tab').tabs({
		show: function(e, ui){
			$(ui.panel).css('visibility','visible');
		}
	});
	$("button.ui-widget").button();
	
});

</script>
<?php if (!$licenseValid): ?>
	<div class="errorbox">
		<strong>
			<span class="title"><?php echo $ahsayClientLanguage[$licError]; ?></span>
		</strong>
		<br>Update your license key or purchase a license from <a href="http://www.ahsaytools.com/">http://www.ahsaytools.com/</a>
	</div>
<?php endif; ?>
<?php if (!empty($resultText)): ?>
	<div class="infobox">
		<strong>
			<span class="title"><?php echo $resultText; ?></span>
		</strong>
	</div>
<?php endif; ?>
<div id="tab">
	<ul style="height:30px;">
		<?php if ($licenseValid): ?>
		<li><a href="#servers-tab">Servers</a></li>
		<li><a href="#user-settings">User Creation Defaults</a></li>
		<li><a href="#system-settings">System Settings</a></li>
		<li><a href="#help-tab">Help Tab Content</a></li>
		<li><a href="#package-tab">Package Settings</a></li>
		<li><a href="#update-tab">Update</a></li>
		<?php endif; ?>
		<li><a href="#license-info">Module Information</a></li>
	</ul>
	<?php if ($licenseValid): ?>
	<div id="user-settings">
		<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">User Creation Settings</h2>

		<form action="<?php echo $modulelink; ?>&x=tools&y=updateUserSettings#user-settings" method="post">
			<input type="hidden" name="x" value="index"/>
			<input type="hidden" name="y" value="updateUserSettings"/>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tbody>
					<?php if ($userSettingsResult): ?>
						<tr>
							<td class="fieldlabel" colspan="2" style="text-align:center;color:red;"><?php echo $userSettingsResult; ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Default Timezone</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<select name="defaultTimezone">
								<?php foreach($ahsaySettings['timezones'] as $tz => $timezone): ?>
									<option value="<?php echo $tz; ?>" <?php if ($tz == $defaultTimezone): ?>selected<?php endif; ?>><?php echo $timezone; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Default timezone to set for newly created users.
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Default Language</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<select name="defaultLanguage">
								<?php foreach($ahsaySettings['languages'] as $lang => $language): ?>
									<option value="<?php echo $lang; ?>" <?php if ($lang == $defaultLanguage): ?>selected<?php endif; ?>><?php echo $language; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Default language to set for newly created users.
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Default Bandwidth</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<select name="defaultBandwidth">
								<?php foreach($ahsaySettings['bandwidth'] as $bw => $bandwidth): ?>
									<option value="<?php echo $bw; ?>" <?php if ($bw == $defaultBandwidth): ?>selected<?php endif; ?>><?php echo $bandwidth; ?></option>
								<?php endforeach; ?>
							</select>					
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Default bandwidth limit for created users.
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Default Owner</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="defaultOwner" size="25" value="<?php echo $defaultOwner; ?>">			
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Default owner for created users. Leave blank to create by 'system'
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Default Group Policy</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="defaultGroupPolicy" size="25" value="<?php echo $defaultOwner; ?>">			
						</td>
					</tr>
					
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Default group policy for users.
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							<input type="submit" value="Save Settings" class="btn success">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<div id="system-settings">
		<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">System Settings</h2>

		<form action="<?php echo $modulelink; ?>&x=tools&y=updateSettings#system-settings" method="post">
			<input type="hidden" name="x" value="index"/>
			<input type="hidden" name="y" value="updateSettings"/>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tbody>
					<?php if ($settingsResult): ?>
						<tr>
							<td class="fieldlabel" colspan="2" style="text-align:center;color:red;"><?php echo $settingsResult; ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<input type="hidden" name="backupTab" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Backup Account Tab</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="backupTab" size="25" value="1" <?php if ($backupTab):?>checked<?php endif; ?> onchange="$('.backupTabTitle').toggle();">
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Display user's backup account, already logged in, in it's own tab.
						</td>
					</tr>
					<tr class="backupTabTitle" <?php if (!$backupTab):?>style="display:none"<?php endif; ?>>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Tab Title</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="backupTabTitle" size="25" value="<?php echo $backupTabTitle; ?>">
						</td>
					</tr>
					<tr class="backupTabTitle" <?php if (!$backupTab):?>style="display:none"<?php endif; ?>>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Enter the title given to the tab that contains the iframe for the user's backup account logged-in already.
						</td>
					</tr>
					<tr>
						<input type="hidden" name="displayPassword" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Display Account Password</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="displayPassword" size="25" value="1" <?php if ($displayPassword):?>checked<?php endif; ?>>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Display Password To User
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">RDR Server</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="rdrServer" size="25" value="<?php echo $rdrServer; ?>">
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Enter the RDR server that will be used for backup account tab, leave blank to direct user to their specific server.
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Direct URL</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="directUrl" size="25" value="<?php echo $directUrl; ?>">
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							URL displayed to the user for direct access to their backup account. Most likely an easy URL redirecting to the end-user specific login. Include http://
						</td>
					</tr>
					<tr>
						<input type="hidden" name="sslServer" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Use SSL</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="sslServer" size="25" value="1" <?php if ($sslServer): ?>checked<?php endif; ?>>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Enable SSL for user logins.
						</td>
					</tr>
					<tr>
						<input type="hidden" name="showProfileButton" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Show User Profile Button</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="showProfileButton" size="25" value="1" <?php if ($showProfileButton): ?>checked<?php endif; ?>>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Show Profile Button (Only available in AhsayOBS 5.5.x Not available in 6.x)
						</td>
					</tr>
					<tr>
						<input type="hidden" name="showLogButton" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Show User Log Button</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="showLogButton" size="25" value="1" <?php if ($showLogButton): ?>checked<?php endif; ?>>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Show Log Button (Only available in AhsayOBS 6.x Not available in 5.5.x)
						</td>
					</tr>

					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							<input type="submit" value="Save Settings" class="btn success">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<div id="help-tab">
		<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">Update Help Tab</h2>
		<script language="javascript" type="text/javascript" src="editor/tiny_mce.js"></script>
		<form action="<?php echo $modulelink; ?>" method="post">
			<input type="hidden" name="x" value="index"/>
			<input type="hidden" name="y" value="updateHelpText"/>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tbody>
					<?php if ($helpTextResult): ?>
						<tr>
							<td class="fieldlabel" colspan="2" style="text-align:center"><?php echo $helpTextResult; ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							<textarea name="helpTabText" id="help_tab_text" rows="25" style="width:100%"><?php echo $helpTabText; ?></textarea>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							<input type="submit" value="Update Text" class="btn success">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	<div id="update-tab">
		<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">Module Updates</h2>
		<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
			<tbody>
				<tr>
					<td>
						<table class="form" width="100%" style="overflow:hidden;" border="0" cellspacing="2" cellpadding="3">
							<tbody>
								<tr>
									<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Current Version:</td>
									<td class="fieldlabel" colspan="2" style="text-align:center"> <?php echo $version; ?></td>
								</tr>
								<?php if ($versionStatus > 0): ?>
								<tr>
									<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Newest Version:</td>
									<td class="fieldlabel" style="text-align:center"> <?php echo $recentVersion; ?></td>
								</tr>
								
								<tr>
									<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Download Latest Version:</td>
									<td class="fieldlabel" colspan="2" style="text-align:center"><a href="http://<?php echo KBILL_UPDATE_URL; ?>/">Download</a></td>
								</tr>
								<?php elseif ($versionStatus == 0): ?>
								<tr>
									<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Version Status:</td>
									<td class="fieldlabel" style="text-align:center">Up-to-date</td>
								</tr>
								<?php else: ?>
								<tr>
									<td class="fieldlabel" colspan="2" style="text-align:center">You are using a newer version that what is available.</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td class="fieldlabel" colspan="2" style="text-align:center">
										<div class="update-news" style="width:600px;margin: 0 auto;">
											<?php echo $updateNews; ?>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="servers-tab">
		<?php if ($licenseValid): ?>
		<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
			<tbody>
				<tr>
					<td class="fieldlabel" style="text-align:center">
						<?php require_once(AHSAY_ADDON_MODULE . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'servers.php'); ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<div id="package-tab">
		<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">Custom Fields (required)</h2>
		<form action="<?php echo $modulelink; ?>&x=tools&y=updateCustomFields#package-tab" method="post">
			<input type="hidden" name="x" value="index"/>
			<input type="hidden" name="y" value="updateCustomFields"/>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tbody>
					<tr>
						<td colspan="2">You can use this form to automatically install the 'Custom Fields' required for each package to obtain a username and password on signup.</td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;">Username Field Description</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="packagesUsernameDescription"/>
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" colspan="2" style="text-align:center">Example: <pre>Backup Username (Only 0-9 a-z _ . characters)</pre></td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;">Password Field Description</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="packagesPasswordDescription"/>
						</td>
					</tr>
					<tr>
						<td class="fieldlabel" colspan="2" style="text-align:center">Example: <pre>Backup Password</pre></td>
					</tr>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;">Select Packages</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<select multiple="true" name="packages[]" style="width:300px;">
								<?php foreach($packages as $id => $pkg): ?>
								<option value="<?php echo $id; ?>"><?php echo $pkg['name']; ?> (<?php if (sizeof($pkg['customfields']) == 2): ?>X<?php else: ?>&nbsp;<?php endif; ?>)</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2"><span class="" style="color:red;">Packages listed with an (X) already contain the necessary custom fields. These packages will have their custom fields removed and cleared. Any user/product/service already configured and using these fields will lose their data. </span></td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							<input type="submit" value="Install Custom Fields" class="btn success">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		
	</div>
	<script type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
	    entity_encoding: "raw",
	    height: 500,
	    convert_urls : false,
		relative_urls : false,
	    plugins : "style,table,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,visualchars,xhtmlxtras",
	    theme_advanced_buttons1 : "cut,copy,paste,pastetext,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,search,replace",
	    theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,cleanup,code,help",
	    theme_advanced_buttons3 : "", // tablecontrols
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true
	});
	</script>
	<?php endif; ?>
	<div id="license-info">
		<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">Ahsay OBS WHMCS Module</h2>
		<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
			<tbody>
				<tr>
					<td class="fieldlabel" colspan="2" style="text-align:center">License Status: <?php echo $licenseValid ? 'Active' : 'Inactive'; ?></td>
				</tr>
				<?php if ($ahsay_addons): ?>
				<tr>
					<td class="fieldlabel" colspan="2" style="text-align:center">Features</td>
				</tr>
				
				<?php foreach($ahsay_addons as $n => $addon): ?>
				<tr>
					<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;"><?php echo $n; ?></td>
					<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
						<?php echo $addon['status']; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							No Addon Features Found Enabled
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">Update License Key</h2>
		<form action="<?php echo $modulelink; ?>&x=tools&y=updateLicenseKey#license-info" method="post">
			<input type="hidden" name="x" value="index"/>
			<input type="hidden" name="y" value="updateLicenseKey"/>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tbody>
					<?php if ($licenseKeyResult): ?>
						<tr>
							<td class="fieldlabel" colspan="2" style="text-align:center;color:red;"><?php echo $licenseKeyResult; ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;">Ahsay OBS WHMCS License key</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="licenseKey" size="25" value="<?php echo $licenseKey; ?>">
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							Enter a new license key. Otherwise to refresh the license and associated add-ons do not change the key but click 'Refresh License'.
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							<input type="submit" value="Refresh License" class="btn success">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<?php /*<h2 class="ui-accordion-header ui-helper-reset ui-state-default ui-state-active ui-corner-top mod-hdr">Configurable Options (optional add-ons)</h2>
		<form action="<?php echo $modulelink; ?>&x=tools&y=updateConfigurableOptions#package-tab" method="post">
			
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<?php /*<td class="fieldlabel" colspan="2" style="text-align:center">
					<h1><span>Configurable Options (optional add-ons)</span></h1>
				</td> ?>
				<tbody>
					<tr>
						<td colspan="2">
							These configurable options allow for a user upon signup to add additional storage space, exchange mailboxes, or increase VMware quota.<br/>
							<span class="" style="color:red;">
								This will simply create the necessary configurable options that can be attached to the products using the specific <a href="configproductoptions.php">Configurable Option</a> settings.
							</span>
						</td>
					</tr>
					<tr>
						<input type="hidden" name="configOptionQuota" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Create 'Quota Add-Ons'</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="configOptionQuota" value="1" onchange="$('.configOptionQuota').toggle();">
						</td>
					</tr>
					<tr style="display:none;" class="configOptionQuota">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;">Username Field Description</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="packagesUsernameDescription"/>
						</td>
					</tr>
					<tr>
						<input type="hidden" name="configOptionMailboxes" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Create 'Exchange Mailbox Add-Ons'</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="configOptionMailboxes" value="1" onchange="$('.configOptionMailboxes').toggle();">
						</td>
					</tr>
					<tr style="display:none;" class="configOptionMailboxes">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;">Username Field Description</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="packagesUsernameDescription"/>
						</td>
					</tr>
					<tr>
						<input type="hidden" name="configOptionVMQuota" size="25" value="0">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;font-weight:bold;">Create 'VMware Quota Add-Ons'</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="checkbox" name="configOptionVMQuota" value="1" onchange="$('.configOptionVMQuota').toggle();">
						</td>
					</tr>
					<tr style="display:none;" class="configOptionVMQuota">
						<td class="fieldlabel" style="text-align:right;padding-right:10px;width:50%;">Username Field Description</td>
						<td class="fieldarea" style="text-align:left;padding-left:10px;width:50%;">
							<input type="text" name="packagesUsernameDescription"/>
						</td>
					</tr>
					<tr>
						<td class="fieldarea" colspan="2" style="text-align:center;">
							<input type="submit" value="Install Configurable Options" class="btn success">
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		*/ ?>