# monorepo-split

## 添加 monorepo-split

```bash
git subtree add -P bin/ https://ghproxy.com/https://github.com/mouyong/monorepo-split.git master
```

## 使用说明

### 1. 分离子目录到仓库

更新 `subtree-split.sh`的变量：`CURRENT_BRANCH`

**注：使用 subtree 方式进行分离，见：https://github.com/mouyong/monorepo-split/blob/master/.github/workflows/split.yml#L41**

```bash
# 当前分支
CURRENT_BRANCH="master"

# split.sh 子目录仓库
remote dcat-saas git@github.com:mouyong/dcat-saas.git

# split.sh 分离子目录到分支
split 'extensions/plugins/DcatSaas' dcat-saas
```

执行分离: `bash ./bin/subtree-split.sh`


### 2. 发布 tag

更新 `release.sh`的变量：`RELEASE_BRANCH`
```bash
# release.sh 从 RELEASE_BRANCH 发布 tag
release dcat-saas git@github.com:mouyong/dcat-saas.git
```

执行发布: `bash ./bin/release.sh v0.0.1`

