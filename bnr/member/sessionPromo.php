<?php 

$promoType = '';
$promoType = isset($_GET['subNav'])?$_GET['subNav']:'';
$info = isset($_GET['info'])?$_GET['info']:'';


if ($promoType == "carousel"){
	?>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>

	<style>
		/* Make the image fully responsive */
		.carousel-inner img {
			width: 100%;
			height: 100%;
		}
	</style>
	<div class="row">
		<!--
		<div class="col-md-5">
				<div class="card card-product" style="margin-top: 60px">
						<div class="card-header card-header-image" data-header-animation="true">
								<a href="#">
										<img class="img" src="../images/promoMaterials/bonus_up.jpeg">
								</a>
						</div>
						<div class="card-body">
							
								<div class="card-actions text-center">
										<button type="button" class="btn btn-danger btn-link fix-broken-card">
												<i class="material-icons">build</i> Fix Header!
										</button>
										<button type="button"  class="btn btn-default btn-link" rel="tooltip" data-placement="bottom" title="View">
												<i class="fa fa-eye"></i> View detail
										</button>
										Bonus increases by 100%
								</div>
							
								<h4 class="card-title">
										<a href="#">Earn $1000 Extra Commission</a>
								</h4>
								<div class="card-description small">
										by recruiting new members with a total turnover of $5000
								</div>
						</div>
						<div class="card-footer">
								<div class="price">
										<h4>Left & Right : $5,000</h4>
								</div>
								<div class="stats">
										<p class="card-category"><i class="material-icons">card_membership</i> Limited Promo</p>
								</div>
						</div>
				</div>
		</div>
		<div class="col-md-5">
				<div class="card card-product" style="margin-top: 60px">
						<div class="card-header card-header-image" data-header-animation="true">
								<a href="#">
										<img class="img" src="../images/events/happy_fasting.jpeg">
								</a>
						</div>
						<div class="card-body">
							
								<div class="card-actions text-center">
										<button type="button" class="btn btn-danger btn-link fix-broken-card">
												<i class="material-icons">build</i> Fix Header!
										</button>
										<button type="button"  class="btn btn-default btn-link" rel="tooltip" data-placement="bottom" title="View">
												<i class="fa fa-eye"></i> View detail
										</button>
										Happy Fasting
								</div>
							
								<h4 class="card-title">
										<a href="#">Earn $1000 Extra Commission</a>
								</h4>
								<div class="card-description small">
										by recruiting new members with a total turnover of $5000
								</div>
						</div>
						<div class="card-footer">
								<div class="price">
										<h4>Left & Right : $5,000</h4>
								</div>
								<div class="stats">
										<p class="card-category"><i class="material-icons">card_membership</i> Limited Promo</p>
								</div>
						</div>
				</div>
		</div>
	-->
	<div class="col-md-6">
		<div id="carousel1" class="carousel slide card card-body" data-ride="carousel">
			<ul class="carousel-indicators">
				<li data-target="#carousel1" data-slide-to="0" class="active"></li>
				<li data-target="#carousel1" data-slide-to="1"></li>
				<li data-target="#carousel1" data-slide-to="2"></li>
			</ul>
			<div class="carousel-inner">
				<div class="carousel-item active">
					<img src="../images/events/langkahsukses.jpeg" alt="Langkah Sukses" width="480" height="480">
					<div class="carousel-caption">
								<!-- <h3>Business Presentation</h3>
									<p>7 Des 2019 @Hotel Grand Tjokro, Jkt</p> -->
								</div>
							</div>
							<div class="carousel-item">
								<img src="../images/events/followsosmed.jpeg" alt="Follow Sosmed" width="480" height="480">
								<div class="carousel-caption">
								<!-- <h3>Business Presentation</h3>
									<p>7 Des 2019 @Hotel Grand Tjokro, Jkt</p> -->
								</div>
							</div>
							<div class="carousel-item">
								<img src="../images/events/perusahaantepat.jpeg" alt="Perusahaan yang Tepat" width="480" height="480">
								<div class="carousel-caption">
								<!-- <h3>Merry Christmas</h3>
									<p></p> -->
								</div>
							</div>
						</div>

						<a class="carousel-control-prev" href="#carousel1" data-slide="prev">
							<span class="carousel-control-prev-icon"></span>
						</a>
						<a class="carousel-control-next" href="#carousel1" data-slide="next">
							<span class="carousel-control-next-icon"></span>
						</a>
					</div>
				</div>
				<div class="col-md-6">
					<div id="carousel2" class="carousel slide card card-body" data-ride="carousel">
						<ul class="carousel-indicators">
							<li data-target="#carousel2" data-slide-to="0" class="active"></li>
							<li data-target="#carousel2" data-slide-to="1"></li>
							<!--<li data-target="#carousel2" data-slide-to="2"></li>-->
						</ul>
						<div class="carousel-inner">
							<div class="carousel-item active">
								<img src="../images/events/Idul Adha 2020.jpg" alt="Idul Adha 2020" width="480" height="480">
								<div class="carousel-caption">
								<!-- <h3>Business Presentation</h3>
									<p>7 Des 2019 @Hotel Grand Tjokro, Jkt</p> -->
								</div>
							</div>
							<div class="carousel-item">
								<img src="../images/events/HUT RI.jpg" alt="HUT RI 2020" width="480" height="480">
								<div class="carousel-caption">
								</div>
							</div>
							<!--<div class="carousel-item">-->
							<!--	<img src="../images/events/lahirPancasila.jpeg" alt="Hari Lahir Pancasila" width="480" height="480">-->
							<!--	<div class="carousel-caption">-->
							<!--	</div>-->
							<!--</div>-->
						</div>

						<a class="carousel-control-prev" href="#carousel2" data-slide="prev">
							<span class="carousel-control-prev-icon"></span>
						</a>
						<a class="carousel-control-next" href="#carousel2" data-slide="next">
							<span class="carousel-control-next-icon"></span>
						</a>
					</div>
				</div>
		<!--
		<div class="col-md-6">
				<div id="carousel2" class="carousel slide card card-body" data-ride="carousel">
					<ul class="carousel-indicators">
						<li data-target="#carousel2" data-slide-to="0" class="active"></li>
						<li data-target="#carousel2" data-slide-to="1"></li>
					<!- -
						<li data-target="#carousel2" data-slide-to="2"></li>
					- ->
					</ul>
					<div class="carousel-inner">
						<div class="carousel-item active">
							<img src="../images/promoMaterials/bnssponsor.jpg" alt="Ramadan" width="1100" height="500">
							<!- -
							<div class="carousel-caption">
								<h3>EXTRA Discount</h3>
								<p></p>
							</div>   
						- ->
						</div>
						<div class="carousel-item">
							<img src="../images/promoMaterials/newmemberdisc.jpg" alt="Bonus02" width="1100" height="500">
							<!- -
							<div class="carousel-caption">
								<h3>EXTRA Commission</h3>
								<p>2nd bonus is yours</p>
							</div>   
						- ->
						</div>
						<!- -
						<div class="carousel-item">
							<img src="../images/promoMaterials/samplePromo2.jpg" alt="Bonus03" width="1100" height="500">
							<div class="carousel-caption">
								<h3>Best One</h3>
								<p>Contratulation, You're great!</p>
							</div>   
						</div>
					- ->
					</div>
					<a class="carousel-control-prev" href="#carousel2" data-slide="prev">
						<span class="carousel-control-prev-icon"></span>
					</a>
					<a class="carousel-control-next" href="#carousel2" data-slide="next">
						<span class="carousel-control-next-icon"></span>
					</a>
				</div>
			-->
		</div>
	</div>


	<?php

}else if ($promoType == "headerAnimation"){

	?>

	<p>&nbsp;</p><p>&nbsp;</p>
	<div class="row">
		<div class="col-md-6">
			<div class="card card-product">
				<div class="card-header card-header-image" data-header-animation="true">
					<a href="#">
						<img class="img" src="../images/promoMaterials/newpairsoon.jpg">
					</a>
				</div>
				<div class="card-body">
							<!--
								<div class="card-actions text-center">
										<button type="button" class="btn btn-danger btn-link fix-broken-card">
												<i class="material-icons">build</i> Fix Header!
										</button>
										<button type="button"  class="btn btn-default btn-link" rel="tooltip" data-placement="bottom" title="View">
												<i class="fa fa-eye"></i> View detail
										</button>
								</div>
							-->
							<h4 class="card-title">
								<a href="#">Get ready! <br>We will be here soon to bring more benefits to you</a>
							</h4>
							<div class="card-description">
								For those of you who want to trade with a more aggressive pair, more profit and risk.
							</div>
						</div>
						<div class="card-footer">
							<div class="price">
								<h4></h4>
							</div>
							<div class="stats">
								<!-- <p class="card-category"><i class="material-icons">card_membership</i> Limited Promo</p> -->
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card card-product">
						<div class="card-header card-header-image" data-header-animation="true">
							<a href="#">
								<img class="img" src="../images/promoMaterials/newpairsoon.jpg">
							</a>
						</div>
						<div class="card-body">
							<!--
								<div class="card-actions text-center">
										<button type="button" class="btn btn-danger btn-link fix-broken-card">
												<i class="material-icons">build</i> Fix Header!
										</button>
										<button type="button"  class="btn btn-default btn-link" rel="tooltip" data-placement="bottom" title="View">
												<i class="fa fa-eye"></i> View detail
										</button>
								</div>
							-->
							<h4 class="card-title">
								<a href="#">Get ready! <br>We will be here soon to bring more benefits to you</a>
							</h4>
							<div class="card-description">
								For those of you who want to trade with a more aggressive pair, more profit and risk.
							</div>
						</div>
						<div class="card-footer">
							<div class="price">
								<h4></h4>
							</div>
							<div class="stats">
								<!-- <p class="card-category"><i class="material-icons">card_membership</i> Limited Promo</p> -->
							</div>
						</div>
					</div>
				</div>
		<!--
		<div class="col-md-4">
				<div class="card card-product">
						<div class="card-header card-header-image" data-header-animation="true">
								<a href="#">
										<img class="img" src="../images/promoMaterials/Macbook_Air_Campaign.png">
								</a>
						</div>
						<div class="card-body">
							<! --
								<div class="card-actions text-center">
										<button type="button" class="btn btn-danger btn-link fix-broken-card">
												<i class="material-icons">build</i> Fix Header!
										</button>
										<button type="button" class="btn btn-default btn-link" rel="tooltip" data-placement="bottom" title="View">
												<i class="fa fa-eye"></i> View detail
										</button>
								</div>
							- ->
								<h4 class="card-title">
										<a href="#">MacBook Air</a>
								</h4>
								<div class="card-description">
										a prestigious MacBook Air will complement every successful activity of your business
								</div>
						</div>
						<div class="card-footer">
								<div class="price">
										<h4>Left & Right : $20,000</h4>
								</div>
								<div class="stats">
										<p class="card-category"><i class="material-icons">card_membership</i> Limited Promo</p>
								</div>
						</div>
				</div>
		</div>
		<div class="col-md-4">
				<div class="card card-product">
						<div class="card-header card-header-image" data-header-animation="true">
								<a href="#">
										<img class="img" src="../images/promoMaterials/ramadan.png">
								</a>
						</div>
						<div class="card-body">
							<! --
								<div class="card-actions text-center">
										<button type="button" class="btn btn-danger btn-link fix-broken-card">
												<i class="material-icons">build</i> Fix Header!
										</button>
										<button type="button" class="btn btn-default btn-link" rel="tooltip" data-placement="bottom" title="View Off">
												<i class="fa fa-eye-slash"></i> View detail
										</button>
								</div>
							- ->
								<h4 class="card-title">
										<a href="#">Ramadan Mubarak</a>
								</h4>
								<div class="card-description">
										We would like to wish you <br>to have strength and patience during fasting.
								</div>
						</div>
						<div class="card-footer">
								<div class="price">
										<h4>Have a blessed Ramadan!</h4>
								</div>
								<div class="stats">
										<p class="card-category"><i class="material-icons">brightness_4</i></p>
								</div>
						</div>
				</div>
		</div>
	-->
</div>

<?php

}

?>