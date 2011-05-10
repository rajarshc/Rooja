<?php
	$title = "Rooja Fashion Live Sales";
	$page = "home";
	require('_header.php');

?>

<section id="account">
	<div class="container">
		<a href="#" class="needAssistance" title="Need Assitance? Talk to a live rep"><img src="" alt="Question mark icon"> Need Assistance</a>
		
	<section id="accountSidebar">
		<h1>Account</h1>
		<ul id="tocLinks">
			<li><a href="">Profile Details</a></li>
			<li><a href="">Account Dashboard</a></li>
			<li><a href="">Account Information</a></li>
			<li><a href="">Shipping Addresses</a></li>
			<li><a href="">My Orders</a></li>
			<li><a href="">Invitations</a></li>
		</ul>
		
		<h2>My Bag</h2>
		<p id="noItems">You have no items in your shopping cart</p>
	</section>
	
	<section id="invitations">
		<h2>Invitations</h2>
		<p>Invite 10 friends to receive free shipping on your next order!</p>
		
		<article id="tracker">
			<h3>Track your Progress</h3>
			<p class="trackerCounter">3 more until free shipping!</p>
			<div id="progressBar"><div id="progressAmt" style="width:70%;"></div></div>
		</article>
		
		<article id="inviteByEmail">
			<h2>Invite by Email</h2>
			<p>Invite a friend and receive $25 to spend on Rooja!</p>
			
			<ul>
				<li><strong>Name</strong> <span>John Smith</span></li>
				<li><strong>Login/Email</strong> <span>john.smith@gmail.com</span></li>
				<li><strong>Password</strong> <span>********</span></li>
			</ul>
			
			<h2>Address Book <a href="#">Manage</a></h2>
				<ul>
					<li><strong>Billing:</strong> <span>John Smith <br> 329 Appleday Lane <br> Los Angeles, CA 90024 <br> T: 3238756678</span></li>
					<li><strong>Shipping:</strong> <span>John Smith <br> 329 Appleday Lane <br> Los Angeles, CA 90024 <br> T: 3238756678</span></li>
				</ul>
		</article>
		
		<article id="inviteFriends">
			<h3>Invite your friends to Rooja</h3>
			<p>Share this link with your friends to earn Rooja credits!</p>
			<input type="text" id="personalLink" name="personalLink" value="http://www.rooja.com/invite/drubramlett"> <input type="submit" name="copyBtn" id="copyBtn" value="Copy">
			<ul id="socialLinks">
				<li><a href="#"><img src="_images/icons/facebook-blue.png" alt="facebook icon"></a></li>
				<li><a href="#"><img src="_images/icons/twitter-teal.png" alt="twitter icon"></a></li>
				<li><a href="#"><img src="_images/icons/plaxo.png" alt="plaxo icon"></a></li>
			</ul>
		</article>
		
		<section id="inviteStatus">
			<article id="approved">
				<h3><img src=""> Approved</h3>
				<ul>
					<li>Person Name</li>
					<li>Person Name</li>
					<li>Person Name</li>
					<li>Person Name</li>
					<li>Person Name</li>
					<li>Person Name</li>
					<li>Person Name</li>
				</ul>
			</article>
			
			<article id="pending">
				<h3><img src=""> Pending</h3>
				<ul>
					<li>emerson@gophilosophie.com</li>
					<li>skot@gophilosophie.com</li>
				
				</ul>
			</article>
		</section>
	</section>
	
	</div>
</section>

<?php require('_footer.php'); ?>