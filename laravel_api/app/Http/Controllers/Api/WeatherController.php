<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Get current weather.
     */
    public function index()
    {
        try {
            // In a real scenario, this calls OpenWeatherMap.
            // For parity with current PHP version:
            return response()->json([
                'status' => 'success',
                'data' => [
                    'temp' => 24,
                    'condition' => 'Güneşli',
                    'humidity' => 45,
                    'wind' => 12,
                    'city' => 'Diyarbakir'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
