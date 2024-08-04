<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SensorDataController extends Controller
{
    public function index()
    {
        // Fetch the latest data
        $data = SensorData::latest()->first();
        
        // Fetch the latest 10 entries for history
        $history = SensorData::latest()->take(10)->get();

        // Convert times to WIB
        $data->created_at = Carbon::parse($data->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        foreach ($history as $entry) {
            $entry->created_at = Carbon::parse($entry->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        }

        // Send data to view
        return view('dashboard', compact('data', 'history'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'gas_level' => 'required|numeric',
            'voltage' => 'required|numeric',
            'current' => 'required|numeric',
            'power' => 'required|numeric',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        SensorData::create($validated);

        return response()->json(['status' => 'success']);
    }

    public function latest()
    {
        // Fetch the latest data
        $data = SensorData::latest()->first();

        return response()->json($data);
    }
}
