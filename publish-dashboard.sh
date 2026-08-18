#!/bin/bash

echo "======================================"
echo " Publicando Dashboard"
echo "======================================"

cd /config/workspace || exit 1

echo ""
echo "[1/2] Eliminando assets anteriores..."

rm -rf public/assets

echo ""
echo "[2/2] Copiando Dashboard..."

cp -r Dashboard/dist/assets public/
cp Dashboard/dist/index.html public/
cp Dashboard/dist/favicon.ico public/ 2>/dev/null || true

echo ""
echo "======================================"
echo " Dashboard publicado correctamente"
echo "======================================"
