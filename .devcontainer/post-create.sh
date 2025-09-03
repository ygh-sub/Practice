#!/usr/bin/env bash
set -euo pipefail
# vim:set foldmarker=[[[,]]] :

### 共有関数定義 ### [[[
function append_if_missing() {
    # ファイルへの重複排除付き追記関数
    # 使用法: append_if_missing [-l] [-t] "検索文字列" "ファイルパス"
    local leading_newline='' trailing_newline='' OPTIND
    while getopts "lt" opt; do
        case $opt in
            l) leading_newline=1 ;;
            t) trailing_newline=1 ;;
            *) ;;
        esac
    done
    shift $((OPTIND-1))

    local search_string="$1"
    local file="$2"
    [ ! -f "$file" ] && return

    if ! grep -q "$search_string" "$file" 2>/dev/null; then
        [ "$leading_newline" ] && echo >> "$file"
        cat >> "$file"
        [ "$trailing_newline" ] && echo >> "$file"
    fi
    return 0
}
# ]]]

echo "🚀 Running post-create setup..."

### Vim ### [[[
# 7日以上古い場合のみapt-get updateを実行してtmuxをインストール
if ! command -v vim &> /dev/null; then
    if [ -z "$(find /var/lib/apt/lists -maxdepth 1 -type f -mtime -7 2>/dev/null)" ]; then
        sudo apt-get update
    fi
    sudo apt-get install --yes vim
fi

if [ ! -f "$HOME/.vimrc" ]; then
    cat .devcontainer/boilerplate/vimrc > "$HOME/.vimrc"
    echo "📝 Created .vimrc file."
fi
# ]]]

### Starship ### [[[
append_if_missing -l "starship init zsh" "$HOME/.zshrc" <<'EOF'
# Starship configuration
eval "$(starship init zsh)"
EOF
# ]]]

### fzf ### [[[
if ! grep -q "fzf" "$HOME/.zshrc"; then
    echo "# fzf configuration" >> "$HOME/.zshrc"
    fzf --zsh >> "$HOME/.zshrc"
fi
# ]]]

### bat ### [[[
if command -v batcat &> /dev/null && ! command -v bat &> /dev/null; then
    append_if_missing -l "alias bat" "$HOME/.zshrc" <<'EOF'
# bat configuration
alias bat="batcat"
EOF
fi
# ]]]

### mise ### [[[
# 現在のスクリプト用にPATHを設定
if [ ! -d "$HOME/.local/bin" ]; then
    mkdir -p "$HOME/.local/bin"
fi
if [[ ":$PATH:" != *":$HOME/.local/bin:"* ]]; then
    export PATH="$HOME/.local/bin:$PATH"
fi

append_if_missing -l "mise activate" "$HOME/.zshrc" <<'EOF'
# mise configuration
if [[ ":$PATH:" != *":$HOME/.local/bin:"* ]]; then
    export PATH="$HOME/.local/bin:$PATH"
fi
eval "$(mise activate zsh)"
EOF

echo "⚙️ Configuring mise settings..."
eval "$(mise activate bash)"

mise settings set npm.bun true
mise settings set pipx.uvx true

echo "🔧 Installing global languages with mise..."
mise use --global \
    uv@latest \
    node@latest \
    bun@latest \

echo "🔧 Installing global tools with mise..."
mise use --global \
    npm:@anthropic-ai/claude-code \
    npm:ccusage \
    sd \
    eza

# プロジェクト固有のmise設定
if [ -f ".mise.toml" ] || [ -f ".tool-versions" ]; then
    echo "📚 Installing project-specific tools with mise..."
    mise install
fi

mise list
# ]]]

### Claude Code ### [[[
# @https://github.com/anthropics/claude-code/blob/main/CHANGELOG.md#106
echo "🔧 Setting up Claude Code configuration..."
if [ ! -d "$HOME/.config/claude" ]; then
    mkdir -p "$HOME/.config/claude"
fi

append_if_missing -l "CLAUDE_CONFIG_DIR" "$HOME/.zshrc" <<'EOF'
# Claude Code configuration
if [[ ":$PATH:" != *":$HOME/.config/claude:"* ]]; then
    export CLAUDE_CONFIG_DIR="$HOME/.config/claude"
fi
EOF
# ]]]

echo "✅ Post-create setup completed!"
