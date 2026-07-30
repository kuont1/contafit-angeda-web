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

    public function sendDeletionCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = $request->user();
        $cooldownKey = "user_del_cooldown_{$user->id}";

        if (\Illuminate\Support\Facades\Cache::has($cooldownKey)) {
            $secondsLeft = max(1, \Illuminate\Support\Facades\Cache::get($cooldownKey) - time());
            $mins = ceil($secondsLeft / 60);
            return $this->errorResponse("Por seguridad, debes esperar {$mins} minuto(s) antes de solicitar otro código de verificación.", null, 429);
        }

        if (! Hash::check($request->input('password'), $user->password)) {
            return $this->errorResponse('La contraseña ingresada es incorrecta.', null, 403);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        \Illuminate\Support\Facades\Cache::put("user_del_code_{$user->id}", $code, now()->addMinutes(10));
        \Illuminate\Support\Facades\Cache::put($cooldownKey, time() + 300, now()->addMinutes(5));

        $apiKey = config('services.brevo.key');
        $senderEmail = config('mail.from.address', 'contafitmach@gmail.com');
        $codeSent = false;

        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => ['name' => 'ContaFit Agenda Security', 'email' => $senderEmail],
                    'to' => [['email' => $user->email, 'name' => $user->first_name ?? $user->name]],
                    'subject' => "🔒 Código de Verificación para Eliminar tu Cuenta",
                    'textContent' => "🔒 Confirmación de Baja de Cuenta\n\nHola {$user->name},\n\nTu código de verificación para eliminar definitivamente tu cuenta de ContaFit Agenda Web es: {$code}\n\nEste código vencerá en 10 minutos. Si no solicitaste esta acción, cambia tu contraseña inmediatamente.\n\nSaludos,\nEquipo de Seguridad de ContaFit Agenda",
                    'htmlContent' => "<html><body>
                        <h2>🔒 Código de Verificación de Seguridad</h2>
                        <p>Hola <strong>{$user->name}</strong>,</p>
                        <p>Has solicitado eliminar tu cuenta de ContaFit Agenda Web. Tu código de verificación es:</p>
                        <h1 style='color: #ef4444; font-size: 32px; letter-spacing: 4px;'>{$code}</h1>
                        <p>Este código caducará en 10 minutos. Si no solicitaste la baja, por favor ignora este mensaje.</p>
                    </body></html>",
                ]);

                if ($response->successful()) {
                    $codeSent = true;
                }
            } catch (\Throwable $e) {
                Log::warning("Error enviando código de borrado vía Brevo API: " . $e->getMessage());
            }
        }

        if (! $codeSent) {
            try {
                Mail::raw("Tu código de verificación para eliminar tu cuenta es: {$code}", function ($msg) use ($user) {
                    $msg->to($user->email)->subject("🔒 Código de Verificación para Eliminar tu Cuenta");
                });
            } catch (\Throwable $e) {
                Log::warning("Error enviando código por Mail facade: " . $e->getMessage());
            }
        }

        return $this->successResponse([
            'email' => $user->email,
        ], "Código de verificación enviado a tu correo ({$user->email}).");
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string'],
            'verification_code' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return $this->errorResponse('La contraseña ingresada es incorrecta.', null, 403);
        }

        // Si se envió un código de verificación, validarlo contra Caché
        if ($request->filled('verification_code')) {
            $cachedCode = \Illuminate\Support\Facades\Cache::get("user_del_code_{$user->id}");
            if (! $cachedCode || $cachedCode !== trim((string) $request->input('verification_code'))) {
                return $this->errorResponse('El código de verificación es incorrecto o ha expirado. Por favor solicita uno nuevo.', null, 422);
            }
            \Illuminate\Support\Facades\Cache::forget("user_del_code_{$user->id}");
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