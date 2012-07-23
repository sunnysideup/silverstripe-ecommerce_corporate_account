<% if BillingAddress %><% control BillingAddress %>
		<% if Organisation %><% control Organisation %>
<table id="OrganisationTable" class="infotable">
	<tr>
		<th scope="col"><% _t("EcommerceCorporateAccount.FOR","For") %></th>
	</tr>
	<tr>
		<td>$CombinedCorporateGroupName</td>
	</tr>
</table>
		<% end_control %><% end_if %>
<% end_control %><% end_if %>
