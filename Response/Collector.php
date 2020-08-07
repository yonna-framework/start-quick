<?php
/**
 * response handle collector
 */

namespace Yonna\Response;

use Throwable;
use Yonna\Foundation\Convert;
use Yonna\Response\Consequent;

/**
 * Class Collector
 * @package Yonna\IO
 */
class Collector
{

    private $response_data_type = 'json';
    private $charset = '';
    private $code = 0;
    private $msg = '';
    private $data = [];

    public function __construct()
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseDataType()
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
        if ($this->code !== Code::THROWABLE && class_exists("\\Yonna\\I18n\\I18n")) {
            $i18n = new \Yonna\I18n\I18n();
            $i18n->set($msg);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return Collector
     */
    public function setData($data): self
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
            case 'file':
                $response = $this->getData()->getRaw();
                break;
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
     * @return mixed
     */
    public function getHeader()
    {
        $header = [];
        if ($this->getCharset()) {
            $header['Content-Type'] = $this->getCharset();
        }
        switch ($this->getResponseDataType()) {
            case 'file':
                $header['Accept-Ranges'] = 'bytes';
                $header['Content-Transfer-Encoding'] = 'binary';
                $header['Cache-Control'] = 'no-cache,no-store,max-age=0,must-revalidate';
                $header['Pragma'] = 'no-cache';
                $header['Content-Disposition'] = 'attachment;filename=' . $this->getData()->getName();
                $header['Accept-Length'] = strlen($this->getData()->getRaw());
                $header['Content-Type'] = $this->getData()->getContentType();
                break;
            case 'xml':
                $header['Content-Type'] = 'application/xml';
                break;
            case 'json':
                $header['Content-Type'] = 'application/json';
                break;
            case 'html':
                $header['Content-Type'] = 'text/html';
                break;
            default:
                $header['Content-Type'] = 'text/plain';
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
            foreach ($this->getHeader() as $k => $v) {
                header($k . ':' . $v);
            }
        } catch (Throwable $e) {
            exit('Yonna cannot modify header information - headers already sent by');
        }
        exit($this->response());
    }

}