<?php

namespace Plugins\Translate\LanguageRecognizer;

use Plugins\Translate\Exceptions\LanguageDetectException;

/**
 * @class LanguageRecognizer
 * 
 * @see https://translatedlabs.com/语言识别器
 * 
 * @date 2022-06-18
 */
class LanguageRecognizerClient
{
    use \Plugins\Translate\Kernel\Traits\InteractWithHttpClient;

    const API_URL = 'https://api.translatedlabs.com/language-identifier/identify';

    public function detect(?string $content)
    {
        if (!$content) {
            return null;
        }

        $body = json_decode(sprintf('{
            "etnologue": true,
            "uiLanguage": "zh",
            "text": "%s"
        }', $content), true);


        $response = $this->getHttpClient()->request('POST', static::API_URL, [
            'json' => $body,
        ]);

        $result = $response->toArray();

        if ($this->isErrorResponse($result)) {
            $this->handleErrorResponse($result);   
        }

        return new LanguageRecognizer([
            'detectContent' => $content,
        ] + $result);
    }

    public function isErrorResponse(array $data): bool
    {
        return !empty($data['error']);
    }

    public function handleErrorResponse(array $data = [])
    {
        throw new LanguageDetectException("请求接口错误，错误信息：{$data['error']}");
    }
}