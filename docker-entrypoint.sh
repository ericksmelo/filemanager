#!/bin/sh
set -e

MAX=${MAX_FILE_MB:-50}
# post_max_size needs a few extra MB for multipart boundaries/headers
POST=$((MAX + 2))

cat > /usr/local/etc/php/conf.d/upload-limits.ini <<EOF
upload_max_filesize = ${MAX}M
post_max_size       = ${POST}M
memory_limit        = 256M
max_execution_time  = 300
EOF

exec apache2-foreground
