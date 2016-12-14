<!DOCTYPE html>
<html>
	@include('layouts.parts.head')
    <body>
		@include('layouts.parts.beginbody')
		@if (isset($navigations) && isset($navigations['super']))
			@include('modules.navigations.supernav')
		@endif
		@if(isset($slideshow))
			@include('modules.slideshow.slideshow',['slideshow'=>$slideshow,'id'=>'homepage'])
		@else
			<div class="navpadding">
			</div>
		@endif
		<div class="container">
			@section('header')
				@include('layouts.parts.header')
			@show
		</div>
		<div class="container">
			<div class="row">
			<div class="col-md-8">
				@yield('content')
			</div>
			<aside class="col-md-4">
				@yield('aside')
			</aside>
			</div>
		</div>
		@include('layouts.parts.footer')
		@include('layouts.parts.endbody')
    </body>
</html>