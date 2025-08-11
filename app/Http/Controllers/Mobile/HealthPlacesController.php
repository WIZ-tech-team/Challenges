<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\HealthPlace;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthPlacesController extends Controller
{
    public function index()
    {
        $places = HealthPlace::all();

        return response()->json([
            'status' => 'success',
            'data' => $places
        ], Response::HTTP_OK);
    }
}
