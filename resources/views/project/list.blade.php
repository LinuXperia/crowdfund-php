@extends('layouts.default')
@section('header')
	@if(isset($category) and $category == false)
		@parent
	@else
	</div>
		<div class="gray-box padding-lg">
			<div class="container text-center">
				<h1>Project Categories.</h1>
				@if(isset($navigations['categories']))
					@foreach($navigations['categories'] as $n)
						<a class="btn btn-default btn-lg" href="{{{$n['url']}}}" title="{{{$n['title']}}}">{{{$n['title']}}}</a>
					@endforeach
				@endif
			</div>
		</div>
	<div class="container">
	@overwrite
	@endif

@section('content')
	@include('errors.errors')
	@if(isset($featured) && $featured)
		<section>
			<div class="padding">
			</div>
				<h3 class="text-center">Featured Projects</h3>
				@include('modules.slideshow.slideshow',['slideshow'=>$featured,'id'=>'project'])
			<div class="padding">
			</div>
		<section>
	@endif
	@if($projects)
		</div>
			<section>
				<div class="gray-box padding">
					<div class="container">
						<div class="row">
							@foreach($projects as $p)
								@include('modules.project.card.item',['p'=>$p])
							@endforeach
						</div>
						<div class="text-center">
							{!! $projects->links() !!}
						</div>
					</div>
				</div>
			<section>
		<div class="container">
	@endif
@endsection