<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDiscordRequest;
use App\Http\Requests\UpdateMotorcycleRequest;
use App\Models\Motorcycle;
use App\Services\DiscordService;
use App\Services\MotorcycleService;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private MotorcycleService $motorcycleService,
        private DiscordService $discordService,
    ) {}

    public function index(): Response
    {
        $motorcycle = Motorcycle::first();

        $apiKey    = config('ai.providers.anthropic.key');
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

        $ok = $this->discordService->send($url, '✅ Test webhook Carnet Moto — la connexion fonctionne !');

        return $ok
            ? back()->with('success', 'Message de test envoyé sur Discord.')
            : back()->with('error', 'Impossible de joindre le webhook Discord.');
    }
}
