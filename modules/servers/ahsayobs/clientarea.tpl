<div align="left">
<h2>Backup Account Details</h2>
</div>
<table width="100%" cellspacing="0" cellpadding="0" class="frame">
	<tbody>
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="10" cellspacing="0">
					<tbody>
						<tr>
							<td class="fieldarea" width="150">
								Access Backup Account:
							</td>
							<td>
								<form action="clientarea.php?action=productdetails#account-info" method="post">
									<input type="hidden" name="id" value="{$serviceid}"> 
									<input type="hidden" name="modop" value="custom"> 
									<input type="hidden" name="a" value="infopage"> 
									<input type="submit" value="Launch" class="btn success">
								</form>
							</td>
						</tr>
						<tr>
							<td class="fieldarea">
								Help and Downloads:
							</td>
							<td>
								<form action="clientarea.php?action=productdetails#help" method="post">
									<input type="hidden" name="id" value="{$serviceid}"> 
									<input type="hidden" name="modop" value="custom"> 
									<input type="hidden" name="a" value="infopage"> 
									<input type="submit" value="Help" class="btn info">
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
