<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Car Availability</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			margin: 30px;
			background-color: #f9f9f9;
			color: #333;
		}

		h2 {
			margin-top: 40px;
		}

		form {
			margin-bottom: 20px;
		}

		label {
			margin-right: 15px;
			font-weight: bold;
		}

		select {
			padding: 5px 10px;
			font-size: 14px;
		}

		button {
			padding: 6px 15px;
			font-size: 14px;
			background-color: #007bff;
			color: white;
			border: none;
			cursor: pointer;
			border-radius: 4px;
		}

		button:hover {
			background-color: #0056b3;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			background-color: white;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		}

		th,
		td {
			border: 1px solid #ddd;
			padding: 10px 8px;
			text-align: center;
		}

		th {
			background-color: #f1f1f1;
			font-weight: bold;
		}

		tr:nth-child(even) {
			background-color: #f9f9f9;
		}

		#spinner {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(255, 255, 255, 0.7);
			z-index: 9999;
			justify-content: center;
			align-items: center;
		}

		.spinner {
			border: 6px solid #f3f3f3;
			border-top: 6px solid #007bff;
			border-radius: 50%;
			width: 50px;
			height: 50px;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}
	</style>
</head>

<body>

	<div id="spinner">
		<div class="spinner"></div>
	</div>

	<form method="get" action="{{ url('/cars') }}" onsubmit="showSpinner()">
		<label>Year:
			<select name="year">
				@foreach(range(2021, 2025) as $y)
					<option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
				@endforeach
			</select>
		</label>
		<label>Month:
			<select name="month">
				@foreach(range(1, 12) as $m)
					<option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $m }}</option>
				@endforeach
			</select>
		</label>
		<button type="submit">Show data</button>
	</form>

	<a href="/calendar">Calendar</a>

	<h2>Results</h2>
	<p><strong>All cars:</strong> {{ $carCount }}</p>

	@if(count($results))
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Year</th>
					<th>Color</th>
					<th>Brand</th>
					<th>Create Date</th>
					<th>Price Type</th>
					<th>Service</th>
					<th>Busy</th>
					<th>Free</th>
					<th>All Days</th>
				</tr>
			</thead>
			<tbody>
				@foreach($results as $car)
					<tr>
						<td>{{ $car->id }}</td>
						<td>{{ $car->name }}</td>
						<td>{{ $car->year }}</td>
						<td>{{ $car->color }}</td>
						<td>{{ $car->brand }}</td>
						<td>{{ $car->create_date }}</td>
						<td>{{ $car->price_type }}</td>
						<td>{{ $car->service }}</td>
						<td>{{ $car->busy }}</td>
						<td>{{ $car->free }}</td>
						<td>{{ $car->all_days }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@else
		<p>Немає результатів для вибраного місяця.</p>
	@endif

	<script>
		function showSpinner() {
			document.getElementById('spinner').style.display = 'flex';		}
	</script>

</body>

</html>