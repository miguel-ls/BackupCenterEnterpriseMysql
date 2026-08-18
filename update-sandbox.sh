#!/bin/bash

echo "======================================"
echo " BackupCenter Sandbox Update"
echo "======================================"

cd /config/workspace || exit 1

echo ""
echo "[1/5] Actualizando Git..."
git pull

echo ""
echo "[2/5] Instalando dependencias PHP..."
composer install

echo ""
echo "[3/5] Instalando dependencias Dashboard..."
cd Dashboard || exit 1
npm install

echo ""
echo "[4/5] Compilando Dashboard..."
npm run build

echo ""
echo "[5/5] Publicando Dashboard..."

cd /config/workspace || exit 1

rm -rf public/assets
cp -r Dashboard/dist/assets public/
cp Dashboard/dist/index.html public/
cp Dashboard/dist/favicon.ico public/ 2>/dev/null || true

echo ""
echo "======================================"
echo " Sandbox actualizado"
echo "======================================"
