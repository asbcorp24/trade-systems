<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Страница пользователей
    public function index()
    {
        return view('users.index', [
            'users' => User::orderBy('id', 'desc')->get()
        ]);
    }

    // Создание
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'login' => 'required|unique:users',
            'password' => 'required',
            'role'  => 'required'
        ]);

        User::create($request->all());

        return response()->json(['success' => true]);
    }

    // Обновление
    public function update(Request $request, $id)
    {
        $u = User::findOrFail($id);

        $request->validate([
            'name'  => 'required',
            'login' => 'required|unique:users,login,' . $id,
            'role'  => 'required'
        ]);

        $data = $request->all();

        if (!$request->password) unset($data['password']);

        $u->update($data);

        return response()->json(['success' => true]);
    }

    // Удаление
    public function delete($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
