<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDiscordRequest;
use App\Http\Requests\UpdateMotorcycleRequest;
use App\Models\Motorcycle;
use App\Services\MotorcycleService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(private MotorcycleService $motorcycleService) {}

    public function index(): Response
    {
        $motorcycle = Motorcycle::first();

        $apiKey = config('services.anthropic.key') ?? env('ANTHROPIC_API_KEY', '');
        $maskedKey = $apiKey ? substr($apiKey, 0, 10) . '...' . substr($apiKey, -4) : null;

        return Inertia::render('Settings/Index', [
            'motorcycle'       => $motorcycle,
            'maintenanceTypes' => $motorcycle->maintenanceTypes()->orderBy('name')->get(),
            'maskedApiKey'     => $maskedKey,
        ]);
    }

    public function updateMotorcycle(UpdateMotorcycleRequest $request)
    {
        $motorcycle = Motorcycle::first();
        $this->motorcycleService->update($motorcycle, $request->validated(), $request->file('photo'));

        return back()->with('success', 'Informations de la moto mises à jour.');
    }

    public function updateDiscord(UpdateDiscordRequest $request)
    {
        Motorcycle::first()->update([
            'discord_webhook_url' => $request->discord_webhook_url,
        ]);

        return back()->with('success', 'Webhook Discord mis à jour.');
    }

    public function testDiscord()
    {
        $url = Motorcycle::first()->discord_webhook_url;

        if (!$url) {
            return back()->with('error', 'Aucun webhook Discord configuré.');
        }

        try {
            $response = Http::post($url, [
                'content' => '✅ Test webhook Carnet Moto — la connexion fonctionne !',
            ]);

            if ($response->successful()) {
                return back()->with('success', 'Message de test envoyé sur Discord.');
            }

            return back()->with('error', 'Erreur Discord : ' . $response->status());
        } catch (ConnectionException) {
            return back()->with('error', 'Impossible de joindre le webhook Discord.');
        }
    }
}
