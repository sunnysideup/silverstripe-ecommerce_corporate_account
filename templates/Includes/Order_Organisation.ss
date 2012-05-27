<% if BillingAddress %><% control BillingAddress %>
		<% if Organisation %><% control Organisation %>
<table id="AddressesTable" class="infotable">
	<tr>
		<th scope="col" width="100%"><% _t("OrderAddress.FOR","For") %></th>
	</tr>
	<tr>
		<td width="100%">$Title</td>
	</tr>
</table>
		<% end_control %><% end_if %>
<% end_control %><% end_if %>
