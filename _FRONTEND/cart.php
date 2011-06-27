<?php
	$title = "Rooja Fashion Live Sales";
	$page = "home";
	require('_header.php');

?>

<section id="cart">
	<div class="container">
		<a href="#" class="needAssistance" title="Need Assitance? Talk to a live rep"><img src="" alt="Question mark icon"> Need Assistance</a>
	
	<h1>Shopping Cart</h1>
	<p>&ldquo;Sleek Racerback Tank&rdquo; was successfully added to your shopping cart!</p>
	
	<a href="#" class="proceedCheckout">Proceed to Checkout</a>
	
	<article id="cartDetails">
		<h3>Order #83741</h3>
		<table>
		
		</table>
		
		<div id="cartLinks">
			<a href="#">Continue Shopping</a>
			<a href="#">Update Shopping Cart</a>
		</div>
		
		<div id="promoAndShipping">
			<h3>Discount Codes</h3>
			<form>
				<input type="text" id="discount" name="discount">
				<input type="submit" id="couponBtn" name="couponBtn">
			</form>
			
			<h3>Estimate Shipping and Tax</h3>
			<input type="text" id="zip" name="zip">
			<input type="text" id="region" name="region">
			<input type="submit" id="getQuote" name="getQuote">
		</div>
		
		<article id="cartDetails">
			<div id="cartPrice">
				<h3>Your Current Total</h3>
				<p><span>Subtotal:</span> $82.57</p>
				<p><span>Shipping:</span> $5.00</p>
				<p class="total"><span>Grand Total:</span> $87.57</p>
			</div>
			
			<p><a href="#">Checkout with multiple addresses</a></p>
			
			<a href="#" class="proceedCheckout">Proceed to Checkout</a>
		</article>
	</article>
	
	</div>
</section>

<section id="relatedProducts">
	<h3>You may also like</h3>
	<ul>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
		<li><a href="#"><img src=""> <span class="numLikes">40</span></a></li>
	</ul>
</section>

<?php require('_footer.php'); ?>