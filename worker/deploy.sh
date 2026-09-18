#!/usr/bin/env sh
set -e

# Script deployment Cloudflare Worker QuranWeb Sync
# Memastikan KV Namespace terdaftar & Worker ter-deploy secara repeatable.

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "=========================================="
echo " QuranWeb Sync - Cloudflare Worker Deploy "
echo "=========================================="

# 1. Cek ketersediaan npx / node
if ! command -v npx >/dev/null 2>&1; then
    echo "Error: 'npx' tidak ditemukan. Pastikan Node.js sudah terinstall."
    exit 1
fi

# 2. Cek apakah KV Namespace ID di wrangler.toml sudah berupa 32-karakter heksadesimal
VALID_KV_ID=$(python3 -c "
import re
try:
    with open('wrangler.toml', 'r') as f:
        content = f.read()
    m = re.search(r'id\s*=\s*\"([a-f0-9]{32})\"', content)
    print(m.group(1) if m else '')
except Exception:
    print('')
")

if [ -z "$VALID_KV_ID" ]; then
    echo ""
    echo "[1/3] Menyiapkan Cloudflare KV Namespace..."

    # Cek apakah namespace QURAN_SYNC_KV sudah ada di akun Cloudflare
    EXISTING_ID=$(python3 -c "
import subprocess, json
try:
    output = subprocess.check_output(['npx', 'wrangler', 'kv', 'namespace', 'list'], stderr=subprocess.DEVNULL).decode('utf-8')
    namespaces = json.loads(output)
    for ns in namespaces:
        if 'QURAN_SYNC_KV' in ns.get('title', ''):
            print(ns.get('id', ''))
            break
except Exception:
    print('')
")

    if [ -n "$EXISTING_ID" ]; then
        echo "KV namespace ditemukan di akun: $EXISTING_ID"
        KV_ID="$EXISTING_ID"
    else
        echo "Membuat KV namespace baru 'QURAN_SYNC_KV'..."
        CREATE_OUTPUT=$(npx wrangler kv namespace create QURAN_SYNC_KV)
        echo "$CREATE_OUTPUT"
        KV_ID=$(python3 -c "
import re
text = '''$CREATE_OUTPUT'''
m = re.search(r'id = \"([a-f0-9]{32})\"', text)
print(m.group(1) if m else '')
")
    fi

    if [ -z "$KV_ID" ]; then
        echo "Gagal mendeteksi KV namespace ID. Silakan cek koneksi atau login ke Cloudflare."
        exit 1
    fi

    # Update wrangler.toml dengan KV ID yang valid
    python3 -c "
import re
with open('wrangler.toml', 'r') as f:
    content = f.read()
new_content = re.sub(r'id\s*=\s*\"[^\"]*\"', f'id = \"$KV_ID\"', content)
with open('wrangler.toml', 'w') as f:
    f.write(new_content)
"
    echo "wrangler.toml berhasil diperbarui dengan KV ID: $KV_ID"
else
    echo "[1/3] Menggunakan KV Namespace ID dari wrangler.toml: $VALID_KV_ID"
fi

# 3. Deploy Worker
echo ""
echo "[2/3] Memulai deployment worker..."
npx wrangler deploy

echo ""
echo "[3/3] Deployment selesai!"
echo "=========================================="
echo "Worker siap digunakan untuk QuranWeb Sync."
