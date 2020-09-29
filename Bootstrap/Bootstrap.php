<?php
/**
 * Bootstrap core
 */

namespace Yonna\Bootstrap;

use Throwable;
use Yonna\Core;
use Yonna\IO\RequestBuilder;
use Yonna\Log\Log;
use Yonna\IO\IO;
use Yonna\IO\Request;
use Yonna\Response\Collector;
use Yonna\Response\Response;

class Bootstrap
{

    public function __construct()
    {
        return $this;
    }

    /**
     * Bootstrap constructor.
     * @param $root
     * @param null $env_name
     * @param null $boot_type
     * @param RequestBuilder|null $builder
     * @return Collector
     * @throws null
     */
    public function boot($root, $env_name, $boot_type, ?RequestBuilder $builder = null)
    {
        /**
         * clear env name
         */
        $env_name = str_replace('.env.', '', $env_name);
        /**
         * @var $Cargo Cargo
         */
        $Cargo = Core::get(Cargo::class, [
            'root' => $root,
            'env_name' => $env_name ?? 'example',
            'boot_type' => $boot_type ?? BootType::AJAX_HTTP,
        ]);

        try {

            /**
             * Cargo
             */

            // 环境
            $Cargo = Env::install($Cargo);
            // 自定义配置，自动加载目录
            $Cargo = Config::install($Cargo);

            /**
             * @var Request $request
             */
            $request = Core::get(Request::class, $Cargo, $builder);

            /**
             * @var IO $io
             */
            $io = Core::singleton(IO::class);
            $collector = $io->response($request);
            Log::file()->info(['msg' => "hello world"], 'test');

        } catch (Throwable $e) {
            // log
            Log::file()->throwable($e);
            $collector = Response::throwable($e);
        }
        return $collector;
    }

}