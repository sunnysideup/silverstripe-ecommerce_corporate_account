<div id="CorporateAccountPage" class="mainSection content-container noSidebar">

	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>

	<% if IsApprovedAccountGroup %>
	<div id="CorporateAccountPageAccountPastOrdersOuter" class="outerHolder">
		<h3><% _t("EcommerceCorporateAccount.ORDERSFROM", "Orders from") %> $GroupTitle</h3>
		<% include AccountPastOrders %>
	</div>
	<% end_if %>

	<% if Form %><div id="FormHolder">$Form</div><% end_if %>


</div>
