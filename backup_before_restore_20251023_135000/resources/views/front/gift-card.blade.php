@extends('front.layout.front-layout')
@push('title', 'Gift Card')
@section('content')
<style>
	.transparent_header {
		border-bottom: 1px solid #e1e1e1;
	}

	.header_area.sticky {
		position: static;
	}

	.tool-page-menu {
		display: block
	}
	
</style>
<section class="section_collage_tool">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-3 p-0">
				<div class="tool-text">
					<div class="gift_car-left tool-accordion">
						<div class="tool-top-head">
							<h1 class="tool-head">Digital Gift Card</h1>
						</div>

						<div class="card-div">
							<div class="card-frame">
								<h4>Choose a design</h4>
								<div class="row">
									<input type="hidden" name="gitCardType" id="gitCardType">
									<div class="col-sm-4 col-4" onclick="return giftCardDesign(this,'yellow')">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-card" name="card-Size" id="card1" checked>
											<label class="add-active" for="card1">
												<img src="{{asset('assets/images/yellow_gift_card.png')}}" >
											</label>
										</div>
									</div>
									<div class="col-sm-4 col-4" onclick="return giftCardDesign(this,'green')">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-card" name="card-Size" id="card2">
											<label class="add-active" for="card2">
												<img src="{{asset('assets/images/green_gift_card.png')}}" >
											</label>
										</div>
									</div>
									<div class="col-sm-4 col-4" onclick="return giftCardDesign(this,'red')">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-card" name="card-Size" id="card3">
											<label class="add-active" for="card3">
												<img src="{{asset('assets/images/red_gift_card.png')}}" >
											</label>
										</div>
									</div>
								</div>
							</div>
							<div class="card-frame">
								<h4>Who's the lucky recipient?</h4>
								<input class="form-control" id="recipientName" type="text" placeholder="Recipient's email">
								<span class="text-danger" id="recipientNameError"></span>
							</div>
							<div class="card-frame">
								<h4>Choose a card value</h4>
								<div class="car-flex">
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,50)" id="US50" checked>
											<label class="add-active" for="US50">
												<div class="con_framebox">
													<h5>US$50</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,100)" id="us100">
											<label class="add-active" for="us100">
												<div class="con_framebox">
													<h5>US$100</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,150)" id="us150">
											<label class="add-active" for="us150">
												<div class="con_framebox">
													<h5>US$150</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,200)" id="us200">
											<label class="add-active" for="us200">
												<div class="con_framebox">
													<h5>US$200</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,250)" id="us250">
											<label class="add-active" for="us250">
												<div class="con_framebox">
													<h5>US$250</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,300)" id="us300">
											<label class="add-active" for="us300">
												<div class="con_framebox">
													<h5>US$300</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,350)" id="us350">
											<label class="add-active" for="us350">
												<div class="con_framebox">
													<h5>US$350</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values">
										<div class="form-check frame-box">
											<input type="radio" class="btn-check active-cost" name="frame-Size" onclick="return addGiftCardCost(this,400)" id="us400">
											<label class="add-active" for="us400">
												<div class="con_framebox">
													<h5>US$400</h5>
												</div>
											</label>
										</div>
									</div>
									<div class="card-values input-custome">
										<div class="form-check frame-box">
											<div class="form-check frame-box">
												<input type="radio" class="btn-check active-cost" name="frame-Size" id="usCustom">
												<label class="add-active" for="usCustom">
													<div class="con_framebox">
														<input type="text" class="form-control " placeholder="Custom" onkeyup="return addGiftCardCost(this,this.value)" id="usCustomValue">
													</div>
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card-frame">
								<h4>Message</h4>
								<textarea class="form-control" id="gift_card_message"></textarea>
							</div>
							<div class="card-frame d-none">
								<h4>When should we send the gift card?</h4>
								<div class="row">
									<div class="col-sm-6">
										<input class="form-control" type="date">
									</div>
									<div class="col-sm-6">
										<input class="form-control" type="time">
									</div>
								</div>
							</div>
							<div class="card-frame d-none">
								<h4>Who is it from?</h4>
								<input type="text" placeholder="Sender's name" class="form-control"></textarea>
							</div>
							<div class="card-frame">
								<button type="button" id="purchase_button" onclick="return purchaseGiftCard()" class="btn btn-primary btn-lg w-100">Purchase</a>
							</div>

							<p>Delivered by email, this gift card never expires</p>


						</div>

					</div>


				</div>
			</div>
			<div class="col-sm-9 pe-0" id="gitCardDiv">

			</div>
		</div>
	</div>
