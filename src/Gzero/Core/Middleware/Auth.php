<?php namespace Gzero\Core\Middleware;

use Closure;

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
     * Redirect user to login page if not authenticated
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth = app()->make('auth');
        if (!$auth->check()) {
            return redirect()->to(config('gzero.loginRedirect', route('login')));
        }
        return $next($request);
    }

}
