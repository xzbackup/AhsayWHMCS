<?php global $_ADDONLANG; ?>
<link rel="stylesheet" type="text/css" href="modules/addons/ahsayobs/templates/style.css" />
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/themes/base/jquery-ui.css" type="text/css" media="all" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/jquery-ui.min.js"></script>
<script type="text/javascript" src="modules/addons/ahsayobs/templates/combobox.js"></script>


<div class="page-header">
    <div class="styled_title">
		<h1>
			<?php echo $_ADDONLANG['clientarea_infopage_title']; ?> <small><?php echo $_ADDONLANG['clientarea_infopage_desc']; ?> </small>
		</h1>
	</div>
</div>
<div class="backup_account">
	<?php if (!$licenseError): ?>
	
	<?php if (sizeof($backuplinks) > 1): ?>
	<fieldset>
		<div class="formrow">
            <label for="combolist"><?php echo $_ADDONLANG['clientarea_infopage_jumptousers']; ?></label>
            <div class="combowrap">
                <select id="backuplist" name="id">
                	<option value="#"><?php echo $_ADDONLANG['clientarea_infopage_jumpto']; ?></option>
					<?php foreach($backuplinks as $username => $link): ?>
					<option value="<?php echo $link;?>"><?php echo $username; ?></option>
					<?php endforeach; ?>
				</select>
            </div>
			<button type="button" class="ui-widget" onclick="window.location=$('#backuplist').attr('value');"><?php echo $_ADDONLANG['clientarea_infopage_switchuser']; ?></button>
        </div>
    </fieldset>
	<?php endif; ?>
	<div id="tabs">
		<ul>
			<li><a href="#account-info"><?php echo $_ADDONLANG['clientarea_infopage_accountinfo']; ?></a></li>
			<?php if ($backupTab): ?><li><a href="#backup-account"><?php echo $backupTabTitle ? $backupTabTitle : "Backup Account"; ?></a></li><?php endif; ?>
			<?php /* <li><a href="#domain-stats">Domain Stats</a></li> */?>
			<li><a href="#help">Help</a></li>
		</ul>
		<div id="account-info">
			<table width="100%" cellpadding="2">
				<tbody>
					<tr>
						<td colspan="2">
							<p>
								<?php echo $_ADDONLANG['clientarea_account_info']; ?><br/>
								<?php echo $_ADDONLANG['clientarea_account_info2']; ?>
							</p>
						</td>
					</tr>
					<tr>
						<td class="fieldarea">
							<p><?php echo $_ADDONLANG['clientarea_infopage_backupuser']; ?>:</p>
						</td>
						<td>
							<p><?php echo $user_name; ?></p>
						</td>
					</tr>
					<?php if ($displayPassword == "0"): ?>
					<tr>
						<td class="fieldarea">
							<p><?php echo $_ADDONLANG['clientarea_infopage_backuppass']; ?>:</p>
						</td>
						<td>
							<p><?php echo $user_pass; ?></p>
						</td>
					</tr>
					<?php endif; ?>
					
					<?php if ($directUrl): ?>
					<tr>
						<td class="fieldarea">
							<p><?php echo $_ADDONLANG['clientarea_infopage_directurl']; ?>:</p>
						</td>
						<td>
							<p>
								<a href="<?php echo $directUrl; ?>" target="_new">
									<?php echo $directUrl; ?>
								</a>
							</p>
						</td>
					</tr>
					<?php endif; ?>
					
					<tr>
						<td class="fieldarea">
							<p><?php echo $_ADDONLANG['clientarea_infopage_changepass']; ?>:</p>
						</td>
						<td>
							<form method="post" action="clientarea.php?action=productdetails#tab2" id="change_password" name="change_password">
								<input type="hidden" name="id" value="<?php echo $id; ?>"> 
								<input type="hidden" name="x" value="<?php echo $id; ?>"> 
								<input type="hidden" name="y" value="<?php echo $id; ?>"> 
								<input type="submit" value="<?php echo $_ADDONLANG['clientarea_infopage_changepass']; ?>" style="background-color:#99ff99;color:#333;">
							</form>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php if ($backupTab): ?>
		<div id="backup-account" class="ui-widget">
			<div align="center" id="userframe" class="hide" style="width:100%;margin:0 auto;">
				<?php if ($showProfileButton): ?>
				<input type="button" value="<?php echo $_ADDONLANG['clientarea_infopage_profile']; ?>" class="btn info" section="profile">
				<?php endif; ?>
				<input type="button" value="<?php echo $_ADDONLANG['clientarea_infopage_backupsets']; ?>" class="btn primary" section="backupsets">
				<input type="button" value="<?php echo $_ADDONLANG['clientarea_infopage_fileexplorer']; ?>" class="btn success" section="fileexplorer">
				<input type="button" value="<?php echo $_ADDONLANG['clientarea_infopage_reports']; ?>" class="btn error" section="reports">
				<input type="button" value="<?php echo $_ADDONLANG['clientarea_infopage_statistics']; ?>" class="btn" section="stats">
				<?php if ($showLogButton): ?>
				<input type="button" value="<?php echo $_ADDONLANG['clientarea_infopage_log']; ?>" class="btn info" section="logs">
				<?php endif; ?>
			</div>
			
			<script type="text/javascript">
			function openFramePage(section){
				hostname = "<?php echo $serverhostname; ?>";
				username = "<?php echo $user_name; ?>";
				if (section == 'profile'){
					url = "/obs/user/editUserProfile.do";
				} else if (section == 'backupsets'){
					url = "/obs/user/editBackupSet.do";
				} else if (section == 'fileexplorer'){
					url = "/obs/user/showBackupFile.do";
				} else if (section == 'reports'){
					url = "/obs/user/showBackupReport.do";
				} else if (section == 'stats'){
					url = "/obs/user/showUserStorageStat.do";
				} else if (section == 'logs'){
					url = "/obs/user/showUserLog.do";
				}
				
				newurl = "http<?php if ($sslServer): ?>s<?php endif; ?>://" + hostname + url;// + "?username=" + username;
				console.log(newurl);
				$('#iframe').attr('src', newurl);
			}
			function reloadFrame(){
				console.log($(this));
				$(this).detach();
				$([
					'<iframe id="iframe" src=""',
			            'frameborder="0" allowtransparency="true" align="top" height="1000" width="100%" marginheight="0" marginwidth="0" vspace="0"',
			            'hspace="0" scrolling="auto" style="padding-top: 15px;">',
				    '</iframe>'
				].join("")).appendTo('#userframe');
				setTimeout(function(){
					openFramePage('profile');
				}, 500);
			}
			$(document).ready(function(){
				$('input[section]').click(function(){
					openFramePage($(this).attr('section'));
				});
				
			    $("#side_menu").toggle('fast');
			    $('#userframe').show();
			    $('#content_left').attr('style','width:920px;float:left;');
			});
			</script>

		</div>
		<?php endif; ?>
		
		<div id="help" style="visibility:hidden;">
			<?php echo $help_tab; ?>
		</div>
	</div>
	<?php else: ?>
	<div style="font-size:120%;width:100%;height:42px;"><?php echo $licenseError; ?></div>
	<?php endif; ?>
	
</div><!-- End demo -->

<?php if (!$licenseError): ?>
<iframe id="iframe" src="http<?php if ($sslServer): ?>s<?php endif; ?>://<?php echo $serverhostname; ?>/obs/user/logon.do?u=<?php echo $user_name; ?>&loginName=<?php echo $user_name; ?>&password=<?php echo $user_pass; ?>"
        frameborder="0" allowtransparency="true" align="top" height="1" width="1" marginheight="0" marginwidth="0" vspace="0"
        hspace="0" scrolling="auto" style="padding-top: 15px;">
</iframe>
<script type="text/javascript">


jQuery(document).ready(function(){
	$('#tabs').tabs({
		show: function(e, ui){
			$(ui.panel).css('visibility','visible');
			if (ui.panel.id == 'backup-account'){
				reloadFrame();
			}
		}
	});
	$("button.ui-widget").button();
	$('#domainlist').combobox();
});

</script>
<?php endif; ?>