<?php
	$title = "Rooja Fashion Live Sales";
	$page = "home";
	require('_header.php');

?>

<section id="saleDetails">
	<div class="container">
		
		<h2 id="saleDetails">Gucci Galore ends March 3rd at 8:00pm</h2>
		<a href="sales.php" class="backTo fl" title="Back to sale">Back to sale</a>
		<a href="#" class="needAssistance fr" title="Need Assitance? Talk to a live rep"><img src="_images/icons/question-mark.png" alt="Question mark icon"> Need Assistance</a>
		<section id="productDetails" class="col4 clear">
			<section id="gallery" class="col2 fl">
				&nbsp;
			</section>
			<section id="productInfo" class="col1 fl">
				<h1>Slim broken - in tee</h1>
				<ul id="productBreakdown">
					<li><a href="#">Details</a></li>
					<li><a href="#">Shipping &amp; Returns</a></li>
				</ul>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut id libero id ante mollis facilisis. Sed ultrices hendrerit tincidunt. Integer elementum pretium libero, quis rhoncus velit tempor nec. Fusce ipsum ligula, scelerisque ac ultricies non, tincidunt consectetur nunc. Vestibulum interdum pretium justo.</p> 
				
				<p>Cras mollis mollis mauris, et varius orci eleifend in. Fusce lectus eros, luctus at cursus eu, malesuada a lorem. Morbi a elementum arcu. Sed ligula velit, volutpat a lacinia vitae, volutpat auctor lectus. Morbi mattis pretium massa, eget iaculis tellus viverra auctor.</p>
				
				<div class="shareThis">
					<p>Share this sale with your friends</p>
					<a href="#"><img src="_images/icons/facebook-blue-24.png" alt="facebook icon"></a>
					<a href="#"><img src="_images/icons/twitter-teal-24.png" alt="twitter icon"></a>
					<a href="#"><img src="_images/icons/email-24.png" alt="facebook icon"></a>
				</div>
			</section>
			
			<section id="purchaseProduct" class="col1 fl noMargin">
				<section id="reverseAuction">
					
					<p id="likeThis">154 people like this item <a href="#" id="likeBtn" class="tr fr">Like this item</a></p>
					
					<figure id="likeDetails" class="sideAd">
						<p>Want to see the price drop? Like this item!</p>
					</figure>
				</section>
				<section id="productOptions">
					<p class="startingPrice">Starting Price: <strike>$1250</strike></p>
					<p class="currentPrice">Current Price: <span>$100.00</span></p>
					
					<ul id="colors">
						<li><a href="#"></a></li>
						<li><a href="#"></a></li>
						<li><a href="#"></a></li>
						<li><a href="#"></a></li>
					</ul>
					<ul id="sizes" class="fl">
						<li><a href="#">XS</a></li>
						<li><a href="#">S</a></li>
						<li><a href="#">M</a></li>
						<li><a href="#">L</a></li>
					</ul>
					<a href="#" class="sizeChart fr">Size Chart</a>
					<br class="clear">
					<input type="text" id="qty" name="qty" value="1"><label for="qty">Quantity</label>
				</section>
				
				<section id="addToBag">
					<a href="#" class="tr" id="addToBagBtn"></a>
					<p id="estimatedShippingDate">Estimated to ship on May 7th</p>
				</section>
				
				<section id="prevNextLinks">
					<a href="#" class="fl" id="prevProduct"><p>Prev</p> <img src="_images/photos/product/prev.jpg"></a>
					<a href="#" class="fr" id="nextProduct"><p>Next</p> <img src="_images/photos/product/next.jpg"></a>
				</section>
			</section>
		</section>
		<br class="clear">
	</div>
</section>

<section id="commentsRelated">
	<div class="container">
		<section id="comments" class="col2 fl">
			<h3>What people are saying...</h3>
		</section>
		<section id="related" class="col2 fl noMargin">
			<h3>You might also like</h3>
			<ul>
				<li><a href="#"><img src="_images/photos/product/related-1.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
				<li><a href="#"><img src="_images/photos/product/related-2.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
				<li><a href="#"><img src="_images/photos/product/related-3.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
				<li><a href="#"><img src="_images/photos/product/related-4.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
				<li><a href="#"><img src="_images/photos/product/related-5.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
				<li><a href="#"><img src="_images/photos/product/related-6.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
				<li><a href="#"><img src="_images/photos/product/related-7.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
				<li><a href="#"><img src="_images/photos/product/related-8.jpg"> <span class="numLikes"><img src="_images/icons/thumbs-up.png" alt="Thumbs Up icon"> 40</span></a></li>
			</ul>
		</section>
		<br class="clear">
	</div>
</section>

<?php require('_footer.php'); ?>