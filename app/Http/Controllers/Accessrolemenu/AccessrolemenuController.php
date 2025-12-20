<?php

namespace App\Http\Controllers\Accessrolemenu;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator;
use App\Models\Roleaccessmenu;
use App\Models\Roleaccesssubmenu;
use App\Models\Submenu;
use App\Models\Menu;
use DB;
use Illuminate\Support\Facades\Cache;

class AccessrolemenuController extends Controller
{

  public function getModule(Request $request)
    {
        // ✅ Check authentication
        if (!Auth::check()) {
            return response("authenticated", 401);
        }

        $roleCode = Auth::user()->role_code;
        $descCode = $request->input('desc_code');

        // ✅ Cache results for this role + desc_code for 5 minutes
        $cacheKey = "accessmenu_{$roleCode}_{$descCode}";
        return Cache::remember($cacheKey, 300, function () use ($roleCode, $descCode) {

            // ✅ Get all allowed menu IDs for this role
            $roleMenus = Roleaccessmenu::where('rolecode', $roleCode)->pluck('menus_id', 'transNo');

            // ✅ Fetch all menus in one query
            $menus = Menu::whereIn('id', $roleMenus->values())
                ->where('status', 'A')
                ->where('desc_code', $descCode)
                ->orderBy('sort')
                ->get();

            // ✅ Get all submenu access for this role (one query)
            $roleSubmenus = Roleaccesssubmenu::where('rolecode', $roleCode)->get();

            // ✅ Get all submenu IDs used by role
            $submenuIds = $roleSubmenus->pluck('submenus_id');

            // ✅ Fetch all submenus in one query
            $submenus = Submenu::whereIn('id', $submenuIds)
                ->where('status', 'A')
                ->where('desc_code', $descCode)
                ->orderBy('sort')
                ->get()
                ->groupBy('id');

            // ✅ Build structured result
            $result = $menus->map(function ($menu) use ($roleMenus, $roleSubmenus, $submenus) {
                $subList = [];

                // Find all submodules for this menu's transaction number
                $transNo = $roleMenus->search($menu->id);
                $subsForMenu = $roleSubmenus->where('transNo', $transNo);

                foreach ($subsForMenu as $sub) {
                    $found = $submenus->get($sub->submenus_id);
                    if ($found) {
                        $item = $found->first();
                        $subList[] = [
                            "description" => $item->description,
                            "icon" => $item->icon,
                            "route" => $item->routes,
                            "sort" => $item->sort
                        ];
                    }
                }

                return [
                    "description" => $menu->description,
                    "icon" => $menu->icon,
                    "route" => $menu->routes,
                    "sort" => $menu->sort,
                    "submenus" => collect($subList)->sortBy('sort')->values()->toArray()
                ];
            })->sortBy('sort')->values();

            return response()->json($result);
        });
    }

   public function index(Request $request)
{
    $roleCode = $request->user()->role_code;

    // Get main menus for this role
    $menus = DB::table('roleaccessmenus')
        ->where('rolecode', $roleCode)
        ->get();

    $result = [];

    foreach ($menus as $menu) {
        // Get submenus linked to this menu
        $submenus = DB::table('roleaccesssubmenus')
            ->where('rolecode', $roleCode)
            ->where('menus_id', $menu->menus_id) // link submenu to main menu
            ->get();

        // Map submenus to 'lines' array
        $lines = $submenus->map(function ($sub) {
            return [
                'submenus_id' => $sub->submenus_id, // submenu identifier
            ];
        })->toArray(); // convert collection to array

        $result[] = [
            'rolecode' => $roleCode,
            'menus_id' => $menu->menus_id, // main menu identifier
            'lines' => $lines,
        ];
    }

    return response()->json($result);
}

    // public function index(Request $request)
    // {

    //         if (Auth::check()) {
    //             $modules = Roleaccessmenu::where('rolecode', Auth::user()->role_code)->get(); 

    //             $result = [];
    //             for ($m = 0; $m < count($modules); $m++) {
                    
    //                 $menus = Menu::where('id', $modules[$m]->menus_id)
    //                     ->where('status', 'A')
    //                     ->where('desc_code', $request->desc_code)
    //                     ->orderBy('sort')
    //                     ->get();

                
    //                 for ($me = 0; $me < count($menus); $me++) {
                        
    //                     $submodule = Roleaccesssubmenu::where([
    //                         ['rolecode', Auth::user()->role_code],
    //                         ['transNo', $modules[$m]->transNo]
    //                     ])->get();

    //                     // Initialize an empty submenus array
    //                     $sub = [];

                
    //                     for ($sb = 0; $sb < count($submodule); $sb++) {
    //                         $submenus = Submenu::where('id', $submodule[$sb]->submenus_id)
    //                             ->where('status', 'A')
    //                             ->where('desc_code', $request->desc_code)
    //                             ->orderBy('sort')
    //                             ->get();
    //                         for ($su = 0; $su < count($submenus); $su++) {
    //                             $sub[] = [
    //                                 "description" => $submenus[$su]->description,
    //                                 "icon" => $submenus[$su]->icon,
    //                                 "route" => $submenus[$su]->routes,
    //                                 "sort" => $submenus[$su]->sort
    //                             ];
    //                         }
    //                     }

                    
    //                     $result[] = [
    //                         "description" => $menus[$me]->description,
    //                         "icon" => $menus[$me]->icon,
    //                         "route" => $menus[$me]->routes,
    //                         "sort" => $menus[$me]->sort,
    //                         "submenus" => $sub
    //                     ];
    //                 }
    //             }

            
    //             usort($result, function($a, $b) {
    //                 return $a['sort'] <=> $b['sort'];
    //             });

    //             return response()->json($result);
    //         } else {
    //             return response("authenticated");
    //         }


            
    // }
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
// accessmenu.index GET 
// {
//     "desc_code" : "tnavigation_token"
// }