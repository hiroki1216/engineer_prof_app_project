#!/bin/bash

# 環境変数を読み込む
source ./.env

# ディレクトリが存在するか確認し、存在しない場合のみ作成
if [ ! -d "${DB_VOLUME}" ]; then
  mkdir -p "${DB_VOLUME}"
  # ディレクトリのパーミッションを変更
  sudo chmod 777 ${DB_VOLUME}
fi

# Docker Compose を実行
docker-compose up