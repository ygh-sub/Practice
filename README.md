# Devcontainer Boilerplate

Claude Code 等コーディングエージェントが環境を破壊しても問題ないように、Devcontainer を使って開発環境を構築するためのボイラープレートです。

Zsh や rg, fd などモダンな開発環境ツールは一通りインストールされています。

言語ランタイムについては、post-create.sh 内の`mise use --global`を拡張するかたちで適宜追加してください。
uv, Node.js, Bun は Claude Code や ccusage のために導入済みです。
Python 3 ランタイムについては uv があるため意図的にボイラープレートではインストールしていません。
必要に応じて追加してください。

vimrc は好みで変更してください。
