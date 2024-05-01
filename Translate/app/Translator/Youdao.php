<?php

namespace Plugins\Translate\Translator;

use Plugins\Translate\Translator\Result\Translate;
use Plugins\Translate\Kernel\Contracts\TranslatorInterface;
use Plugins\Translate\Kernel\Exceptions\TranslateException;

/**
 * @see http://ai.youdao.com/docs/doc-trans-api.s#p02
 */
class Youdao implements TranslatorInterface
{
    use \Plugins\Translate\Kernel\Traits\InteractWithConfig;
    use \Plugins\Translate\Kernel\Traits\InteractWithHttpClient;

    const API_URL = 'https://openapi.youdao.com/api';

    public function getHttpClientDefaultOptions()
    {
        $http = $this->config['http'] ?? [];

        $options = array_merge(
            [
                'base_uri' => $http['base_uri'] ?? $this->getBaseUri(),
                'timeout' => 5, // 请求 5s 超时
                'http_errors' => false,
                'headers' => [
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
        return Youdao::API_URL;
    }

    public function getAppId()
    {
        return $this->config['app_id'] ?? null;
    }

    public function getAppKey()
    {
        return $this->config['app_key'] ?? null;
    }

    protected function getRequestParams($q, $from = 'zh-CHS', $to = 'EN')
    {
        $salt = uniqid();
        $curtime = time();

        $params = [
            'q' => $q,
            'from' => $from,
            'to' => $to,
            'appKey' => $this->getAppId(),
            'salt' => $salt,
            'signType' => $this->config['signType'] ?? 'v3',
            'curtime' => $curtime,
            'ext' => 'mp3',
            'voice' => $this->config['voice'] ?? 0,
            'strict' => $this->config['strict'] ?? 'false',
        ];

        if ($vocabId = $this->config['vocabId'] ?? null) {
            $params['vocabId'] = $vocabId;
        }

        $params['sign'] = $this->makeSignature($params);

        return $params;
    }

    protected function makeSignature(array $params)
    {
        if ($params['signType'] != 'v3') {
            $signStr = $this->getAppId() . $params['q'] . $params['salt'] . $this->getAppKey();

            return md5($signStr);
        }

        return $this->makeV3Signature($params);
    }

    protected function makeV3Signature(array $params)
    {
        $input = $q = $params['q'];

        $qLen = mb_strlen($q);
        if ($qLen > 20) {
            $input = mb_substr($q, 0, 10) . $qLen . mb_substr($q, -10);
        }

        $signStr = $this->getAppId() . $input . $params['salt'] . $params['curtime'] . $this->getAppKey();

        return hash('sha256', $signStr, false);
    }

    /**
     * {@inheritdoc}
     */
    public function translate($q, $from = 'zh-CHS', $to = 'EN'): mixed
    {
        $response = $this->getHttpClient()->request('POST', '', [
            'form_params' => $this->getRequestParams($q, $from, $to),
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        if (empty($result)) {
            throw new TranslateException("请求接口错误，未获取到翻译结果");
        }

        if ($result['errorCode'] != '0') {
            $errorCode = $result['errorCode'];
            $errorCodeReasonHref = 'https://ai.youdao.com/DOCSIRMA/html/自然语言翻译/API文档/文本翻译服务/文本翻译服务-API文档.html#p02';

            throw new TranslateException(sprintf(
                "请求接口错误，错误码：%s，查看错误原因：%s",
                $errorCode,
                $errorCodeReasonHref
            ), $errorCode);
        }

        return new Translate($this->mapTranslateResult($result));
    }

    public function mapTranslateResult(array $translateResult): array
    {
        return [
            'src' => $translateResult['query'],
            'dst' => current($translateResult['translation']),
            'original' => $translateResult,
        ];
    }
}