</section>
@endsection
@push('js')
<script>
	window.onload = function() {
		getGiftCard();
	};

	function giftCardDesign(element, gitCardType) {
		var radios = document.getElementsByName('card-Size');

		// Loop through each radio and remove 'checked' attribute
		radios.forEach(function(radio) {
			radio.checked = false;
		});

		// Add 'checked' attribute to the clicked radio button
		var clickedRadio = element.querySelector('input[type="radio"]');
		clickedRadio.checked = true;

		$('#gitCardType').val(gitCardType);
		getGiftCard();
		return false;
	}


	function addGiftCardCost(el, value) {
	
		// Remove 'active' class from all labels
		document.querySelectorAll('.card-values .add-active .con_framebox').forEach(box => {
			box.classList.remove('active');
		});

		// For custom input (text field), check if the radio was selected indirectly
		if (el.id === "usCustomValue") {
			document.getElementById("usCustom").checked = true;
			el.closest('.con_framebox').classList.add('active');
		} else {
			// Add active to selected radio's label's .con_framebox
			const label = document.querySelector(`label[for="${el.id}"] .con_framebox`);
			if (label) {
				label.classList.add('active');
			}
		}
	
		$("#gitCardCostSpan").text('');
		var cardCost = $('#gitCardCostSpan').text(value)

		getGiftCard();
		return true;
	}


	function getGiftCard() {
		var gitCardType = $('#gitCardType').val() != '' ? $('#gitCardType').val() : 'yellow';
		var gitCardCost = $('#gitCardCostSpan').text() != '' ? $('#gitCardCostSpan').text() : 50;

		$.ajax({

			url: "{{ route('front.getGiftCard') }}",

			type: 'POST',

			data: {
				gitCardType: gitCardType,
				gitCardCost: gitCardCost,
				token: "{{ csrf_token() }}",
			},

			dataType: 'json',
			success: function(res) {
				if (res.status == 1) {
					$("#gitCardDiv").html('');
					$("#gitCardDiv").html(res.data);
				} else {
					console.log(res.message);
				}

			},
			error: function(error) {
				console.log(error);
			}

		});

		return false;

	}

	function purchaseGiftCard() {
		var gitCardType = $('#gitCardType').val() != '' ? $('#gitCardType').val() : 'yellow';
		var gitCardCost = $('#gitCardCostSpan').text() != '' ? $('#gitCardCostSpan').text() : 50;
		var recipientName = $('#recipientName').val();
		var giftCardMessage = $('#gift_card_message').val();
		var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

		if (recipientName == '') {
			$("#recipientNameError").html('Please enter recipient email');
			return false;
		} else if (!emailPattern.test(recipientName)) {
			$("#recipientNameError").html('Please enter a valid email address');
			return false;
		} else {
			$("#recipientNameError").html('');
		}

		$.ajax({

			url: "{{ route('front.purchaseGiftCard') }}",

			type: 'POST',

			data: {
				gitCardType: gitCardType,
				gitCardCost: gitCardCost,
				recipientName: recipientName,
				giftCardMessage: giftCardMessage,
				token: "{{ csrf_token() }}",
			},

			dataType: 'json',
			beforeSend: function() {
				$('#purchase_button').prop('disabled', true);
				$('#purchase_button').text('Processing..');
			},
			success: function(res) {
				$('#purchase_button').prop('disabled', false);
				$('#purchase_button').text('Purchase');

				if (res.status == 1) {

					toastr.success(res.message, '', {

						closeButton: true,

						timeOut: 5000,

						extendedTimeOut: 0

					});

					window.location.href = "{{ route('checkout') }}";

				} else {

					toastr.error(res.message, '', {
						closeButton: true,
						timeOut: 5000,
						extendedTimeOut: 0
					});

				}
			},
			error: function(error) {
				console.log(error);
			}

		});

		return false;

	}
</script>
@endpush