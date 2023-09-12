<?php

namespace App\Http\Controllers;

use App\Models\HealthPlace;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HealthPlacesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
     return view ('healthyPlaces');   
     }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {    $health = HealthPlace::all();
        return view ('readHealthyPlaces',[
            'health'=> $health,
        ]
    );   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {         $validator = $request->validate(  [
      
        'name'        => 'required',
        'address'     => 'required',
        'image'       => 'required|image|mimes:png,jpg|max:2048',
        'longitude'   => 'required',
        'latitude'    => 'required',

       
    ]);
    

        $data = $request->all();
        $model = new HealthPlace();
        $model->fill($data);
        $model->image= $request->post('image');
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $model->image = $file->store('/healthy', 'public');
        }
        $model->save();
        return redirect('#')->with('success','Healty place has been successfully added !');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $health = HealthPlace::destroy($id);
       
        return redirect('/readHealthyPlaces' );
    }
    public function getAll(Request $request)
    {
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        
       // $health_places = HealthPlace::all();
        
      //  $nearest_places = [];
        $showResult = DB::table("health_places")
        ->select("health_places.*"
            ,DB::raw("55555 * acos(cos(radians(" . $lat . ")) 
            * cos(radians(health_places.latitude)) 
            * cos(radians(health_places.longitude) - radians(" . $lon . ")) 
            + sin(radians(" .$lat . ")) 
            * sin(radians(health_places.latitude))) AS distance"))
           // ->groupBy("health_places.id", "health_places.latitude", "health_places.longitude")
            ->orderBy("distance", "asc")
           ->paginate(5)
           ;
            $showResult->appends([]); // Remove query parameters for pagination

            // Convert the result to an array
            $showResultArray = $showResult->toArray();
            
            // Get only the data without pagination information
            $dataWithoutPagination = $showResultArray['data'];

        // foreach ($health_places as $health_place) {
        //   $distance = DB::raw('( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians(' . $health_place->latitude . ' ) ) * cos( radians('. $health_place->longitude . ' ) - radians( $lon ) ) + sin( radians(' . $lat . ') ) * sin( radians(' . $health_place->latitude . ' ) ) ) )');
        //   $nearest_places[] = [
        //     'id'       => $health_place->id,
        //     'name'     => $health_place->name,
        //     'address'  => $health_place->address,
        //     'lat'      => $health_place->latitude,
        //     'lon'      => $health_place->longitude,
        //     'distance' => $distance,
        //   ];
        // }
        
        // usort($showResult, function ($a, $b) {
        //   return $a['distance'] <=> $b['distance'];
        // });
        
      //  return $nearest_places;
        return response()->json([
            'message'=>'All Healthy Places Here',
            'data'   =>  $dataWithoutPagination,
            'status' =>Response::HTTP_OK,
        ]);
    }
}
