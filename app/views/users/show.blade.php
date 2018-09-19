@extends(Config::get('Sentinel::config.layout'))

{{-- Web site Title --}}
@section('title')
@parent
Home
@stop

{{-- Content --}}
@section('content')
	<h4>Account Profile</h4>
	
  	<div class="well clearfix">
	    <div class="col-md-8">
			<p><strong>Username:</strong> {{ $user->username }}</p>
		    @if ($user->full_name)
		    	<p><strong>Name:</strong> {{ $user->full_name }} </p>
			@endif
		    <p><strong>Email:</strong> {{ $user->email }}</p>
		    
		</div>
		<div class="col-md-4">
			<p><em>Account created: {{ $user->created_at }}</em></p>
			<p><em>Last Updated: {{ $user->updated_at }}</em></p>
		</div>
	</div>

	<h4>Group Memberships:</h4>
	<?php $userGroups = $user->getGroups(); ?>
	<div class="well">
	    <ul>
	    	@if (count($userGroups) >= 1)
		    	@foreach ($userGroups as $group)
					<li>{{ $group['name'] }}</li>
				@endforeach
			@else 
				<li>No Group Memberships.</li>
			@endif
	    </ul>
	</div>

@stop
