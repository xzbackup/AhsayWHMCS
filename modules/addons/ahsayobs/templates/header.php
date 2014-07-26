<script type="text/html" id="linktemplate">
	
	<?php /* <?php if (sizeof($pkgs) == 1): ?>
	<?php $pkg = array_pop($pkgs); ?>
		<li><a href="clientarea.php?action=productdetails&id=<?php echo $pkg['id']; ?>&modop=custom&a=infopage" style=""><?php echo $pkg['username']; ?></a></li>
	<?php elseif (sizeof($pkgs) > 1): ?>
		
	<?php endif; ?>*/ ?>
	<ul id="ahsay">
		<li class="menu">
			<a href="#" class="menu" style="color:limegreen">Backup</a>
			<ul class="menu-dropdown">
		  	<?php foreach($pkgs as $pkg): ?>
				<li><a href="clientarea.php?action=productdetails&id=<?php echo $pkg['id']; ?>&modop=custom&a=infopage"><?php echo $pkg['username']; ?></a></li>
				<li class="divider"></li>
			<?php endforeach; ?>
			</ul>
		</li>
	</ul>
</script>
<script type="text/javascript">
$(document).ready(function(){
	if ($('.nav.secondary-nav').length != 0){
		$($('#linktemplate')[0].innerHTML).insertBefore('.nav.secondary-nav');
		jQuery(".dropdown-toggle,#ahsay a.menu").click(function(e) {
			if (jQuery(this).parent("li").hasClass("open")) {
				jQuery("ul").find('li').removeClass('open');
			} else {
				jQuery("ul").find('li').removeClass('open');
				jQuery(this).parent("li").addClass('open');
			}
			return false;
		});
	} else if ($('#side_menu ul').length != 0){
		$('<li>'+$('#linktemplate')[0].innerHTML+'</li>').appendTo('#side_menu ul');
	}
	
});
</script>