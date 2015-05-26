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
     * Return 404 if user is not authenticated or got no admin rights
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
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
