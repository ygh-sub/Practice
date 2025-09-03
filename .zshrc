export PATH="/usr/local/mysql/bin:$PATH"

# bun completions
[ -s "/Users/tomohiro/.bun/_bun" ] && source "/Users/tomohiro/.bun/_bun"

# bun
export BUN_INSTALL="$HOME/.bun"
export PATH="$BUN_INSTALL/bin:$PATH"

alias cc='claude --dangerously-skip-permissions'


