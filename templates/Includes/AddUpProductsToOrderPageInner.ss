<tr>
	<td class="name" id="Name_$RowNumber" >
		<input type="text"name="name_$RowNumber">
	</td>
	<td class="buyable" id="Buyable_$RowNumber">
		<select name="buyable_$RowNumber"  rel="$BuyableClassNameAndID">
			<option value="0">-- select product --</option>
		<% control Buyables %>
			<option value="{$ClassName}_{$ID}" rel="$CalculatedPrice">$Title @ $CalculatedPrice.Nice</option>
		<% end_control %>
		</select>
	</td>
	<td class="qty" id="Qty_$RowNumber"><input type="text" name="qty_$RowNumber" value="$Qty"></td>
	<td class="total" id="Total_$RowNumber">$Total</td>
</tr>

