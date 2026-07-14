<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * This is set dynamically in the __construct() method below.
     * It detects the correct URL based on trusted proxy headers and a
     * whitelist of allowed hosts to prevent Host Header Injection attacks.
     *
     * IMPORTANT: Make sure 'app.baseURL' is COMMENTED OUT in your .env file!
     */
    public string $baseURL = 'http://bms.test/';

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     */
    public string $indexPage = '';

    /**
     * --------------------------------------------------------------------------
     * URI Protocol
     * --------------------------------------------------------------------------
     */
    public string $uriProtocol = 'REQUEST_URI';

    public string $permittedURIChars = 'a-z 0-9~%.:_\-';
    public string $defaultLocale = 'en';
    public bool $negotiateLocale = false;
    public array $supportedLocales = ['en'];
    public string $appTimezone = 'UTC';
    public string $charset = 'UTF-8';
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     */
    public array $proxyIPs = [
        '0.0.0.0/0' => 'X-Forwarded-For',
    ];

    public bool $CSPEnabled = false;

    // =========================================================================
    // DYNAMIC BASE URL LOGIC
    // =========================================================================

    /**
     * Whitelist of trusted hostnames.
     */
    private array $trustedHosts = [
        '100.108.49.45',
        'bms.test',
        'bms.rps-home-lab.com',
        'localhost'
    ];

    /**
     * Default fallback URL used in CLI mode or untrusted requests.
     */
    private string $defaultBaseURL = 'http://bms.test/';

    /**
     * Constructor - Dynamic Base URL Detection
     */
    public function __construct()
    {
        parent::__construct();

        // 1. CLI Detection
        if ($this->isCli()) {
            $this->baseURL = $this->defaultBaseURL;
            return;
        }

        // 2. Determine the Host (keeps original port if present)
        $rawHost = $this->detectHost();

        // 3. Strip port from host for security checks
        $hostWithoutPort = $this->stripPort($rawHost);

        // 4. Security Check
        if (! $this->isHostTrusted($hostWithoutPort)) {
            log_message(
                'warning',
                'App::__construct() - Untrusted Host header detected: "{host}". Falling back to default.',
                ['host' => $rawHost]
            );
            $this->baseURL = $this->defaultBaseURL;
            return;
        }

        // 5. Determine Scheme
        $scheme = $this->detectScheme();

        // 6. Determine Port (Passes rawHost for port extraction)
        $port = $this->detectPort($scheme, $rawHost);

        // 7. Build port suffix (hides :80 and :443)
        $portSuffix = $this->buildPortSuffix($scheme, $port);

        $this->baseURL = "{$scheme}://{$hostWithoutPort}{$portSuffix}/";
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    private function isCli(): bool
    {
        return defined('STDIN')
            || php_sapi_name() === 'cli'
            || ! isset($_SERVER['HTTP_HOST']);
    }

    private function detectHost(): string
    {
        // Cloudflare Tunnel sends the original public host via HTTP_X_FORWARDED_HOST
        if (! empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $hosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
            return trim($hosts[0]);
        }

        if (! empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        if (! empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        return 'bms.test';
    }

    private function stripPort(string $host): string
    {
        if (str_contains($host, ']')) {
            return preg_replace('/\]:\d+$/', ']', $host) ?? $host;
        }

        return strtok($host, ':') ?: $host;
    }

    private function isHostTrusted(string $host): bool
    {
        return in_array(
            strtolower($host),
            array_map('strtolower', $this->trustedHosts),
            true
        );
    }

    private function detectScheme(): string
    {
        if (! empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
        }

        if (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        }

        return 'http';
    }

    /**
     * Detect the port number.
     * Prioritizes proxy headers to prevent internal ports (like 8081) 
     * from leaking into the public Cloudflare URL structure.
     */
    private function detectPort(string $scheme, string $rawHost): int
    {
        // 1. Check for custom Docker-injected external port
        if (! empty($_SERVER['MY_EXTERNAL_PORT'])) {
            return (int) $_SERVER['MY_EXTERNAL_PORT'];
        }
    
        if ($envPort = getenv('MY_EXTERNAL_PORT')) {
            return (int) $envPort;
        }

        // 2. If behind Cloudflare / reverse proxy, map to standard scheme ports (80/443).
        // This stops internal container/host ports (like :8081) from breaking public URLs.
        if (! empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return ($scheme === 'https') ? 443 : 80;
        }

        // 3. Check X-Forwarded-Port (set by standard proxies)
        if (! empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            return (int) trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PORT'])[0]);
        }

        // 4. Extract port from Host header if accessing directly (e.g. 100.108.49.45:8081)
        if (str_contains($rawHost, ':')) {
            $parts = explode(':', $rawHost);
            $possiblePort = (int) end($parts);
            if ($possiblePort > 0) {
                return $possiblePort;
            }
        }
    
        // 5. Fallback to standard local web server detection
        if (! empty($_SERVER['SERVER_PORT'])) {
            return (int) $_SERVER['SERVER_PORT'];
        }
    
        return ($scheme === 'https') ? 443 : 80;
    }

    private function buildPortSuffix(string $scheme, int $port): string
    {
        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            return '';
        }

        return ":{$port}";
    }
}