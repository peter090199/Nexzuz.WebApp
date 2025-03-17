<?php

namespace App\Http\Controllers\System\Menus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Menu;
use App\Models\Submenu;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Facades\Auth; 

class MenuController extends Controller
{
   /**
     * Display a listing of the resource.
     */

    private $description = "Menus";

    public function index(Request $request)
    {
        $request->merge(['description' => $this->description]);
        $accessResponse = $this->accessmenu($request);

        if ($accessResponse !== 1) {
            return response()->json(['success' => false,'message' => 'Unauthorized']);
        }

        $menu = Menu::orderBy('sort', 'asc')->get();
        $result = [];

            for($m = 0; $m<count($menu); $m++){


                $submenu = Submenu::where('transNo', $menu[$m]->transNo)->orderBy('sort', 'asc') ->get();   
                $sub = [];
                for($su = 0; $su<count($submenu); $su++){

                    $sub[$su] = [
                        "id" => $submenu[$su]->id,
                        "transNo" => $submenu[$su]->transNo,
                        "desccode" => $submenu[$su]->desc_code,
                        "description" => $submenu[$su]->description,
                        "icon" => $submenu[$su]->icon,
                        "route" => $submenu[$su]->routes,
                        "sort" => $submenu[$su]->sort
                    ];
                }

                $result[$m] = [
                    "id" => $menu[$m]->id,
                    "transNo" => $menu[$m]->transNo,
                    "desccode" => $menu[$m]->desc_code,
                    "description" => $menu[$m]->description,
                    "icon" => $menu[$m]->icon,
                    "route" => $menu[$m]->routes,
                    "sort" => $menu[$m]->sort,
                    "submenu" => $sub
                ];
            }
            return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //




    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 
        $request->merge(['description' => $this->description]);
        $accessResponse = $this->accessmenu($request);

        if ($accessResponse !== 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            DB::beginTransaction();
            $data = $request->all();
            $header = Validator::make($data, [
                'desc_code' => 'required|string',
                'description' => 'required|string',
                'icon' => 'required|string',
                'class' => 'required|string',
                'routes' => 'required|string',
                'sort' => 'required|integer',
                'status' => 'nullable|string'
            ]);

            if ($header->fails()) {
                return response()->json([
                    'success' => false,  // Indicate failure
                    'message' => $header->errors()  // Return validation errors
                ]); 
            }

            // Check if the menu description already exists
            $menuexists = Menu::where('description', $data['description'])->exists();

            if ($menuexists) {
                return response()->json(['success' => false, 'message' => 'Menu description already exists. Please avoid duplicates.']);
            }

            $trans = Menu::max('transNo');
            $transNo = empty($trans) ? 1 : $trans + 1;

            Menu::insert([
                "transNo" => $transNo,
                'desc_code' => $data['desc_code'],
                "description" => $data['description'],
                'icon' =>$data['icon'],
                'class'=>$data['class'],
                'routes' =>$data['routes'],
                'sort' =>$data['sort'],
                'status' => $data['status'] ? $data['status'] : 'I',
                'created_by' => Auth::user()->fullname,
                'updated_by' => Auth::user()->fullname
            ]);

            foreach($data['lines'] as $line){
                $lineValidator = Validator::make($line, [
                    
                    'description' => 'required|string',
                    'icon' => 'required|string',
                    'class' => 'required|string',
                    'routes' => 'required|string',
                    'sort' => 'required|integer',
                    'status' => 'nullable|string'
                ]);
                
            
                if ($lineValidator->fails()) {
                    $lineErrors[$index] = $lineValidator->errors();
                }

                // Check if the submenu description already exists
                $subexists = Submenu::where('description', $line['description'])->exists();

                if ($subexists) {
                    return response()->json(['success' => false, 'message' => 'Submenu description already exists. Please avoid duplicates.']);
                }

                Submenu::insert([
                    "transNo" => $transNo,
                    "desc_code" => $data['desc_code'],
                    "description" => $line['description'],
                    'icon' =>$line['icon'],
                    'class'=>$line['class'],
                    'routes' =>$line['routes'],
                    'sort' =>$line['sort'],
                    'status' => $line['status'] ? $line['status'] : 'I',
                    'created_by' => Auth::user()->fullname,
                    'updated_by'=> Auth::user()->fullname,
                ]);
            }
                    // Commit the transaction
                    DB::commit();

                    // Return response
                    return response()->json([
                        'success' => true,
                        'message' => 'Menu and submenus created successfully',
                    ]);
    
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success'=>false,'message' => $th->getMessage()  ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}


// menu.store POST 
// {
//     "desc_code" : "top_navigation",
//     "description" : "Security roles",
//     "icon" : "icon-sys",
//     "class" : "class-sys",
//     "routes" : "sys.index",
//     "sort" : "5",
//     "status" : "A",
//     "lines" : [
//         {
//             "description" :"Security roles",
//             "icon" : "icon-sr",
//             "class" : "class-sr",
//             "routes" : "security.index",
//             "sort" :"1",
//             "status" : "I"
//         },
//         {
//             "description" :"Users",
//             "icon" : "icon-user",
//             "class" : "class-user",
//             "routes" : "user.index",
//             "sort" :"2",
//             "status" : "I"
//         },
//         {
//             "description" :"Menu",
//             "icon" : "icon-menu",
//             "class" : "class-menu",
//             "routes" : "menu.index",
//             "sort" :"3",
//             "status" : "A"
//         }
//     ]
// }