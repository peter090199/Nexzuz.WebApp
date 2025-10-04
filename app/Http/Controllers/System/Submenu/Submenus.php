<?php

namespace App\Http\Controllers\System\Submenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Submenu;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Facades\Auth; 


class Submenus extends Controller
{
    
    public function saveSubmenus(Request $request)
    {
        try {
            DB::beginTransaction(); // Start a database transaction
    
            $data = $request->all();
    
            // Validate the incoming request
            $submenuErrors = [];
    
            foreach ($data['lines'] as $index => $line) {
                $lineValidator = Validator::make($line, [
                    'description' => 'required|string',
                    'icon' => 'required|string',
                    'class' => 'required|string',
                    'routes' => 'required|string',
                    'sort' => 'required|integer',
                    'status' => 'nullable|string'
                ]);
    
                if ($lineValidator->fails()) {
                    $submenuErrors[$index] = $lineValidator->errors();
                }
    
                // Check if the submenu description already exists
                $subexists = Submenu::where('description', $line['description'])
                    ->where('transNo', $data['transNo']) // Ensure submenu is unique within the same transNo
                    ->exists();
    
                if ($subexists) {
                    return response()->json(['success' => false, 'message' => 'Submenu description already exists. Please avoid duplicates.']);
                }
            }
    
            // If there are validation errors, roll back the transaction
            if (!empty($submenuErrors)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Submenu validation failed', 'errors' => $submenuErrors]);
            }
    
            // Check if the Menu exists by transNo
            $menu = Menu::where('transNo', $data['transNo'])->first();
            if (!$menu) {
                return response()->json(['success' => false, 'message' => 'Menu not found for the given transNo']);
            }
    
            // Get the desc_code from the Menu
            $desc_code = $menu->desc_code;
    
            // Insert submenus
            foreach ($data['lines'] as $line) {
                Submenu::insert([
                    "transNo" => $data['transNo'], 
                    "desc_code" => $desc_code,
                    "description" => $line['description'],
                    'icon' => $line['icon'],
                    'class' => $line['class'],
                    'routes' => $line['routes'],
                    'sort' => $line['sort'],
                    'status' => $line['status'] ?: 'I',
                    'created_by' => Auth::user()->fullname,
                    'updated_by' => Auth::user()->fullname,
                ]);
            }
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Submenus saved successfuly.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
    
     // Update submenu by ID
    public function updateSubmenuById(Request $request, $id)
    {
        $request->validate([
            'transNo'     => 'required|integer',
            'description' => 'required|string|max:255',
            'icon'        => 'required|string|max:255',
            'class'       => 'nullable|string|max:255',
            'routes'      => 'required|string|max:255',
            'sort'        => 'required|integer|min:1',
            'status'      => 'required|string|in:A,I',
        ]);

        $submenu = Submenu::find($id);

        if (!$submenu) {
            return response()->json([
                'success' => false,
                'message' => 'Submenu not found'
            ], 404);
        }

        $submenu->transNo     = $request->input('transNo');
        $submenu->description = $request->input('description');
        $submenu->icon        = $request->input('icon');
        $submenu->class       = $request->input('class');
        $submenu->route       = $request->input('routes'); // note: db column is "route"
        $submenu->sort        = $request->input('sort');
        $submenu->status      = $request->input('status');
        $submenu->updated_by  = Auth::user()->name ?? 'system'; // or $request->user if you pass it

        $submenu->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Submenu updated successfully',
            'submenu'  => $submenu
        ]);
    }

    
    public function getSubmenuByMenuTransNo($transNo)
    {
        // Retrieve the menu by transNo
        $menu = Menu::where('transNo', $transNo)->first();

        if (!$menu) {
            return response()->json(['success' => false, 'message' => 'Menu not found']);
        }

        // Retrieve the associated submenus for the given transNo
        $submenus = Submenu::where('transNo', $transNo)->orderBy('sort', 'asc')->get();

        // Format the submenu data
        $submenuData = $submenus->map(function($submenu) {
            return [
                'id' => $submenu->id,
                'transNo' => $submenu->transNo,
                'desccode' => $submenu->desc_code,
                'description' => $submenu->description,
                'icon' => $submenu->icon,
                'route' => $submenu->routes,
                'sort' => $submenu->sort,
                'status' => $submenu->status,
                'updated_by' => $submenu->updated_by,
            ];
        });

        return response()->json([
            'success' => true,
            'submenus' => $submenuData
        ]);
    }

    public function deleteSubmenu($id)
    {
        try {
            $submenu = DB::table('submenus')->where('id', $id)->first();

            if (!$submenu) {
                return response()->json([
                    'success' => false,
                    'message' => "Submenu with ID {$id} not found."
                ]);
            }

            DB::table('submenus')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => "Submenu deleted successfully."
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }


}
