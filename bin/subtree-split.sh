#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH="master"

function split()
{
    git subtree split -P $1 -b $2
    git push $2 "$2:refs/heads/$CURRENT_BRANCH" -f
    git remote remove $2 || true
    git branch -D $2 || true
}

function remote()
{
    git remote add $1 $2 || git remote set-url $1 $2 || true
}

git pull origin $CURRENT_BRANCH || true


# remote dcat-saas git@github.com:mouyong/dcat-saas.git

# split 'extensions/plugins/DcatSaas' dcat-saas
remote MarketManager git@github.com:plugins-world/MarketManager.git

remote LaravelOauth git@github.com:mouyong/laravel-oauth.git
remote LaravelConfig git@github.com:mouyong/laravel-config.git
remote LaravelDoc git@github.com:mouyong/laravel-doc.git
remote PhpSupport git@github.com:mouyong/php-support.git
remote Translate git@github.com:mouyong/translate.git
remote ECloudHousekeeper git@github.com:mouyong/ECloudHousekeeper.git

split 'MarketManager' MarketManager

split 'LaravelOauth' LaravelOauth
split 'LaravelConfig' LaravelConfig
split 'LaravelDoc' LaravelDoc
split 'PhpSupport' PhpSupport
split 'Translate' Translate
split 'ECloudHousekeeper' ECloudHousekeeper