<div class="white-box">
	<img class="project-homepage-image" src="{{{asset('images/project/medium/'.$p->image)}}}">
	<div class="project-homepage-description">
		<h3><a href="{{{$p->url}}}" title="{{{$p->title}}}">{{{$p->title}}}</a></h3>
		<p>
		{{{$p->intro}}}
		</p>
		
		# @include('modules.categories.list',['categories'=>$p->categories])
		
		<div class="progress">
		  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{{$p->percentage}}}"
		  aria-valuemin="0" aria-valuemax="100" style="width:{{{$p->percentage}}}%">
		  </div>
		</div>
		<div class="row">
			<div class="col-md-3">
				<b>{{{$p->percentage}}}%</b>
				<br>
				Funded
			</div>
			<div class="col-md-3">
				<b>{{{number_format($p->totalgoal)}}} $</b>
				<br>
				Goal
			</div>
			<div class="col-md-3">
				<b>{{{number_format($p->totalpayment)}}} $</b>
				<br>
                Collected
			</div>
			<div class="col-md-3">
				<b>{{{$p->daysleft}}}</b>
				<br>
				Days to go
			</div>
		</div>
	</div>
</div>