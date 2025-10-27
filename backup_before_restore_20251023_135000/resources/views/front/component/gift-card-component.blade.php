<div class="sticky-top">

	<div class="gift-card row giftCartClass_{{$giftCardType }}" style="background: {{$giftCardType }}" ;">
		<!-- <img src="{{asset('assets/images/card1.png')}}"> -->
		<div class="col d-flex flex-column justify-content-between">
			<div class="text-content">
				<h1>Gift</h1>
				<p>Card</p>
			</div>

			<div class="amount">
				US$ <span class="gift-card-value" id="gitCardCostSpan">{{$giftCardValue}}</span>
			</div>
		</div>

		<div class="col d-flex align-items-end">
			<div class="gift-icon">
				🎁
			</div>
		</div>


	</div>

</div>