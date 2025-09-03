## 練習用リポジトリ

新しい技術スタックや試したい内容がある場合はこのリポジトリでブランチを作成して行う
似たようなリポジトリが乱立することを防ぐため

亀井さん作成の divcontainer をベースにしているため ClaudeCode も使用可能、詳細は以下参照

## Devcontainer Boilerplate

Claude Code 等コーディングエージェントが環境を破壊しても問題ないように、Devcontainer を使って開発環境を構築するためのボイラープレートです。

Zsh や rg, fd などモダンな開発環境ツールは一通りインストールされています。

言語ランタイムについては、post-create.sh 内の`mise use --global`を拡張するかたちで適宜追加してください。
uv, Node.js, Bun は Claude Code や ccusage のために導入済みです。
Python 3 ランタイムについては uv があるため意図的にボイラープレートではインストールしていません。
必要に応じて追加してください。

vimrc は好みで変更してください。
