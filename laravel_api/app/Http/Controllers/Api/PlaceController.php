<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    /**
     * Get places.
     */
    public function index(Request $request)
    {
        try {
            $query = Place::where('is_approved', 1)->where('is_active', 1);

            if ($request->has('district_id')) {
                $dId = $request->district_id;
                if (!is_numeric($dId)) {
                    $dist = \DB::table('districts')->where('slug', $dId)->first();
                    if ($dist) $dId = $dist->id;
                }
                $query->where('district_id', $dId);
            }

            if ($request->has('category')) {
                $cat = strtolower($request->category);
                $query->where(function($q) use ($cat) {
                    $q->where('category', $cat)
                      ->orWhere('category', 'like', '%' . str_replace('-places', '', $cat) . '%');
                    
                    // Slug variations
                    $cleanCat = strtolower(str_replace('-places', '', $cat));
                    if ($cleanCat == 'historical' || $cleanCat == 'tarihi') $q->orWhere('category', 'Historical');
                    if ($cleanCat == 'nature' || $cleanCat == 'doga') $q->orWhere('category', 'Nature');
                    if ($cleanCat == 'thermal' || $cleanCat == 'hotspring' || $cleanCat == 'kaplica') $q->orWhere('category', 'HotSpring');
                    if ($cleanCat == 'park' || $cleanCat == 'parks-gardens') $q->orWhere('category', 'ParkAndGarden');
                });
            }

            // Bozuk/Test verilerini filtrele
            $query->where('name', 'NOT LIKE', '%DENEME%')
                  ->where('name', 'NOT LIKE', '%ASDASD%')
                  ->whereRaw('LENGTH(name) > 3');

            $places = $query->orderBy('popular_score', 'desc')
                           ->orderBy('name', 'asc')
                           ->get();

            return response()->json([
                'status' => 'success',
                'data' => $places
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single place.
     */
    public function show($id)
    {
        try {
            $place = Place::find($id);

            if (!$place) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mekan bulunamadı.'
                ], 404);
            }

            // Calculate check-in stats
            $place->checkin_day = \DB::table('check_ins')
                ->where('target_id', $place->id)
                ->where('target_type', 'place')
                ->where('status', 'APPROVED')
                ->whereDate('created_at', \Carbon\Carbon::today())
                ->count();
                
            $place->checkin_month = \DB::table('check_ins')
                ->where('target_id', $place->id)
                ->where('target_type', 'place')
                ->where('status', 'APPROVED')
                ->whereMonth('created_at', \Carbon\Carbon::now()->month)
                ->whereYear('created_at', \Carbon\Carbon::now()->year)
                ->count();
                
            $place->checkin_year = \DB::table('check_ins')
                ->where('target_id', $place->id)
                ->where('target_type', 'place')
                ->where('status', 'APPROVED')
                ->whereYear('created_at', \Carbon\Carbon::now()->year)
                ->count();

            return response()->json([
                'status' => 'success',
                'data' => $place
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
