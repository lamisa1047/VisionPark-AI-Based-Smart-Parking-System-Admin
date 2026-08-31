<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Slot;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BookingController extends Controller
{
    public function store(Request $request, $slot_id)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'entry_time' => 'required|date',
            'exit_time' => 'nullable|date|after:entry_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slot = Slot::find($slot_id);

        if (! $slot) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $vehicle = Vehicle::where('id', $request->vehicle_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 400);
        }

        if ($slot->status === 'occupied') {
            return response()->json(['message' => 'Slot is already occupied'], 400);
        }

        try {
            $booking = Booking::create([
                'vehicle_id' => $vehicle->id,
                'slot_id' => $slot->id,
                'entry_time' => $request->entry_time,
                'exit_time' => $request->exit_time,
            ]);

            $slot->update(['status' => 'occupied']);

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking,
            ], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $bookings = Booking::whereHas('vehicle', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })->with(['vehicle', 'slot'])->get();

            return response()->json([
                'bookings' => $bookings,
            ], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}