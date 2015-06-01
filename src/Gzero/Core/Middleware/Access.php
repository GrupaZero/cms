<?php namespace Gzero\Core\Middleware;

use Closure;

/**
 * This file is part of the GZERO Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Access
 *
 * @package    Gzero\CORE
 * @author     Mateusz Urbanowicz <urbanowiczmateusz89@gmail.com>
 * @copyright  Copyright (c) 2015, Mateusz Urbanowicz
 */
class Access {

    /**
     * Return 404 if user is not authenticated or got no admin rights
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth = app()->make('auth');
        if (!$auth->check() || !$auth->user()->isAdmin) {
            abort(404, trans('HTTP.NOT_FOUND'));
        }
        return $next($request);
    }

}