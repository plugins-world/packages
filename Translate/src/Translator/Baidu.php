<?php

namespace Plugins\Translate\Translator;

use Plugins\Translate\Translator\Result\Translate;
use Plugins\Translate\Kernel\Contracts\TranslatorInterface;
use Plugins\Translate\Kernel\Exceptions\TranslateException;

/**
 * @see http://api.fanyi.baidu.com/manage/developer
 * 
 * @see http://api.fanyi.baidu.com/api/trans/product/apidoc
 */
class Baidu implements TranslatorInterface
{
    use \Plugins\Translate\Kernel\Traits\InteractWithConfig;
    use \Plugins\Translate\Kernel\Traits\InteractWithHttpClient;

    const HTTP_URL = 'http://api.fanyi.baidu.com/api/trans/vip/translate';

    const HTTPS_URL = 'https://fanyi-api.baidu.com/api/trans/vip/translate';

    public function getHttpClientDefaultOptions()
    {
        return array_merge(
            [
                'base_uri' => Baidu::HTTPS_URL,
            ],
            (array) ($this->config['http'] ?? []),
        );
    }

    public function getAppId()
    {
        return $this->config['app_id'] ?? null;
    }

    public function getAppKey()
    {
        return $this->config['app_key'] ?? null;
    }

    protected function getRequestParams($q, $from = 'zh', $to = 'en')
    {
        $salt = time();

        $params = [
            'q' => $q,
            'from' => $from ?: 'zh',
            'to' => $to ?: 'en',
            'appid' => $this->getAppId(),
            'salt' => $salt,
            'tts' => $this->config['tts'] ?? 1,
            'dict' => $this->config['dict'] ?? 1,
            'action' => $this->config['action'] ?? 0,
        ];


        $params['sign'] = $this->makeSignature($params);

        return $params;
    }

    protected function makeSignature(array $params)
    {
        return md5($this->getAppId().$params['q'].$params['salt'].$this->getAppKey());
    }

    /**
     * @param  string $q
     * @param  string $from
     * @param  string $to
     * 
     * @return Translate
     * 
     * @see https://fanyi-api.baidu.com/api/trans/vip/translate
     */
    public function translate(string $q, $from = 'zh', $to = 'en'): mixed
    {
        $response = $this->getHttpClient()->request('POST', '', [
            'form_params' => $this->getRequestParams($q, $from, $to),
        ]);

        $result = $response->toArray();

        if (!empty($result['error_code'])) {
            throw new TranslateException("请求接口错误，错误信息：{$result['error_msg']}", $result['error_code']);
        }

        return new Translate($this->mapTranslateResult($result));
    }

    public function mapTranslateResult(array $translateResult): array
    {
        return [
            'src' => reset($translateResult['trans_result'])['src'],
            'dst' => reset($translateResult['trans_result'])['dst'],
            'original' => $translateResult,
        ];
    }
}
