<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
	<!-- Brand and toggle get grouped for better mobile display -->
	<div class="navbar-header">
	  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
		<span class="sr-only">Нээх/Хаах</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	  </button>
	  <a class="navbar-brand" href="{{{url('/')}}}">POLONIAGO</a>
	</div>

	<!-- Collect the nav links, forms, and other content for toggling -->
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	  <ul class="nav navbar-nav">
		@foreach($navigations['super'] as $n)
			@include('modules.navigations.item',['item'=>$n])
		@endforeach
	  </ul>
	  {!! Form::open(array('url'=>'project/search','method'=>'get','class'=>'navbar-form navbar-right')) !!}
		<div class="form-group">
			{!! Form::text('searchtext',old('searchtext'),['class'=>'form-control','placeholder'=>trans('project.name')]) !!}
		</div>
		<button type="submit" class="btn btn-default">{{{trans('messages.search')}}}</button>
	  {!! Form::close() !!}
	  @if(isset($navigations['user']))
	  <ul class="nav navbar-nav navbar-right">
		@foreach($navigations['user'] as $n)
			@include('modules.navigations.item',['item'=>$n])
		@endforeach
	  </ul>
	  @endif
	</div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
@include('modules.modal',['title'=>'Login','id'=>'loginModal','modalbody'=>'modules.user.login'])
@include('modules.modal',['title'=>'Register','id'=>'registerModal','modalbody'=>'modules.user.register'])