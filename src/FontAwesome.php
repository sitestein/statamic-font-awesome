<?php

namespace Sitestein\FontAwesome;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Statamic\Support\Str;

class FontAwesome
{
    protected $apiToken;
    protected $kitToken;
    protected $version;

    public function __construct()
    {
        $this->apiToken = config('statamic-font-awesome.api_token');
        $this->kitToken = config('statamic-font-awesome.kit_token');
    }

    public function kit(string $token = null): Collection
    {
        if ($token) {
            $this->kitToken = $token;
        }

        return Cache::rememberForever("font_awesome::kit::{$this->kitToken}", function () {
            $response = Http::withToken($this->authToken())
                ->post('https://api.fontawesome.com', [
                    'query' => $this->kitQuery()
                ])->json()['data']['me']['kit'];

            return collect([
                'id' => $response['token'],
                'url' => "https://kit.fontawesome.com/{$response['token']}.js",
                'license' => $response['licenseSelected'],
                'version' => $response['version'],
            ]);
        });
    }

    public function styles(): Collection
    {
        return Cache::rememberForever('font_awesome::styles', function () {
            $response = Http::post('https://api.fontawesome.com', [
                'query' => $this->stylesQuery()
            ])->json()['data']['release']['icons'];

            return collect($response)->flatMap(function ($icon) {
                // The styles available for the license type of the kit.
                return $icon['membership'][$this->kit()->get('license')];
            })->unique();
        });
    }

    public function search($query)
    {
        return Cache::remember('font_awesome::search::'. sha1($query), 60 * 5, function () use ($query) {
            return Http::post('https://api.fontawesome.com', [
                'query' => $this->searchQuery($query)
            ])->json()['data']['search'];
        });
    }

    protected function iconClass(string $icon, string $style): string
    {
        return Str::startsWith($this->kit()->get('version'), '5')
            ? 'fa' . substr($style, 0, 1) . ' fa-' . $icon
            : "fa-$style fa-$icon";
    }

    protected function authToken(): string
    {
        if ($token = Cache::get('font_awesome::token')) {
            return $token;
        }

        $response = Http::withToken($this->apiToken)
            ->post('https://api.fontawesome.com/token')
            ->collect();

        Cache::put('font_awesome::token', $response->get('access_token'), $response->get('expires_in'));

        return $response->get('access_token');
    }

    protected function stylesQuery(): string
    {
        return
            'query {
                release (version:' . '"' . $this->kit()->get('version') . '"' . ') {
                    icons {
                        membership {' . $this->kit()->get('license') . '}
                    }
                }
            }';
    }

    protected function kitQuery(): string
    {
        return
            'query {
                me {
                    kit (token:' . '"' . $this->kitToken . '"' . ') {
                        token
                        licenseSelected
                        version
                        iconUploads {' . "name" . '}
                    }
                }
            }';
    }

    protected function searchQuery($search): string
    {
        return
            'query {
                search (version: "' . $this->kit()->get('version') . '", query: "'. $search .'") {
                    value: id
                    label
                    styles
                }
            }';
    }
}
