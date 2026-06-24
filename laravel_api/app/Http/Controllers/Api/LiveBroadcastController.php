<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveBroadcastController extends Controller
{
    /**
     * Get live broadcasts for a district.
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('live_broadcasts')->where('is_active', 1);

            if ($request->has('district_id')) {
                $query->where('district_id', $request->district_id);
            }

            $broadcasts = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $broadcasts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
