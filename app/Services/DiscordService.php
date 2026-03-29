<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class DiscordService
{
    public function send(string $url, string $message): bool
    {
        try {
            return Http::post($url, ['content' => $message])->successful();
        } catch (ConnectionException) {
            return false;
        }
    }
}
