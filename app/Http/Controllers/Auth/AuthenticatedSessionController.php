<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        
        $request->session()->regenerate();

        // Vérifier le statut de l'utilisateur.
        // Les étudiants doivent toujours pouvoir se connecter : la "désactivation"
        // d'un étudiant par l'administration ne doit pas bloquer son accès, seule
        // la suppression (status = failed) le doit. Pour le personnel en revanche,
        // le statut "pending" bloque bien la connexion tant que le compte n'a pas
        // été validé par un administrateur.
        $user = Auth::user();
        $isStudent = in_array($user->role, ['student', 'etudiant'], true);

        $blocked = $isStudent
            ? $user->status === 'failed'
            : $user->status !== 'Success';

        if ($blocked) {
            // Déconnecter l'utilisateur
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $isStudent
                ? 'Votre compte a été supprimé. Veuillez contacter l\'administration.'
                : 'Votre compte n\'a pas encore été validé par l\'administrateur. Veuillez patienter ou contacter le service concerné.';

            return redirect()->route('login')->with('error', $message);
        }

        // Vérifier l'autorisation de connexion, indépendamment du statut du compte.
        if (! $user->peut_se_connecter) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Votre connexion a été désactivée. Veuillez contacter l\'administration.');
        }

        // Redirection vers l'espace correspondant au rôle / poste de l'utilisateur.
        return redirect($user->homePath())
            ->with('success', 'Bienvenue ' . ($user->name ?? '') . ' !');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}