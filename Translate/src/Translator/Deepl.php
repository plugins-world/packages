<?php

namespace Plugins\Translate\Translator;

use Plugins\Translate\Translator\Result\Translate;
use Plugins\Translate\Kernel\Contracts\TranslatorInterface;
use Plugins\Translate\Kernel\Exceptions\TranslateException;

/**
 * @see https://www.deepl.com/zh/your-account/keys
 * 
 * @see https://developers.deepl.com/docs/v/zh/api-reference/translate/openapi-spec-for-text-translation
 */
class Deepl implements TranslatorInterface
{
    use \Plugins\Translate\Kernel\Traits\InteractWithConfig;
    use \Plugins\Translate\Kernel\Traits\InteractWithHttpClient;

    const FREE_API_URL = 'https://api-free.deepl.com';
    const PRO_API_URL = 'https://api.deepl.com';

    public function getHttpClientDefaultOptions()
    {
        $http = $this->config['http'] ?? [];

        $options = array_merge(
            [
                'base_uri' => $http['base_uri'] ?? $this->getBaseUri(),
                'timeout' => 5, // 请求 5s 超时
                'http_errors' => false,
                'headers' => [
                    'Authorization' => "DeepL-Auth-Key {$this->getAppKey()}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
            $http
        );

        return $options;
    }

    public function getBaseUri()
    {
        $base_uri = Deepl::FREE_API_URL;
        if ($this->config['is_use_pro_api'] ?? false) {
            $base_uri = Deepl::PRO_API_URL;
        }

        return $base_uri;
    }

    public function getAppId()
    {
        if ($this->config['is_use_pro_api'] ?? false) {
            return $this->config['pro_app_id'];
        }

        return $this->config['free_app_id'];
    }

    public function getAppKey()
    {
        if ($this->config['is_use_pro_api'] ?? false) {
            return $this->config['pro_app_key'];
        }

        return $this->config['free_app_key'];
    }

    protected function getRequestParams($q, $from = 'zh', $to = 'en')
    {
        $params = [
            'text' => [
                $q,
            ],
            'from' => $from ?: 'zh',
            'target_lang' => $to ?: 'en',
        ];


        return $params;
    }

    /**
     * @param  string $q
     * @param  string $from
     * @param  string $to
     * 
     * @return Translate
     * 
     * @see https://developers.deepl.com/docs/v/zh/api-reference/translate/openapi-spec-for-text-translation
     */
    public function translate(string $q, $from = 'zh', $to = 'en'): mixed
    {
        $response = $this->getHttpClient()->request('POST', '/', [
            'json' => $this->getRequestParams($q, $from, $to),
        ]);

        dd($response->getBody()->getContents());
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
