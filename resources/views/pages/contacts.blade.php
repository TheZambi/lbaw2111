@extends('layouts.app')


@section('title')
    <title>Contact Us</title>
@endsection


<!--Google map-->
@section("content")
@include('partials.sidebarItem',["categories" => $categories])


<div class="container col-12 p-0" style="background-color: #D0D8D4">
	<div class="col-12 offset-md-1 col-md-10 h-100 p-0" style="max-width: 100%; background-color: #F8F8F8;">
		<div class="container" id="contact">
			<div class="d-flex row">
				<div class="col-12 col-md-8">
					<iframe id="ContactsMap" style="width: 100%" height="350" src="https://maps.google.co.uk/maps?f=q&source=s_q&hl=en&geocode=&q=15+Springfield+Way,+Hythe,+CT21+5SH&aq=t&sll=52.8382,-2.327815&sspn=8.047465,13.666992&ie=UTF8&hq=&hnear=15+Springfield+Way,+Hythe+CT21+5SH,+United+Kingdom&t=m&z=14&ll=51.077429,1.121722&output=embed">
					</iframe>
				</div>
				<section class="col pt-md-5" id="ContactsCol">
					<div class="container pt-md-5">
						<h2>Fneuc</h2>
						<address>
							<strong>Fneuc</strong><br>
							15 Springfield Way<br>
							Hythe CT21 5SH<br>
							UK<br>
							Phone: +0351 123 456 789
						</address>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
@endsection