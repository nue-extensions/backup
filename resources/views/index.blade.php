@extends('layouts.app')
@section('title', $title)

@section('css')
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
	<style>
		.output-body {
			white-space: pre-wrap;
			background: #000000;
			color: #00fa4a;
			padding: 10px;
			border-radius: 0;
		}
	</style>
@endsection

@section('js')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
	<script>
		$(function () {
			$(".backup-run").click(function() {
				var $btn = $(this);
				$btn.text('Loading ...');
				NProgress.start();
				$.ajax({
					url: '{{ route('backup-run') }}',
					data : {
						_token: $('meta[name="csrf-token"]').attr('content'),
					},
					method: 'POST',
					success: function (data){
						$('.output-box').removeClass('collapse');
						$('.output-box .output-body').html(data.message)

						$btn.text('+ Backup');
						$btn.button('reset');
						NProgress.done();
					}
				});
				return false;
			});
			$(".backup-delete").click(function() {
				var url = $(this).data('href');
				swal({
					title: "Are you sure ?",
					type: "warning",
					text: "We will delete this menu permanently.",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "Confirm",
					cancelButtonText: "Cancel",
					allowOutsideClick: false
				}).then(function(data) {
					$.ajax({
						type: 'DELETE',
						url: url,
						data: {
							"_token": $('meta[name="csrf-token"]').attr('content'),
						},
						success: function(data) {
							history.go(0);
						}
					});
				});
				return false;
			});
		});
	</script>
@endsection

@section('content')
	
	@include('nue::partials.breadcrumb', ['lists' => [
		'Extensions' => 'javascript:;', 
		$title => 'active'
	]])

	<div class="d-flex bg-white align-items-center p-2">
		<div class="col-sm">
			<h2 class="page-header-title mb-0">{!! $title !!}</h2>
			<p class="mb-0">Here are our existing backups.</p>
		</div>
		<div class="col-sm-auto d-sm-flex d-none">
			<a class="btn btn-white btn-sm ms-1 backup-run" href="javascript:;">
				<i class="bi-plus"></i> Backup
			</a>
		</div>
	</div>

	<div class="card rounded-0">
		<div class="card-body table-responsive p-0">
			<table class="table table-striped">
				<tbody>
					<tr class="thead-light">
						<th>#</th>
						<th>Name</th>
						<th>Disk</th>
						<th>Reachable</th>
						<th>Healthy</th>
						<th># of backups</th>
						<th>Newest backup</th>
						<th class="text-end">Used storage</th>
					</tr>
					@foreach($backups as $index => $backup)
						<tr>
							<td width="1">{{ $index+1 }}.</td>
							<td>{{ @$backup[0] }}</td>
							<td>{{ @$backup['disk'] }}</td>
							<td>{{ @$backup[1] }}</td>
							<td>{{ @$backup[2] }}</td>
							<td>{{ @$backup['amount'] }}</td>
							<td>{{ @$backup['newest'] }}</td>
							<td align="right">{{ @$backup['usedStorage'] }}</td>
						</tr>
						@if($backup['files'])
							<tr>
								<td colspan="8">
									@foreach($backup['files'] as $i => $file)
										<div class="alert alert-soft-secondary d-flex mb-1 rounded-0" role="alert">
											<div class="ms-2 me-auto">
												<div class="lead fw-semi-bold">
													{{ ++$i }}. {{ $file }}
												</div>
											</div>
											<div class="btn-group">
												<a href="{{ route('backup-download', ['disk' => $backup['disk'], 'file' => $backup[0].'/'.$file]) }}" class="btn btn-xs btn-white">
													<span class="iconify" data-icon="heroicons-solid:download"></span> Download
												</a>
												<a href="javascript:;" data-href="{{ route('backup-delete', ['disk' => $backup['disk'], 'file' => $backup[0].'/'.$file]) }}" class="btn btn-xs btn-danger backup-delete">
													<span class="iconify" data-icon="heroicons-solid:trash"></span> Delete
												</a>
											</div>
										</div>
									@endforeach
								</td>
							</tr>
						@endif
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	<div class="content container-fluid">
		<div class="card shadow-none rounded-1 output-box collapse">
			<div class="card-header">
				<h3 class="mb-0">
					<span class="iconify" data-icon="heroicons-solid:terminal"></span>
					Output
				</h3>
			</div>
			<pre class="output-body mb-0"></pre>
		</div>
	</div>
@endsection