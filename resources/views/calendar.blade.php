<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Календар бронювань на {{ $month }}/{{ $year }}</title>

    <script src="/js/daypilot-all.min.js"></script>

    <style>
	html, body {
		height: 100%;
		margin: 0;
	}

	#dp {
		width: 100%;
		height: calc(100vh - 60px);
	}
	</style>
	</head>
<body>

	<a href="/cars">Головна</a>

    <h2>Календар бронювань на {{ $month }}/{{ $year }}</h2>

    <form method="GET" action="{{ route('calendar') }}" style="margin-bottom: 20px;">
    <label for="year">Рік:</label>
    <select name="year" id="year">
        @for ($y = 2022; $y <= now()->year + 1; $y++)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>

    <label for="month">Місяць:</label>
    <select name="month" id="month">
        @for ($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
            </option>
        @endfor
    </select>

    <button type="submit">Показати</button>
</form>

    <div id="dp"></div>

   <script>
    document.addEventListener("DOMContentLoaded", function () {
        const dp = new DayPilot.Scheduler("dp");

        dp.startDate = "{{ $year }}-{{ sprintf('%02d', $month) }}-01";
        dp.days = {{ $totalDays }};
        dp.scale = "Day";
		dp.cellWidthSpec = "Auto";            
		dp.heightSpec = "Full";               
		dp.autoScroll = "Never";              
		dp.dynamicLoading = false;           

        dp.timeHeaders = [
            { groupBy: "Month", format: "MMMM yyyy" },
            { groupBy: "Day", format: "d" }
        ];

        dp.allowEventMove = false;
        dp.allowEventResize = false;
        dp.allowEventOverlap = false;
        dp.eventMoveHandling = "Disabled";
        dp.eventResizeHandling = "Disabled";
        dp.eventClickHandling = "Disabled";

        dp.resources = [
            @foreach ($cars as $car)
                {
                    id: "{{ $car->car_id }}",
                    name: "{{ $car->slug }} {{ $car->registration_number }} ({{ $car->attribute_year }})"
                },
            @endforeach
        ];

        dp.events.list = [
            @foreach ($groupedBookings as $carId => $bookings)
                @foreach ($bookings as $booking)
                    {
                        id: "e{{ $loop->parent->index }}_{{ $loop->index }}",
                        resource: "{{ $carId }}",
                        start: "{{ \Carbon\Carbon::parse($booking['start'])->format('Y-m-d') }}",
                        end: "{{ \Carbon\Carbon::parse($booking['end'])->addDay()->format('Y-m-d') }}",
                        text: "{{ ucfirst($booking['type']) }} {{ \Carbon\Carbon::parse($booking['start'])->format('d.m') }}–{{ \Carbon\Carbon::parse($booking['end'])->format('d.m') }}",
                        barColor: "{{ $booking['type'] === 'service' ? '#dc3545' : '#2196F3' }}"
                    },
                @endforeach
            @endforeach
        ];

        dp.init();
    });
  </script>

</body>
</html>
