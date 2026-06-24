<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get dashboard stats for mobile admin panel.
     */
    public function dashboard(Request $request)
    {
        try {
            $role = $request->header('X-User-Role');
            $districtId = $request->header('X-District-Id');
            $isSuper = ($role === 'SUPER_ADMIN');

            $queryFilter = $isSuper ? [] : ['district_id' => $districtId];

            // Stats
            $stats = [
                'cek_gonder' => DB::table('cek_gonder_forms')->where($queryFilter)->count(),
                'places' => DB::table('places')->where($queryFilter)->count(),
                'businesses' => DB::table('businesses')->where($queryFilter)->count(),
                'users' => DB::table('users')->where($isSuper ? [] : ['district_id' => $districtId])->count(),
                'pending_events' => DB::table('events')->where($queryFilter)->where('global_status', 'PENDING')->count(),
            ];

            // Recent Cek Gonder
            $recentCekGonder = DB::table('cek_gonder_forms')
                ->where($queryFilter)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'recent_cek_gonder' => $recentCekGonder
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
