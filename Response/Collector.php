<?php
/**
 * response handle collector
 */

namespace Yonna\Response;

use Throwable;
use Yonna\Foundation\Convert;

/**
 * Class Collector
 * @package Yonna\IO
 */
class Collector
{

    private $response_data_type = 'json';
    private $charset = 'utf-8';
    private $code = 0;
    private $msg = '';
    private $data = array();

    public function __construct()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseDataType(): string
    {
        return $this->response_data_type;
    }

    /**
     * @param string $response_data_type
     * @return Collector
     */
    public function setResponseDataType(string $response_data_type): self
    {
        $this->response_data_type = $response_data_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return Collector
     */
    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return Collector
     */
    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     * @return Collector
     */
    public function setMsg(string $msg): self
    {
        $this->msg = $msg;
        if (class_exists("\\Yonna\\I18n\\I18n")) {
            $i18n = new \Yonna\I18n\I18n();
            $i18n->set($msg, [
                'source' => 'response'
            ]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Collector
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * to array
     * @return array
     */
    public function toArray()
    {
        $data = $this->getData();
        return array(
            'code' => $this->getCode(),
            'msg' => $this->getMsg(),
            'data' => $data,
        );
    }

    /**
     * to JSON
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * to JSON
     * @return false|string
     */
    public function toXml()
    {
        return xmlrpc_encode(Convert::obj2String($this->toArray()));
    }

    /**
     * to Text
     * @return false|string
     */
    public function toHtml()
    {
        return Convert::arr2html(Convert::obj2String($this->toArray()));
    }

    /**
     * to Text
     * @return false|string
     */
    public function toText()
    {
        return var_export($this->toArray(), true);
    }

    /**
     * to JSON
     * @return false|string
     */
    public function response()
    {
        $response = null;
        switch ($this->getResponseDataType()) {
            case 'xml':
                $response = $this->toXml();
                break;
            case 'json':
                $response = $this->toJson();
                break;
            case 'html':
                $response = $this->toHtml();
                break;
            case 'text':
            default:
                $response = $this->toText();
                break;
        }
        return $response;
    }

    /**
     * @param string $format
     * @return mixed
     */
    public function getHeader($format = 'str')
    {
        switch ($this->getResponseDataType()) {
            case 'xml':
                $ContentType = 'application/xml';
                break;
            case 'json':
                $ContentType = 'application/json';
                break;
            case 'html':
                $ContentType = 'text/html';
                break;
            default:
                $ContentType = 'text/plain';
                break;
        }
        switch ($format) {
            case 'arr':
            case 'array':
                $header = [
                    'Content-Type' => $ContentType,
                    'Charset' => $this->getCharset()
                ];
                break;
            case 'str':
            case 'string':
            case 'text':
            default:
                $header = 'Content-Type:' . $ContentType;
                $header .= ';Charset=' . $this->getCharset();
                break;
        }
        return $header;
    }

    /**
     * @return string
     */
    public function end()
    {
        try {
            header($this->getHeader('str'));
        } catch (Throwable $e) {
            exit('Yonna cannot modify header information - headers already sent by');
        }
        exit($this->response());
    }

}