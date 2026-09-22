<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::select(
            'id',
            'first_name',
            'middle_name',
            'last_name',
            'second_last_name',
            'email',
            'employee_number',
            'user_type',
            'position',
            'url',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($usuarios, 200);
    }

    /**
     * Crear usuario
     */
    public function store(Request $request)
    {
        $validated = $this->validateUsers($request);

        // Generar URL a partir del número de empleado
        $validated['url'] = $validated['employee_number'] . '_f';

        // Password inicial
        $validated['password'] = Hash::make('password');

        $usuario = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario
        ], 201);
    }

    /**
     * Mostrar usuario
     */
    public function show($id)
    {
        $usuario = User::select(
            'id',
            'first_name',
            'middle_name',
            'last_name',
            'second_last_name',
            'email',
            'employee_number',
            'user_type',
            'position',
            'url',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($usuario, 200);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = User::activos()
            ->findOrFail($id);

        $validated = $this->validateUsers($request, $id);

        // Si cambia el número de empleado,
        // también se actualiza la URL
        $validated['url'] = $validated['employee_number'] . '_f';

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data'    => $usuario
        ], 200);
    }

    /**
     * Validación de usuarios
     */
    public function validateUsers(Request $request, $id = null)
    {
        return $request->validate(
            [
                'first_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'middle_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'last_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'second_last_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($id),
                ],

                'employee_number' => [
                    'required',
                    'numeric',
                    Rule::unique('users', 'employee_number')->ignore($id),
                ],

                'user_type' => [
                    'required',
                    'integer',
                    'in:1,2,3,4',
                ],
            ],
            [
                'first_name.required' => 'El primer nombre es obligatorio.',
                'first_name.string'   => 'El primer nombre debe ser texto.',
                'first_name.max'      => 'El primer nombre no puede tener más de 100 caracteres.',

                'middle_name.required' => 'El segundo nombre es obligatorio.',
                'middle_name.string'   => 'El segundo nombre debe ser texto.',
                'middle_name.max'      => 'El segundo nombre no puede tener más de 100 caracteres.',

                'last_name.required' => 'El apellido paterno es obligatorio.',
                'last_name.string'   => 'El apellido paterno debe ser texto.',
                'last_name.max'      => 'El apellido paterno no puede tener más de 100 caracteres.',

                'second_last_name.required' => 'El apellido materno es obligatorio.',
                'second_last_name.string'   => 'El apellido materno debe ser texto.',
                'second_last_name.max'      => 'El apellido materno no puede tener más de 100 caracteres.',

                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email'    => 'El correo electrónico debe ser válido.',
                'email.max'      => 'El correo electrónico no puede tener más de 255 caracteres.',
                'email.unique'   => 'El correo electrónico ya existe.',

                'employee_number.required' => 'El número de empleado es obligatorio.',
                'employee_number.numeric'  => 'El número de empleado debe ser numérico.',
                'employee_number.unique'   => 'El número de empleado ya existe.',

                'user_type.required' => 'El tipo de usuario es obligatorio.',
                'user_type.integer'  => 'El tipo de usuario debe ser un número.',
                'user_type.in'       => 'El tipo de usuario seleccionado no es válido.',
            ]
        );
    }
}
