<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'second_last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = User::create([
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'last_name' => $request->input('last_name'),
            'second_last_name' => $request->input('second_last_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Disparar correo de bienvenida vía Brevo REST API si está configurada
        $apiKey = config('services.brevo.key');
        $senderEmail = config('mail.from.address', 'contafitmach@gmail.com');
        $welcomeSent = false;

        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => ['name' => 'ContaFit Agenda', 'email' => $senderEmail],
                    'to' => [['email' => $user->email, 'name' => $user->first_name]],
                    'subject' => "🎉 ¡Bienvenido a ContaFit Agenda Web!",
                    'textContent' => "🎉 ¡Hola {$user->first_name}!\n\nBienvenido a ContaFit Agenda Web. Tu cuenta se ha creado exitosamente con el correo {$user->email}.\n\nYa puedes comenzar a organizar tus tareas, recordatorios y consultar los feriados oficiales de Ecuador.\n\nSaludos,\nEl equipo de ContaFit Agenda",
                    'htmlContent' => "<html><body>
                        <h2>🎉 ¡Bienvenido, {$user->first_name}!</h2>
                        <p>Tu cuenta se ha creado exitosamente con el correo <strong>{$user->email}</strong> en ContaFit Agenda Web.</p>
                        <p>Ya puedes comenzar a organizar tus tareas, recordatorios y consultar los feriados oficiales de Ecuador.</p>
                        <br><p>Saludos,<br>El equipo de ContaFit Agenda</p>
                    </body></html>",
                ]);

                if ($response->successful()) {
                    $welcomeSent = true;
                    Log::info("Correo de bienvenida enviado a {$user->email} vía Brevo API v3. Message ID: " . $response->json('messageId'));
                } else {
                    Log::warning("Error al enviar bienvenida Brevo API ({$response->status()}): " . $response->body());
                }
            } catch (\Throwable $e) {
                Log::warning("Excepción al enviar bienvenida vía Brevo API: " . $e->getMessage());
            }
        }

        if (! $welcomeSent) {
            try {
                Mail::raw(
                    "🎉 ¡Hola {$user->first_name}!\n\nBienvenido a ContaFit Agenda Web. Tu cuenta se ha creado exitosamente con el correo {$user->email}.\n\nYa puedes comenzar a gestionar tus tareas, recordatorios y consultar los feriados nacionales de Ecuador.\n\nSaludos,\nEl equipo de ContaFit Agenda",
                    function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject("🎉 ¡Bienvenido a ContaFit Agenda Web!");
                    }
                );
            } catch (\Throwable $e) {
                Log::warning("No se pudo enviar el correo de bienvenida por Mail facade a {$user->email}: " . $e->getMessage());
            }
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            // Log si el servidor de correo aún no está alcanzable durante desarrollo
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'auth_token' => $token,
        ], 'Usuario registrado correctamente. Te hemos enviado un correo de bienvenida.', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return $this->errorResponse('Credenciales inválidas.', null, 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'auth_token' => $token,
        ], 'Inicio de sesión exitoso.');
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return $this->successResponse(null, 'Sesión cerrada correctamente.');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => $request->user(),
        ], 'Usuario autenticado.');
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'show_holidays' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = $request->user();
        $user->update([
            'show_holidays' => $request->input('show_holidays'),
        ]);

        return $this->successResponse([
            'user' => $user->fresh(),
        ], 'Ajustes actualizados correctamente.');
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return $this->errorResponse('La contraseña ingresada es incorrecta.', null, 403);
        }

        // Hard delete en cascada de todos los eventos y tokens
        $user->tokens()->delete();
        $user->events()->forceDelete();
        $user->delete();

        return $this->successResponse(null, 'Cuenta eliminada de forma permanente.');
    }

    private function successResponse(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    private function errorResponse(string $message, mixed $data = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    private function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse('Error de validación.', [
            'errors' => $errors,
        ], 422);
    }
}