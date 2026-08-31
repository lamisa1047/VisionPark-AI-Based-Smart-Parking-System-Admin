<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Throwable;

class SlotController extends Controller
{
    public function index()
    {
        try {
            $slots = Slot::all();

            return response()->json([
                'slots' => $slots,
            ], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}