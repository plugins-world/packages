<?php

namespace Plugins\Translate\Translator\Google;

use Plugins\Translate\Translator\Result\Translate;
use Plugins\Translate\Kernel\Contracts\TranslatorInterface;
use Plugins\Translate\Kernel\Exceptions\TranslateException;

class Google implements TranslatorInterface
{
    use \Plugins\Translate\Kernel\Traits\InteractWithConfig;

    /**
     * @param string $from
     * @param string $to
     *
     * @return \Stichoza\GoogleTranslate\GoogleTranslate
     *
     * @throws \Exception
     */
    public function getTranslateClient($from, $to)
    {
        $translateClient = new GoogleTranslateClient($this->config);

        return $translateClient
            ->resetOptions()
            ->setSource($from)
            ->setTarget($to);
    }

    public function translate(string $q, $from = 'zh-CN', $to = 'en'): mixed
    {
        $translateClient = $this->getTranslateClient($from, $to);

        try {
            $result = $translateClient->translate($q);
        } catch (\Throwable $e) {
            throw new TranslateException("请求接口错误，错误信息：{$e->getMessage()}", $e->getCode());
        }

        $rawResponse = $translateClient->getResponse($q);

        return new Translate($this->mapTranslateResult([
            'src' => $q,
            'dst' => $result,
            'original' => $rawResponse,
        ]));
    }

    public function mapTranslateResult(array $translateResult): array
    {
        return [
            'src' => $translateResult['src'],
            'dst' => $translateResult['dst'],
            'original' => $translateResult['original'],
        ];
    }
}