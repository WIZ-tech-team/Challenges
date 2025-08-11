<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LocationsController extends Controller
{
    public function index()
    {
        $locations = Location::with('location_image')->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'title' => 'required|string|max:255',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|file'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        if ($validator->passes()) {
            $location = Location::create($request->only([
                'latitude',
                'longitude',
                'title',
                'address',
                'description'
            ]));

            if($request->hasFile('image')) {
                $location->addMedia($request->file('image'))->toMediaCollection('location_image');
            }

            return response()->json([
                'status' => 'success',
                'data' => $location
            ], Response::HTTP_OK);
        }
    }
}
