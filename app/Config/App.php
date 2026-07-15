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
     * Static — this app is only ever served through the Cloudflare Tunnel
     * at bms.rps-home-lab.com, so there's no need to detect it dynamically.
     *
     * IMPORTANT: Make sure 'app.baseURL' is COMMENTED OUT (or absent) in your
     * .env file, otherwise it will override this value.
     */
    public string $baseURL = 'https://bms.rps-home-lab.com/';

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

    /**
     * Since we're always behind Cloudflare Tunnel (https), force it.
     */
    public bool $forceGlobalSecureRequests = true;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * Trust the Cloudflare Tunnel connection (usually 127.0.0.1 / localhost,
     * since cloudflared connects to your app locally) so CodeIgniter reads
     * X-Forwarded-* headers correctly for things like IP address and scheme.
     */
    public array $proxyIPs = [
        '127.0.0.1'     => 'X-Forwarded-For',
        '172.16.0.0/12' => 'X-Forwarded-For', // covers default Docker bridge/user-defined networks
    ];

    public bool $CSPEnabled = false;
}