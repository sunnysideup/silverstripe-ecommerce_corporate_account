<h1 class="pagetitle">$Title</h1>
<% if Content %><div id="ContentHolder">$Content</div><% end_if %>

<% if AddProductsToOrderRows %>
<form method="post" action="$Link(submit)" id="AddProductsToOrderRowsForm">
	<div class="reset"><a href="$Link(reset)/" title="reset">Reset</a></div>
	<table summary="Order Form" id="AddProductsToOrderRowsTable">
		<thead>
			<tr>
				<th class="name">Name</th>
				<th class="buyable">Product</th>
				<th class="qty">Quantity</th>
				<th class="total">Total</th>
			</tr>
		</thead>
		<tbody>
			<% control AddProductsToOrderRows %><% include AddUpProductsToOrderPageInner %><% end_control %>
		</tbody>
	</table>
	<div class="addProductsToOrderAddRows"><a href="$Link(addrow)/" title="add a row">Add Row</a></div>
	<div class="Actions">
		<input type="submit" value="review order" name="check" />
		<input type="submit" value="finalise order" name="submit" />
		<input type="hidden" value="0" name="rowNumbers" />
	</div>
</form>
<div id="AddProductsToOrderRowsResult"></div>
<% end_if %>
