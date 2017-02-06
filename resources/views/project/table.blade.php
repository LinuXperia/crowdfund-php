@extends('layouts.default')
@section('header')
	@parent
    @include('project.actions')
@endsection
@section('content')
	@include('errors.errors')
	<div class="container">
		<table class="table table-bordered table-striped datatable">
			<thead>
				<tr>
					<th>Project Number</th>
					<th>{{{trans('project.name')}}}</th>
					<th>{{{trans('messages.status')}}}</th>
					<th>{{{trans('messages.image')}}}</th>
					<th>{{{trans('messages.video')}}}</th>
					<th>{{{trans('messages.intro')}}}</th>
					<th>{{{trans('messages.detail')}}}</th>
					<th>{{{trans('project.goals')}}}</th>
					<th>{{{trans('messages.actions')}}}</th>
				</tr>
			</thead>
			<tbody>
				@foreach($projects as $p)
					@include('modules.project.table.item')
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="9">
						{!! $projects->links() !!}
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
@endsection