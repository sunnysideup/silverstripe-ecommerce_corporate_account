<div id="CorporateAccountPage">

	<% if IsApprovedAccountGroup %>

	<div id="CorporateAccountPageAccountPastOrdersOuter" class="outerHolder">
		<h2><% _t("EcommerceCorporateAccount.OTHERUSERS", "Orders from") %> $GroupTitle</h2>
		<% include AccountPastOrders %>
	</div>

	<% end_if %>


</div>
