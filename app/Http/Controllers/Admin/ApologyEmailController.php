<?php

namespace App\Http\Controllers\Admin;

use App\Mail\ApologyEmail;
use App\Models\ApologyToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ApologyEmailController extends \App\Http\Controllers\Controller
{
    /**
     * Show apology email sending interface
     * Lists all tokens and allows admin to send emails
     */
    public function index()
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $tokens = ApologyToken::where('used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'total_tokens' => ApologyToken::count(),
            'pending_send' => ApologyToken::where('used', false)
                ->where('expires_at', '>', now())->count(),
            'already_sent' => ApologyToken::where('used', true)->count(),
            'expired' => ApologyToken::where('expires_at', '<=', now())->count(),
        ];

        return view('admin.apology-emails.index', compact('tokens', 'stats'));
    }

    /**
     * Send all pending apology emails
     * Admin clicks confirm button, this processes the batch
     */
    public function sendAll(Request $request)
    {
        if (!auth('admin')->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get all unused, non-expired tokens
        $tokens = ApologyToken::where('used', false)
            ->where('expires_at', '>', now())
            ->get();

        if ($tokens->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada email yang perlu dikirim.',
            ]);
        }

        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($tokens as $token) {
            try {
                Mail::to($token->email)->send(new ApologyEmail(
                    $token->email,
                    $token->token,
                ));
                $sent++;

                // Log success
                Log::info("Apology email sent", [
                    'email' => $token->email,
                    'token_id' => $token->id,
                ]);
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'email' => $token->email,
                    'error' => $e->getMessage(),
                ];

                // Log error
                Log::error("Apology email failed", [
                    'email' => $token->email,
                    'token_id' => $token->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
            'total' => $sent + $failed,
            'errors' => $errors,
        ]);
    }

    /**
     * Send email to specific recipient (for testing/resending)
     */
    public function sendOne(Request $request, ApologyToken $token)
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Unauthorized');
        }

        if ($token->used) {
            return back()->with('error', 'Token ini sudah digunakan.');
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return back()->with('error', 'Token ini sudah kadaluarsa.');
        }

        try {
            Mail::to($token->email)->send(new ApologyEmail(
                $token->email,
                $token->token,
            ));

            Log::info("Apology email sent (single)", [
                'email' => $token->email,
                'token_id' => $token->id,
            ]);

            return back()->with('success', "Email berhasil dikirim ke {$token->email}");
        } catch (\Exception $e) {
            Log::error("Apology email failed (single)", [
                'email' => $token->email,
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate expired tokens
     */
    public function regenerateExpired(Request $request)
    {
        if (!auth('admin')->check()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find expired, unused tokens
        $expiredTokens = ApologyToken::where('used', false)
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredTokens->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada token yang kadaluarsa.',
            ]);
        }

        $regenerated = 0;

        foreach ($expiredTokens as $expiredToken) {
            $newToken = \Str::random(32);
            $expiredToken->update([
                'token' => $newToken,
                'expires_at' => now()->addDays(7),
                'used' => false,
                'used_at' => null,
            ]);
            $regenerated++;
        }

        return response()->json([
            'success' => true,
            'regenerated' => $regenerated,
        ]);
    }

    /**
     * View token details and re-registration URL
     */
    public function show(ApologyToken $token)
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $token->url = route('checkout.reregister', $token->token);
        $token->qr_code = route('api.qr', ['url' => base64_encode($token->url)]);

        return view('admin.apology-emails.show', compact('token'));
    }
}
