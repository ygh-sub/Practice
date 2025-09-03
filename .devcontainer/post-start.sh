#!/usr/bin/env bash
set -euo pipefail

echo "🚀 Running post-start setup..."

echo "🔧 Upgrading mise packages..."
mise upgrade
echo "✅ Mise packages upgraded!"

echo "✅ Post-start setup completed!"
