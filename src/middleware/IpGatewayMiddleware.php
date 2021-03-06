<?php

namespace LaravelIpGateway\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

/**
 * Class IpGatewayMiddleware
 *
 * @package LaravelIpGateway\Middleware
 */
class IpGatewayMiddleware
{
    protected $ipList;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $prohibitRequest = false;

        if (config('ip-gateway')) {
            if (config('ip-gateway.enable_package') === true) {

                if (config('ip-gateway.enable_blacklist') === true) {
                    foreach ($request->getClientIps() as $ip) {
                        if ($this->grantIpAddress($ip)) {
                            $prohibitRequest = true;
                            Log::warning($ip . ' IP address has tried to access.');
                        }
                    }
                }

                if (config('ip-gateway.enable_blacklist') === false) {
                    foreach ($request->getClientIps() as $ip) {
                        if (!$this->grantIpAddress($ip)) {
                            $prohibitRequest = true;
                            Log::warning($ip . ' IP address has tried to access.');
                        }
                    }
                }
            }
        }

        if ($prohibitRequest === false) {
            return $next($request);
        } else {
            if (config('ip-gateway.redirect_route_to') != '') {
                return redirect(config('ip-gateway.redirect_route_to'));
            } else {
                return redirect('/404');
            }
        }
    }

    /**
     * Grant IP address
     *
     * @param $ip
     *
     * @return bool
     */
    protected function grantIpAddress($ip)
    {
        $this->ipList = config('ip-gateway.ip-list');
        return in_array($ip, $this->ipList);
    }
}
