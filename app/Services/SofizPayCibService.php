<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SofizPayCibService
{
    public function createPath(): string
    {
        $configured = trim((string) config('services.sofizpay.create_path', ''));
        if ($configured !== '') {
            return str_starts_with($configured, '/') ? $configured : '/' . $configured;
        }

        return $this->isSandbox() ? '/sandbox/make-cib-transaction/' : '/make-cib-transaction/';
    }

    public function checkPath(): string
    {
        $configured = trim((string) config('services.sofizpay.check_path', ''));
        if ($configured !== '') {
            return str_starts_with($configured, '/') ? $configured : '/' . $configured;
        }

        return $this->isSandbox() ? '/sandbox/cib-transaction-check/' : '/cib-transaction-check/';
    }

    public function createCibTransaction(array $queryParams): array
    {
        return $this->sendGet($this->createPath(), $queryParams);
    }

    public function checkCibTransaction(string $orderNumber): array
    {
        return $this->sendGet($this->checkPath(), [
            'order_number' => $orderNumber,
        ]);
    }

    public function isPaidCheck(array $data): bool
    {
        return (string)($data['respCode'] ?? '') === '00'
            && (int)($data['errorCode'] ?? 1) === 0
            && (int)($data['orderStatus'] ?? -1) === 2;
    }

    public function parsePaymentFailureHint(array $data): string
    {
        $candidates = [
            $data['errorMessage'] ?? null,
            $data['message'] ?? null,
            $data['respMsg'] ?? null,
            $data['resultMessage'] ?? null,
            $data['error'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return 'Payment is not confirmed yet. Please retry in a moment.';
    }

    public function parsePaidAmountDzd(array $data): ?float
    {
        $amount = $data['Amount'] ?? $data['amount'] ?? null;
        if ($amount === null || $amount === '') {
            return null;
        }

        return (float) $amount;
    }

    public function parseDestinationAccount(array $data): ?string
    {
        $account = $data['destination_account'] ?? $data['account'] ?? $data['merchant_account'] ?? null;
        if (!is_string($account) || trim($account) === '') {
            return null;
        }

        return trim($account);
    }

    protected function sendGet(string $path, array $queryParams): array
    {
        $url = $this->fullUrl($path);
        $response = Http::timeout($this->timeoutSeconds())->acceptJson()->get($url, $queryParams);

        if (!$response->ok()) {
            $body = trim((string) $response->body());
            $snippet = mb_substr($body, 0, 500);
            throw new \RuntimeException(
                'SofizPay request failed with status ' . $response->status()
                . ' on ' . $url
                . ($snippet !== '' ? ' | body: ' . $snippet : '')
            );
        }

        $json = $response->json();
        if (!is_array($json)) {
            throw new \RuntimeException('SofizPay response is not valid JSON.');
        }

        return $json;
    }

    protected function fullUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('services.sofizpay.base_url', 'https://sofizpay.com'), '/');
        return $baseUrl . $path;
    }

    protected function timeoutSeconds(): int
    {
        return (int) config('services.sofizpay.timeout', 30);
    }

    protected function isSandbox(): bool
    {
        return (bool) config('services.sofizpay.sandbox', false);
    }
}
