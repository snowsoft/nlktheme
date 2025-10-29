<?php

namespace Nlk\Theme\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nlk\Theme\Security\SecurityHelper;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $cspPolicy
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $cspPolicy = null)
    {
        $response = $next($request);

        // Add security headers
        if (config('theme.security.headers', true)) {
            $this->addSecurityHeaders($response, $cspPolicy);
        }

        // Validate view paths if theme is being used
        if ($request->route() && method_exists($response, 'getOriginalContent')) {
            $content = $response->getOriginalContent();
            if ($content instanceof \Illuminate\View\View) {
                $viewPath = $content->getName();
                if (!SecurityHelper::isValidViewPath($viewPath)) {
                    abort(403, 'Invalid view path detected.');
                }
            }
        }

        return $response;
    }

    /**
     * Add security headers to response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  string|null  $cspPolicy
     * @return void
     */
    protected function addSecurityHeaders($response, $cspPolicy = null)
    {
        // Content Security Policy
        if (config('theme.security.csp.enabled', false)) {
            $csp = $cspPolicy ?: SecurityHelper::generateCSP(
                config('theme.security.csp.directives', [])
            );
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // X-Content-Type-Options
        if (config('theme.security.headers_config.x_content_type', true)) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        // X-Frame-Options
        if (config('theme.security.headers_config.x_frame_options', true)) {
            $frameOptions = config('theme.security.headers_config.x_frame_options_value', 'DENY');
            $response->headers->set('X-Frame-Options', $frameOptions);
        }

        // X-XSS-Protection
        if (config('theme.security.headers_config.x_xss_protection', true)) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        // Referrer Policy
        if (config('theme.security.headers_config.referrer_policy', true)) {
            $referrerPolicy = config('theme.security.headers_config.referrer_policy_value', 'strict-origin-when-cross-origin');
            $response->headers->set('Referrer-Policy', $referrerPolicy);
        }

        // Permissions Policy
        if (config('theme.security.headers_config.permissions_policy', false)) {
            $permissionsPolicy = config('theme.security.headers_config.permissions_policy_value', '');
            if ($permissionsPolicy) {
                $response->headers->set('Permissions-Policy', $permissionsPolicy);
            }
        }
    }
}

