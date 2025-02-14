<?php

namespace App\Http\Controllers;

use App\Models\User;
use DB;
use Arr;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function assignRoleToUser($userId)
    {
        // Menemukan user berdasarkan ID
        $user = User::find($userId);

        // Menemukan role dan memberikan permission
        $role = Role::findByName('admin');
        $role->givePermissionTo('read product', 'write product');

        // Memberikan role kepada user
        $user->assignRole('admin');

        return response()->json(['message' => 'Role and permissions assigned successfully']);
    }

    public function index(Request $request)
    {
        // Ambil input dari search
        $search = $request->input('search');
        
        if ($search) {
            $users = User::searchUser($search);
        } else {
            $users = User::paginate(25);
        }
        return view('users.index', compact('users'));
    }

    // Add user with roles
    public function create()
    {
        $roles = Role::all();
        return view('users.add', compact('roles'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        // Validasi input
        $request->validate([
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'roles' => 'required',
        ]);

        // Membuat user baru
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
            'created_at' => now(),
        ]);

        // Tambahkan role ke user
        $user->assignRole($request->input('roles'));

        // Redirect dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    // Edit user with roles
    public function edit(string $id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }
    
    // Update user with roles
    public function update(Request $request, string $id)
    {
        // Validasi input
        $request->validate([
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
            'roles' => 'required',
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'];
        } else {
            $input = Arr::except($input, array('password'));
        }

        // Membuat user baru
        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        // Tambahkan role ke user
        $user->assignRole($request->input('roles'));

        // Redirect dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    // Show user with roles
    public function show(string $id)
    {
        $user = User::find($id);
        $roles = $user->roles;
        return view('users.show', compact('user', 'roles'));
    }


    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
