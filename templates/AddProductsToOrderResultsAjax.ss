<div class="addUpProductsToOrderPageSummary">
	<p>$Message</p>
<% if BuyableSummary %>
	<div class="buyableSummary">
		<h4>Summary of Products</h4>
		<ul>
			<% control BuyableSummary %>
			<li><span class="title">$Title</span><span class="semiColon">: </span><span class="quantity">$Qty</span></li>
		<% end_control %>
		</ul>
	</div>
<% end_if %>

<% if NameSummary %>
	<div class="nameSummary">
		<h4>Summary by Name</h4>
		<ul>
		<% control NameSummary %>
			<li>
				<span class="name">$Name</span>
				<span class="equal">= </span>
				<span class="totalInner">$SumTotal</span>
				<ul>
				<% control Buyables %>
					<li>
						<span class="title">$Buyable.Title</span>
						<span class="semiColon">: </span>
						<span class="quantity">$Qty</span>
						<span class="times"> x </span>
						<span class="price">$Price</span>
						<span class="equal">= </span>
						<span class="total">$ItemTotal</span>
					</li>
			<% end_control %>
				</ul>
			</li>
		<% end_control %>
		</ul>
	</div>
<% end_if %>

</div>
