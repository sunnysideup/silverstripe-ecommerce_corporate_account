<div id="CorporateAccountPage">

	<% if IsApprovedAccountGroup %>

	<div id="CorporateAccountPageOtherAccountsOuter" class="outerHolder">
		<h3><% _t("EcommerceCorporateAccount.USERACCOUNTSFOR", "User accounts for") %> $GroupTitle</h3>
		<% if GroupMembers %>
			<ul>
			<% control GroupMembers %>
				<li>$Name ($Email)</li>
			<% end_control %>
			</ul>
		<% else %>
			<p class="message"><% _t("EcommerceCorporateAccount.NOOTHERUSERS", "There are no other users for this account") %></p>
		<% end_if %>
	</div>

	<div id="CorporateAccountPageAccountPastOrdersOuter" class="outerHolder">
		<h2><% _t("EcommerceCorporateAccount.OTHERUSERS", "Orders from") %> $GroupTitle</h2>
		<% include AccountPastOrders %>
	</div>

	<% end_if %>

	<div id="CorporateAccountPageOrganisationFormOuter" class="outerHolder">
		<h3><% _t("EcommerceCorporateAccount.UPDATEYOURDETAILS", "Update your details") %></h3>
		$OrganisationForm
	</div>


</div>
