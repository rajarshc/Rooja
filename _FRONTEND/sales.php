<?php
	$title = "Rooja Fashion Live Sales";
	$page = "home";
	require('_header.php');

?>

<section id="saleDetails">
	<div class="container">
		<a href="home.php" class="backTo fl" title="Back to Home">Back to home</a>
		<a href="#" class="needAssistance fr" title="Need Assitance? Talk to a live rep" class="fr"><img src="_images/icons/question-mark.png" alt="Question mark icon"> Need Assistance</a>
		
	<section id="saleInfo" class="col1 fl clear">
		<h1>Gucci Galore</h1>
		<p class="saleEnding">This sale ends March 3rd at 8:00pm</p>
		
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac ligula ac magna bibendum dictum nec eu libero. In hac habitasse platea dictumst. Nullam a nisi quis odio lacinia vehicula. Suspendisse scelerisque lacus et tortor pretium volutpat. Etiam a rutrum dui. In quis mauris massa.</p>
		
		<div class="shareThis">
			<p>Share this sale with your friends</p>
			<a href="#"><img src="_images/icons/facebook-blue-24.png" alt="facebook icon"></a>
			<a href="#"><img src="_images/icons/twitter-teal-24.png" alt="twitter icon"></a>
			<a href="#"><img src="_images/icons/email-24.png" alt="facebook icon"></a>
		</div>
		
		<aside id="filterSale">
			<h3>Find it Fast</h3>
			<form>
			<select name="size" id="size"></select>
			<select name="type" id="type"></select>
			<select name="style" id="style"></select>
			<select name="Sort by" id="Sort by"></select>
			<input type="submit" id="filterSaleBtn" name="filterSaleBtn" value="Search" class="submitBtn">
			<a href="#">Clear Filters</a>
			</form>
			
		</aside>
		
		<figure id="inviteFriends" class="sideAd">
			<p>Invite 10 friends to Rooja and get free shipping for life</p>
		</figure>
	</section>
	
	<section id="saleProducts" class="col3 fl">
		<article class="product col1 fl">
			<a href="#"><img src="_images/photos/sales/1.jpg" alt="product name"></a>
			<article class="productCore">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
		<article class="product col1 fl">
			<a href="#"><img src="_images/photos/sales/2.jpg" alt="product name"></a>
			<article class="productCore">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
		<article class="product col1 fl noMargin">
			<span class="numLeft">2</span>
			<a href="#"><img src="_images/photos/sales/3.jpg" alt="product name"></a>
			<article class="productCore soldOut">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
		<article class="product col1 fl">
			<a href="#"><img src="_images/photos/sales/4.jpg" alt="product name"></a>
			<article class="productCore">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
		<article class="product col1 fl">
			<a href="#"><img src="_images/photos/sales/5.jpg" alt="product name"></a>
			<article class="productCore">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
		<article class="product col1 fl noMargin">
			<a href="#"><img src="_images/photos/sales/6.jpg" alt="product name"></a>
			<article class="productCore">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
		<article class="product col1 fl">
			<a href="#"><img src="_images/photos/sales/7.jpg" alt="product name"></a>
			<article class="productCore">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
		<article class="product col1 fl">
			<a href="#"><img src="_images/photos/sales/8.jpg" alt="product name"></a>
			<article class="productCore">
				<span class="numLikes">121</span>
				<p class="price"><span class="livePrice">$100.00</span> Sold Out</p>
				<h2 class="productName">Name of the product</h2>
			</article>
		</article>
	</section>
	
	<a href="#" class="backToTop fr">Back to Top</a>
	<br class="clear">
	</div>
</section>

<?php require('_footer.php'); ?>