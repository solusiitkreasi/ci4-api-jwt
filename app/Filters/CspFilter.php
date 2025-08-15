<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CSPFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Not needed for before
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (config('App')->CSPEnabled) {
            $csp = config('ContentSecurityPolicy');
            
            $directives = [];
            
            if ($csp->defaultSrc) {
                $directives[] = "default-src " . $this->buildSrcList($csp->defaultSrc);
            }
            
            if ($csp->scriptSrc) {
                $directives[] = "script-src " . $this->buildSrcList($csp->scriptSrc);
            }
            
            if ($csp->styleSrc) {
                $directives[] = "style-src " . $this->buildSrcList($csp->styleSrc);
            }
            
            if ($csp->imageSrc) {
                $directives[] = "img-src " . $this->buildSrcList($csp->imageSrc);
            }
            
            if ($csp->fontSrc) {
                $directives[] = "font-src " . $this->buildSrcList($csp->fontSrc);
            }
            
            if ($csp->connectSrc) {
                $directives[] = "connect-src " . $this->buildSrcList($csp->connectSrc);
            }
            
            if (!empty($directives)) {
                $cspHeader = implode('; ', $directives);
                $response->setHeader('Content-Security-Policy', $cspHeader);
            }
        }
        
        return $response;
    }
    
    private function buildSrcList($sources)
    {
        if (is_string($sources)) {
            return $sources === 'self' ? "'self'" : $sources;
        }
        
        if (is_array($sources)) {
            $formatted = [];
            foreach ($sources as $source) {
                if ($source === 'self') {
                    $formatted[] = "'self'";
                } elseif (in_array($source, ['unsafe-inline', 'unsafe-eval'])) {
                    $formatted[] = "'{$source}'";
                } else {
                    $formatted[] = $source;
                }
            }
            return implode(' ', $formatted);
        }
        
        return "'self'";
    }
}