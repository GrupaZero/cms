<?php namespace Gzero\Core\Middleware;

use Closure;
use Gzero\Api\AccessForbiddenException;

/**
 * This file is part of the GZERO Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Auth
 *
 * @package    Gzero\CORE
 * @author     Mateusz Urbanowicz <urbanowiczmateusz89@gmail.com>
 * @copyright  Copyright (c) 2015, Mateusz Urbanowicz
 */
class Auth {

    /**
     * If ajax request throw exception, otherwise redirect to login page
     *
     * @param         $request
     * @param Closure $next
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws AccessForbiddenException
     */
    function handle($request, Closure $next)
    {
        $auth = app()->make('auth');
        if (!$auth->check()) {
            if ($request->ajax()) {
                throw new AccessForbiddenException('Forbidden.', 403);
            }
            return redirect()->to(config('gzero.loginRedirect', route('login')));
        }
        return $next($request);
    }

}
