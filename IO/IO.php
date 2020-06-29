<?php
/**
 * input / output
 */

namespace Yonna\IO;

use Closure;
use Yonna\Foundation\Arr;
use Yonna\Foundation\Str;
use Yonna\Response\Collector;
use Yonna\Response\Response;
use Yonna\Scope\Config;
use Yonna\Throwable\Exception;

class IO
{

    public function __construct()
    {
        return $this;
    }

    /**
     * @param array $scopes
     * @param Request $request
     * @param array $upperData
     * @return mixed
     * @throws Exception\ThrowException
     */
    private function scopes(array $scopes, Request $request, $upperData = [])
    {
        $responses = [];
        foreach ($scopes as $sk => $sv) {
            $sk = Str::upper($sk);
            $sc = Arr::get(Config::fetch(), "{$request->getRequestMethod()}.{$sk}");
            if (!$sc) {
                Exception::throw('no scope isset');
            }
            if ($sc['call'] instanceof Closure) {
                // 判断 upperData
                if ($upperData) {
                    foreach ($sv as $svk => $svv) {
                        if (is_string($svv)) {
                            $opt = substr($svv, 0, 3);
                            $field = str_replace($opt, '', $svv);
                            switch ($opt) {
                                case 'eq:':
                                    $sv[$svk] = $upperData[$field] ?? null;
                                    break;
                                case 'in:':
                                    $sv[$svk] = array_column($upperData, $field);
                                    break;
                            }
                        }
                    }
                }
                $request->setInput($sv);
                if ($sc['before']) {
                    foreach ($sc['before'] as $before) {
                        $request = $before($request);
                    }
                }
                $response = $sc['call']($request);
                foreach ($sv as $vKey => $vVal) {
                    $data = isset($response['list']) ? $response['list'] : $response;
                    if ($vKey === '*') {
                        foreach ($data as &$l) {
                            $l['_'] = $this->scopes($vVal, $request, $l)[0];
                        }
                        if (isset($response['list'])) {
                            $response['list'] = $data;
                        } else {
                            $response = $data;
                        }
                    } elseif ($vKey === '+') {
                        $response['_'] = $this->scopes($vVal, $request, $data)[0];
                    }
                }
                if ($sc['after']) {
                    foreach ($sc['after'] as $after) {
                        $response = $after($request, $response);
                    }
                }
                $responses[$sk] = $response;
            }
        }
        $count = count($responses);
        return [$count === 1 ? current($responses) : $responses, $count];
    }

    /**
     * @param Request $request
     * @return Collector
     */
    public function response(Request $request)
    {
        $scopes = $request->getScopes();
        if (!$scopes) {
            return Response::abort('no scope');
        }
        $responses = null;
        try {
            $responses = $this->scopes($scopes, $request);
        } catch (Exception\ThrowException $e) {
            return Response::abort($e->getMessage());
        }
        // response
        $response = $responses[0];
        if ($responses[1] === 1) {
            if ($response === null) {
                $response = Response::notFound('You should return some response but not null');
            } else if (is_array($response)) {
                $response = Response::success('fetch array success', $response);
            } else if (is_string($response)) {
                $response = Response::success('fetch string success', ['string' => $response]);
            } else if (is_numeric($response)) {
                $response = Response::success('fetch number success', ['number' => $response]);
            } else if (is_bool($response)) {
                $response = $response === true ? Response::success('fetch boolean success', ['bool' => $response]) : Response::error('error');
            }
        } else {
            $response = Response::success('fetch multi success', $response);
        }
        if (!($response instanceof Collector)) {
            $response = Response::notFound('Response must instanceof ResponseCollector');
        }
        return Crypto::output($request, $response);
    }

}