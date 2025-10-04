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
            return response()->json([
                'success' => true,
                'data'    => $result
            ], 200);

             return response()->json([
                 'success' => false,
                 'message' => 'Error: ' . $e->getMessage()
             ], 500);

          
    }

   
     public function getAllModules()
     {
         try {
            $modules = Menu::orderBy('transNo', 'asc')->get();
             return response()->json([
                 'success' => true,
                 'data'    => $modules
             ], 200);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Error: ' . $e->getMessage()
             ], 500);
         }
     }

    public function saveMenu(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'desc_code'   => 'required|string',
            'description' => 'required|string',
            'icon'        => 'required|string',
            'class'       => 'required|string',
            'routes'      => 'required|string',
            'sort'        => 'required|integer',
            'status'      => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }

        // Check for duplicate description
        if (DB::table('menus')->where('description', $data['description'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Menu description already exists. Please avoid duplicates.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Get next control number safely
            $counter = DB::table('counters')
                ->where('name', 'menu_transNo')
                ->lockForUpdate()
                ->first();

            $transNo = $counter->last_number + 1;

            // Update counter
            DB::table('counters')
                ->where('name', 'menu_transNo')
                ->update(['last_number' => $transNo]);

            // Insert menu
            DB::table('menus')->insert([
                'transNo'    => $transNo,
                'desc_code'  => $data['desc_code'],
                'description'=> $data['description'],
                'icon'       => $data['icon'],
                'class'      => $data['class'],
                'routes'     => $data['routes'],
                'sort'       => $data['sort'],
                'status'     => $data['status'] ?? 'I',
                'created_by' => Auth::user()->fullname,
                'updated_by' => Auth::user()->fullname,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu created successfully',
                'transNo' => $transNo
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function deleteMenu($transNo)
    {
        DB::beginTransaction();

        try {
            // Check if the menu exists
            $menu = DB::table('menus')->where('transNo', $transNo)->first();
            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => "Menu with transNo {$transNo} not found."
                ]);
            }

            // Delete submenus first (if any)
            DB::table('submenus')->where('transNo', $transNo)->delete();

            // Delete the menu
            DB::table('menus')->where('transNo', $transNo)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Menu and its submenus deleted successfully."
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    
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


    public function updateMenuById(Request $request, $id)
    {
        $request->validate([
            'desc_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'class' => 'nullable|string|max:255',
            'routes' => 'required|string|max:255',
            'sort' => 'required|integer|min:1',
            'status' => 'required|string|in:A,I',
        ]);

        $menu = Menu::updateById(
            $id,
            $request->only(['desc_code','description','icon','class','routes','sort','status']),
            Auth::user()->name ?? 'system'
        );

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu updated successfully',
            'menu' => $menu
        ]);
    }


    /**
     * Update the specified resource in storage.
     */


    // public function updateMenuById(Request $request, $id)
    // {
    //     $request->validate([
    //        // 'transNo'     => 'required|integer',
    //         'desc_code' => 'required|string|max:255',
    //         'description' => 'required|string|max:255',
    //         'icon'        => 'required|string|max:255',
    //         'class'       => 'nullable|string|max:255',
    //         'routes'      => 'required|string|max:255',
    //         'sort'        => 'required|integer|min:1',
    //         'status'      => 'required|string|in:A,I',
    //     ]);

    //     $data = Menu::find($id);

    //     if (!$data) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Menu not found'
    //         ], 404);
    //     }

    //     $data->description     = $request->input('description');
    //     $data->desc_code = $request->input('desc_code');
    //     $data->icon        = $request->input('icon');
    //     $data->class       = $request->input('class');
    //     $data->routes       = $request->input('routes'); // note: db column is "route"
    //     $data->sort        = $request->input('sort');
    //     $data->status      = $request->input('status');
    //     $subdatamenu->updated_by  = Auth::user()->name ?? 'system'; // or $request->user if you pass it
    //     $data->save();

    //     return response()->json([
    //         'success'  => true,
    //         'message'  => 'Menu updated successfully',
    //         'submenu'  => $data
    //     ]);
    // }

    

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