<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:50|unique:vehicles,number',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $vehicle = $request->user()->vehicles()->create([
                'name' => $request->name,
                'number' => $request->number,
            ]);

            return response()->json([
                'message' => 'Vehicle has been created successfully',
                'vehicle' => $vehicle,
            ], 201);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $vehicles = $request->user()->vehicles;

            return response()->json([
                'vehicles' => $vehicles,
            ], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 400);
        }

        try {
            $vehicle->delete();

            return response()->json([
                'message' => 'Vehicle has been deleted successfully',
            ], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}