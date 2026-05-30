<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class SupabaseStorage
{
    public function upload(UploadedFile $file, string $directory): string
    {
        $extension = strtolower((string) ($file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg'));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg';

        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'imagine';
        $safeName = Str::slug($baseName) ?: 'imagine';
        $safeName = Str::limit($safeName, 80, '');
        $objectPath = trim($directory, '/').'/'.Str::uuid().'-'.$safeName.'.'.$extension;

        $contents = @file_get_contents($file->getRealPath());
        if ($contents === false) {
            throw new RuntimeException('Nu am putut citi fisierul incarcat.');
        }

        return $this->uploadBinary($contents, $objectPath, $file->getMimeType() ?: 'application/octet-stream');
    }

    public function uploadBinary(string $contents, string $objectPath, string $contentType): string
    {
        $this->ensureConfigured();

        $objectPath = trim($objectPath, '/');
        $response = Http::withHeaders(array_merge($this->headers(), [
                'Content-Type' => $contentType,
                'Cache-Control' => '3600',
                'x-upsert' => 'true',
            ]))
            ->withBody($contents, $contentType)
            ->post($this->objectEndpoint($objectPath));

        if (! $response->successful()) {
            Log::error('Supabase upload nereusit', [
                'status' => $response->status(),
                'body' => $response->body(),
                'path' => $objectPath,
            ]);

            throw new RuntimeException('Imaginea nu a putut fi incarcata in Supabase. Verifica URL-ul, cheia si bucket-ul.');
        }

        return $this->publicUrl($objectPath);
    }

    public function deleteByUrl(?string $url): void
    {
        $url = trim((string) $url);
        if ($url === '') {
            return;
        }

        $objectPath = $this->objectPathFromPublicUrl($url);
        if (! $objectPath) {
            return;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->delete($this->bucketEndpoint(), ['prefixes' => [$objectPath]]);

            if (! $response->successful()) {
                Log::warning('Supabase delete nereusit', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'path' => $objectPath,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Supabase delete a aruncat exceptie', [
                'path' => $objectPath,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function ensureConfigured(): void
    {
        if (! $this->baseUrl() || ! $this->apiKey() || ! $this->bucket()) {
            throw new RuntimeException('Supabase nu este configurat. Completeaza SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY si SUPABASE_STORAGE_BUCKET in .env.');
        }
    }

    private function headers(): array
    {
        $key = $this->apiKey();

        return [
            'apikey' => $key,
            'Authorization' => 'Bearer '.$key,
        ];
    }

    private function objectEndpoint(string $objectPath): string
    {
        return $this->baseUrl().'/storage/v1/object/'.$this->bucket().'/'.$this->encodePath($objectPath);
    }

    private function bucketEndpoint(): string
    {
        return $this->baseUrl().'/storage/v1/object/'.$this->bucket();
    }

    private function publicUrl(string $objectPath): string
    {
        return $this->baseUrl().'/storage/v1/object/public/'.$this->bucket().'/'.$this->encodePath($objectPath);
    }

    private function objectPathFromPublicUrl(string $url): ?string
    {
        $prefix = $this->baseUrl().'/storage/v1/object/public/'.$this->bucket().'/';

        if (! Str::startsWith($url, $prefix)) {
            return null;
        }

        return rawurldecode(Str::after($url, $prefix));
    }

    private function encodePath(string $path): string
    {
        return collect(explode('/', trim($path, '/')))
            ->map(fn (string $segment) => rawurlencode($segment))
            ->implode('/');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('supabase.url'), '/');
    }

    private function apiKey(): string
    {
        return (string) config('supabase.service_role_key');
    }

    private function bucket(): string
    {
        return trim((string) config('supabase.bucket'), '/');
    }
}
