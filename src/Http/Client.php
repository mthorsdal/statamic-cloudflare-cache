<?php

namespace Eminos\StatamicCloudflareCache\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Client
{
    protected string $baseUrl = 'https://api.cloudflare.com/client/v4';
    protected ?string $token;
    protected ?string $zoneId;

    public function __construct()
    {
        $this->token = config('cloudflare-cache.api_token');
        $this->zoneId = config('cloudflare-cache.zone_id');
    }

    public function purgeUrls(array $urls): bool
    {
        if (empty($urls)) {
            return false;
        }
        
        return $this->request('purge_cache', [
            'files' => $urls,
        ]);
    }

    public function purgeEverything(): bool
    {
        return $this->request('purge_cache', [
            'purge_everything' => true,
        ]);
    }

    protected function request(string $action, array $data): bool
    {
        if (!$this->token || !$this->zoneId) {
            Log::error('Cloudflare Cache: API token or Zone ID not configured');
            return false;
        }

        if (config('cloudflare-cache.debug')) {
            Log::debug('Cloudflare Cache: Attempting API call', [
                'action' => $action,
                'data' => $data,
            ]);
        }
        
        $endpoint = "{$this->baseUrl}/zones/{$this->zoneId}";
        
        if ($action === 'purge_cache') {
            $endpoint .= '/purge_cache';
        }
        
        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $data);
                
            if ($response->successful()) {
                Log::info('Cloudflare Cache: Successfully purged cache', [
                    'action' => $action,
                    'success' => $response->json('success'),
                ]);
                return true;
            }
            
            Log::error('Cloudflare Cache: Failed to purge cache', [
                'action' => $action,
                'errors' => $response->json('errors'),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Cloudflare Cache: Exception when purging cache', [
                'action' => $action,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
