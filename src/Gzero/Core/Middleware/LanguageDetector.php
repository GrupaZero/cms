<?php namespace Gzero\Core\Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\RedirectResponse;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class LanguageDetector
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class LanguageDetector {

    /**
     * List of allowed prefixes that does not need multilingual detection
     * @var array
     */
    protected $allowedPrefixes = ['admin', '_debugbar', '_hidden'];

    /**
     * Config.
     *
     * @var Repository
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Repository $config Laravel config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

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
        if (!preg_match('/^api/', $request->getHost()) && !in_array($request->segment(1), $this->allowedPrefixes, true)) {
            if ($this->config->get('gzero.multilang.enabled') && !$this->config->get('gzero.multilang.detected')) {
                return new RedirectResponse(route('home'));
            }
        }
        return $next($request);
    }

}
