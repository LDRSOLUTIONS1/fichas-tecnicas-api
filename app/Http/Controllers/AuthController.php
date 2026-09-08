<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name'        => 'required|string|max:100',
                'middle_name'       => 'nullable|string|max:100',
                'last_name'         => 'required|string|max:100',
                'second_last_name'  => 'required|string|max:100',
                'email'             => 'required|string',
                'phone'             => 'nullable|string|max:20',
                'employee_number'   => 'required|string|max:50',
                'position'          => 'required|string|max:255',
            ], [
                'first_name.required' => 'El primer nombre es obligatorio.',
                'last_name.required' => 'El primer apellido es obligatorio.',
                'second_last_name.required' => 'El segundo apellido es obligatorio.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'employee_number.required' => 'El número de colaborador es obligatorio.',
                'position.required' => 'El puesto es obligatorio.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();

            $data['url'] = $request->employee_number . '_f';

            $position = strtoupper(trim($request->position));

            if (str_contains($position, 'DIRECTOR')) {
                $data['user_type'] = User::DIRECTOR;
            } elseif (str_contains($position, 'GERENTE')) {
                $data['user_type'] = User::GERENTE;
            } else {
                $data['user_type'] = $request->user_type ?? User::VIEWER;
            }

            $data['password'] = Hash::make($request->password);

            $user = User::create($data);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario registrado exitosamente.',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar usuario.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logincollaborator($employee_number)
    {
        try {
            $user = User::where('employee_number', $employee_number)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Empleado no encontrado.'
                ], 404);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Autenticación exitosa',
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al autenticar empleado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $jwt = $request->cookie('token');

        if (!$jwt) {
            return response()->json([
                'message' => 'No existe token'
            ], 401);
        }

        try {
            $decoded = JWT::decode(
                $jwt,
                new Key(config('jwt.secret_rh'), 'HS256')
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }

        $user = User::where(
            'external_rh_id',
            $decoded->id_colaborador
        )->first();

        if (!$user) {
            $user = User::where(
                'email',
                $decoded->correo
            )->first();
        }

        if ($user) {
            $user->update([
                'external_rh_id' => $decoded->id_colaborador,
                'collaborator_number' => $decoded->id_colaborador,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'location_name' => $decoded->nombre_sede
            ]);
        } else {
            $user = User::create([
                'external_rh_id' => $decoded->id_colaborador,
                'collaborator_number' => $decoded->id_colaborador,
                'role_id' => 3,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'location_name' => $decoded->nombre_sede,
                'password' => Hash::make(Str::random(40))
            ]);
        }

        $tokenResult = $user->createToken('reportes');
        $token = $tokenResult->plainTextToken;

        AccessLog::create([
            'user_id'    => $user->id,
            'token_id'   => $tokenResult->accessToken->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at'   => now(),
        ]);

        return response()->json([

            'token' => $token,
            'user' => $user

        ]);
    }
}
