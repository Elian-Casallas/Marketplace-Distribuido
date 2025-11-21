<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserMarketplace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserMarketplaceControllers extends Controller
{
    public function informacionUsuarios(Request $request)
    {
        // filtro por atributo: ?filter=color:Azul
        try{
            if ($request->has('filter')) {
                $parts = explode(':', $request->query('filter'), 2);
                if (count($parts) === 2) {
                    $key = $parts[0];
                    $value = $parts[1];
                    $items = UserMarketplace::where("attributes.$key", $value)->get();
                    return response()->json($items);
                }
            }
            $usuario = UserMarketplace::all();
            Log::info('✅ (UserMarketplaceControllers - informacionUsuarios) Usuarios obtenidos correctamente.');
            return response()->json($usuario);
        } catch (\Throwable $e) {
            Log::warning("⚠️ Error en UserMarketplaceControllers - informacionUsuarios: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener los usuarios.'], 500);
        }

    }

    public function informacionUsuario(Request $request, $id)
    {
        try {
            Log::info("🔍 (UserMarketplaceControllers - informacionUsuario) Buscando usuario con identificación: $id");
            $tipo = $request->query('tipo', 'identificacion');
            if ($tipo === 'id') {
                $usuario = UserMarketplace::where('id', $id)->first();
            } else {
                $usuario = UserMarketplace::where('identificacion', $id)->first();
            }
            if (!$usuario) {
                Log::warning("⚠️ (UserMarketplaceControllers - informacionUsuario) Usuario con identificación $id no encontrado.");
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            Log::info("✅ (UserMarketplaceControllers - informacionUsuario) Usuario encontrado: {$usuario->name}");
            return response()->json($usuario);
        } catch (\Throwable $e) {
            Log::error("❌ (UserMarketplaceControllers - informacionUsuario) Error: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al buscar un Usuario.'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $id = $request->input('identificacion');
            $password = $request->input('password');
            Log::info("🔍 (UserMarketplaceControllers - login) Buscando usuario con identificación: $id");
            $usuario = UserMarketplace::where('identificacion', $id)->first();
            if (!$usuario) {
                Log::warning("⚠️ (UserMarketplaceControllers - login) Usuario con identificación $id no encontrado.");
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }
            Log::info("✅ (UserMarketplaceControllers - login) Usuario encontrado: {$usuario->name}");

            if (!Hash::check($password, $usuario->password)) {
                return response()->json(['error' => 'Contraseña incorrecta'], 401);
            }
            return response()->json([
                'existe' => true,
                'identificacion' => $usuario->identificacion,
                'name' => $usuario->name,
                'id' => $usuario->id
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ (UserMarketplaceControllers - login) Error: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al buscar un Usuario.'], 500);
        }
    }

    public function guardarUsuario(Request $request)
    {
        try {
            Log::info('🟢 (UserMarketplaceControllers - guardarUsuario) Intentando crear usuario...');
            Log::info("🔹 name: " . json_encode($request->input('telefono')) . " | tipo: " . gettype($request->input('telefono')));
            $validated = $request->validate([
                'name' => 'sometimes|string',
                'email' => 'sometimes|email|unique:usersMarketplace,email,',
                'password' => 'sometimes|string|min:6',
                'identificacion' => 'sometimes|string|unique:usersMarketplace,identificacion,',
                'isSeller' => 'boolean',
                'telefono' => 'sometimes|digits_between:7,15',
                'productosVenta' => 'sometimes|array',
                'domicilio' => 'sometimes|array',
            ]);
            $usuarioExist = UserMarketplace::where('identificacion', $validated['identificacion'])->first();
            if ($usuarioExist) {
                Log::warning("⚠️ (UserMarketplaceControllers - informacionUsuario) Usuario con identificación {$validated['identificacion']} encontrado.");
                return response()->json(['error' => 'Usuario encontrado'], 202);
            }
            // 👉 Hashear la contraseña ANTES de crear el usuario
            $validated['password'] = bcrypt($validated['password']);

            $usuario = UserMarketplace::create($validated);
            Log::info("✅ (UserMarketplaceControllers - guardarUsuario) Usuario creado: {$usuario->name}");
            return response()->json([
                'message' => 'Usuario creado correctamente',
                'usuario' => $usuario
            ], 201);
        } catch (\Throwable $e) {
            Log::error("❌ (UserMarketplaceControllers - guardarUsuario) Error al crear usuario: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al crear el usuario.'], 500);
        }
    }

    public function actualizarUsuario(Request $request, $id)
    {
        try {
            Log::info("🟡 (UserMarketplaceControllers - actualizarUsuario) Intentando actualizar usuario con identificación: $id");
            $isSeller = $request->input('password');
            Log::info("🔍 isSeller: " . $isSeller . " | tipo: " . gettype($isSeller));
            $usuario = UserMarketplace::find($id);

            if (!$usuario) {
                Log::warning("⚠️ (UserMarketplaceControllers - actualizarUsuario) Usuario con identificación $id no encontrado.");
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }
            $validated = $request->validate([
                'name' => 'sometimes|string',
                'email' => 'sometimes|email|unique:usersMarketplace,email,',
                'password' => 'sometimes|string|min:6',
                'isSeller' => 'boolean',
                'telefono' => 'sometimes|digits_between:7,15',
                'productosVenta' => 'sometimes|array',
                'domicilio' => 'sometimes|array',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }

            $usuario->update($validated);

            Log::info("✅ (UserMarketplaceControllers - actualizarUsuario) Usuario actualizado: {$usuario->name}");

            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'usuario' => $usuario
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ (UserMarketplaceControllers - actualizarUsuario) Error al actualizar Usuario con identificación $id: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar el Usuario.'], 500);
        }
    }
}
