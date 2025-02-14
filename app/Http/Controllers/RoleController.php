<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $permissions = Permission::all();
        return view('roles.add', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|unique:roles',
            'permissions' => 'nullable|array|exists:permissions,id',  // Validasi permissions harus ada di database
        ]);

        // Membuat role baru
        $role = Role::create(['name' => $request->name]);

        // Jika permissions ada, tambahkan permissions ke role
        if ($request->permissions) {
            // Validasi tambahan: pastikan ID permissions valid
            $permissions = Permission::whereIn('id', $request->permissions)->get();  // Mengambil objek Permission berdasarkan ID
            if ($permissions->count() !== count($request->permissions)) {
                // Jika ada ID permission yang tidak ditemukan di database, beri tahu pengguna
                return redirect()->back()->withErrors('Some permissions are invalid.');
            }

            // Memberikan permission ke role yang baru
            $role->givePermissionTo($permissions);
        }

        // Redirect dengan pesan sukses
        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $role = Role::findOrFail($id);
        $permissions = $role->permissions;
        return view('roles.show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $role = Role::findOrFail($id);
        $permissions = Permission::all();

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'array|exists:permissions,id', // Validasi bahwa ID permissions ada di database
        ]);

        // Temukan role berdasarkan ID
        $role = Role::findOrFail($id);

        // Update nama role
        $role->update(['name' => $request->name]);

        // Ambil objek Permission berdasarkan ID yang dikirim
        $permissions = Permission::find($request->permissions);

        // Pastikan permissions ditemukan
        if ($permissions->isEmpty()) {
            return redirect()->back()->withErrors('Some permissions are invalid.');
        }

        // Sinkronisasi permissions dengan role menggunakan objek Permission
        $role->syncPermissions($permissions);

        // Redirect dengan pesan sukses
        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }
}
