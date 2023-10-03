<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // app/Http/Controllers/CityController.php

public function index()
{
    $cities = City::all();
        return view('cities_index',
         ['cities' => $cities]);
}

public function create()
{
    return view('cities');
}

public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|unique:cities|max:255',
    ]);

    $city = new City();
    $city->name = $validatedData['name'];
    $city->save();

    return redirect()->route('cities')->with('success', 'City created successfully');

}

public function show($id)
{
    /*$city = City::findOrFail($id);
    return view('cities_index', ['city' => $city]);*/

}

public function edit($id)
{
    $city = City::findOrFail($id);
        return view('cities.edit', ['city' => $city]);
}

public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'name' => 'required|unique:cities,name,' . $id . '|max:255',
    ]);

    $city = City::findOrFail($id);
    $city->name = $validatedData['name'];
    $city->save();

    return redirect()->route('cities')->with('success', 'City updated successfully');
;
}

public function destroy($id)
{
    $city = City::destroy($id);
   
        return redirect()->route('cities')->with('success', 'City deleted successfully');
   
}

}
