<?php namespace Gzero\Core\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Init
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class Init {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (str_contains($request->getRequestUri(), 'index.php')) {
            return new RedirectResponse(url(preg_replace('#index.php(/)?#', '', $request->fullUrl())), 301);
        }
        $this->toSnakeCase($request);
        return $next($request);
    }


    /**
     * It changes all parameters to snake_case
     *
     * @param \Illuminate\Http\Request $request Request object
     *
     * @return void
     */
    private function toSnakeCase($request)
    {
        $params = [];
        foreach ($request->all() as $key => $value) {
            $params[snake_case($key)] = $value;
        }

        $request->replace($params);
    }

}
