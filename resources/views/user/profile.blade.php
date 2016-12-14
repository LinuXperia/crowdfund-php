@extends('layouts.default')
@section('header')
	@parent
@endsection

@section('content')
	@include('errors.errors')
	@if(isset($user))
		<div class="row">
			<div class="col-md-4">
				<img class="thumbnail" src="{{{asset('images/avatar/large/'.$user->avatar)}}}" alt="{{{$user->fullname}}}">
			</div>
			<div class="col-md-4">
				<h3> About Me </h3>
				{{{$user->bio}}}
			</div>
			<div class="col-md-4">
				<h3> Projects </h3>
				<ul>
					@if($user->projects)
					<li>
						Projects List:
							<ul>
								@foreach($user->projects as $p)
									<li>
										<a title="{{{$p->title}}}" href="{{{$p->url}}}">{{{$p->title}}}</a>
									</li>
								@endforeach
							</ul>
					</li>
					@endif
					@if($user->totalpayments)
					<li>
						Payments List:
							<ul>
								@foreach($user->totalpayments as $p)
									<li>
										<a title="{{{$p['title']}}}" href="{{{$p['url']}}}">{{{$p['title']}}}</a> - {{{number_format($p['value'])}}} ₮
									</li>
								@endforeach
							</ul>
					</li>
					@endif
				</ul>
			</div>
			
		</div>
	@endif
@endsection