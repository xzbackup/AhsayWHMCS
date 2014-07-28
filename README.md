AhsayWHMCS
==========

WHMCS provisioning module for the Ahsay OBS.

Template Integration
=========
This module allows for you to define links inside any template that will always list the backup accounts a WHMCS client may have so that they do not need to go through the My Services > View Details > Open navigation...

Example Template For Display Backup Account Links On A Template 
(Below code specifically for new 5.0 'Default' top navigation bar)

{if is_array($backuplinks)}
<ul>
	<li class="menu">
		<a href="#" class="menu" style="color:limegreen">Backup Users</a>
		<ul class="menu-dropdown">
		{foreach from=$backuplinks key=username item=link}
			<li><a href="{$link}">{$username}</a></li>
		{/foreach}
		</ul>
	</li>
</ul>
{/if}
