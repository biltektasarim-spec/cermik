<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Service;
use App\Models\MunicipalGuide;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    /**
     * Get announcements.
     */
    public function getAnnouncements(Request $request)
    {
        try {
            // Legacy DB tablosunda 'is_active' sütunu bulunmadığı için filtre kaldırıldı.
            $query = Announcement::query();

            if ($request->has('district_id')) {
                $query->where(function($q) use ($request) {
                    $q->where('district_id', $request->district_id)
                      ->orWhereNull('district_id');
                });
            }

            $announcements = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $announcements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get events.
     */
    public function getEvents(Request $request)
    {
        try {
            // 'is_active' yerine admin panelindeki 'status' sütunu kullanılıyor.
            $query = Event::where('status', 'APPROVED');

            if ($request->has('district_id')) {
                $query->where(function($q) use ($request) {
                    $q->where('district_id', $request->district_id)
                      ->orWhereNull('district_id');
                });
            }

            if ($request->has('global_status')) {
                $query->where('global_status', $request->global_status);
            }

            $events = $query->orderBy('event_date', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get services/projects.
     */
    public function getServices(Request $request)
    {
        try {
            // Hizmetler tablosunda is_active sütunu yerine genel listeleme yapılıyor.
            $query = Service::query();

            if ($request->has('district_id')) {
                $query->where('district_id', $request->district_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $services = $query->orderBy('id', 'desc')->get();
            
            $lang = $request->get('lang', 'tr');
            if ($lang === 'en') {
                foreach ($services as $s) {
                    if (!empty($s->title_en)) $s->title = $s->title_en;
                    if (!empty($s->description_en)) $s->description = $s->description_en;
                }
            }
 
            return response()->json([
                'status' => 'success',
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get municipal guide entries.
     */
    public function getGuide(Request $request)
    {
        try {
            $query = MunicipalGuide::query();

            if ($request->has('district_id')) {
                $query->where(function($q) use ($request) {
                    $q->where('district_id', $request->district_id)
                      ->orWhere('district_id', 0)
                      ->orWhereNull('district_id');
                });
            }

            $guide = $query->orderBy('category', 'asc')
                           ->orderBy('name', 'asc')
                           ->get();

            return response()->json([
                'status' => 'success',
                'data' => $guide
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
