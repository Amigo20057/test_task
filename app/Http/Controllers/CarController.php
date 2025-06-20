<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', 1);
        $year = $request->input('year', 2023);

        $from = date("Y-m-01", strtotime("$year-$month-01"));
        $to = date("Y-m-t", strtotime($from));
        $totalDays = date("t", strtotime($from));

        $results = DB::select("
            SELECT 
                c.car_id AS id,
                c.registration_number AS name,
                c.attribute_year AS year,
                m.attribute_interior_color AS color,
                cb.slug AS brand,
                c.created_at AS create_date,
                c.price_type,

                COUNT(DISTINCT CASE WHEN b.service = 1 THEN b.day END) AS service,
                COUNT(DISTINCT CASE WHEN b.busy = 1 THEN b.day END) AS busy,
                ? 
                  - COUNT(DISTINCT CASE WHEN b.service = 1 THEN b.day END)
                  - COUNT(DISTINCT CASE WHEN b.busy = 1 THEN b.day END) AS free,
                ? AS all_days

            FROM rc_cars c

            LEFT JOIN rc_cars_models m ON m.car_model_id = c.car_model_id
            LEFT JOIN rc_cars_brands cb ON cb.car_brand_id = m.car_brand_id

            LEFT JOIN (
                SELECT 
                    car_id,
                    DATE(start_date) AS day,
                    MAX(CASE 
                        WHEN LOWER(other) LIKE '%service%' THEN 1
                        ELSE 0
                    END) AS service,
                    MAX(CASE 
                        WHEN TIMESTAMPDIFF(MINUTE, 
                            GREATEST(start_date, DATE_FORMAT(start_date, '%Y-%m-%d 09:00:00')),
                            LEAST(end_date, DATE_FORMAT(end_date, '%Y-%m-%d 21:00:00'))
                        ) >= 540 THEN 1
                        ELSE 0
                    END) AS busy
                FROM rc_bookings
                WHERE status = 1
                  AND (
                    (start_date BETWEEN ? AND ?) OR
                    (end_date BETWEEN ? AND ?) OR
                    (start_date < ? AND end_date > ?)
                  )
                GROUP BY car_id, DATE(start_date)
            ) b ON b.car_id = c.car_id

            WHERE 
                c.company_id = 1 AND 
                c.status = 1 AND 
                c.is_deleted != 1

            GROUP BY 
                c.car_id, c.registration_number, c.attribute_year, 
                m.attribute_interior_color, cb.slug, 
                c.created_at, c.price_type

            ORDER BY c.car_id
        ", [
            $totalDays,
            $totalDays,
            $from,
            $to,
            $from,
            $to,
            $from,
            $to
        ]);

        return view('index', [
            'results' => $results,
            'year' => $year,
            'month' => $month,
            'carCount' => count($results)
        ]);
    }

    public function calendar(Request $request)
    {
        $month = (int) $request->input('month', 1);
        $year = (int) $request->input('year', 2023);

        $from = date("Y-m-01", strtotime("$year-$month-01"));
        $to = date("Y-m-t", strtotime($from));
        $totalDays = date("t", strtotime($from));

        $cars = DB::table('rc_cars AS c')
            ->leftJoin('rc_cars_models AS m', 'm.car_model_id', '=', 'c.car_model_id')
            ->leftJoin('rc_cars_brands AS b', 'b.car_brand_id', '=', 'm.car_brand_id')
            ->where('c.company_id', 1)
            ->where('c.status', 1)
            ->where('c.is_deleted', '!=', 1)
            ->select('c.car_id', 'c.registration_number', 'm.attribute_interior_color', 'b.slug', 'c.attribute_year')
            ->orderBy('c.car_id')
            ->get();

        $bookings = DB::table('rc_bookings')
            ->where('status', 1)
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])
                    ->orWhereBetween('end_date', [$from, $to])
                    ->orWhere(function ($q) use ($from, $to) {
                        $q->where('start_date', '<', $from)
                            ->where('end_date', '>', $to);
                    });
            })
            ->select('car_id', 'start_date', 'end_date', 'other')
            ->get();

        $groupedBookings = [];
        foreach ($bookings as $booking) {
            $groupedBookings[$booking->car_id][] = [
                'start' => $booking->start_date,
                'end' => $booking->end_date,
                'type' => str_contains(strtolower($booking->other), 'service') ? 'service' : 'rent',
            ];
        }

        return view('calendar', compact('cars', 'groupedBookings', 'year', 'month', 'totalDays'));
    }

}

