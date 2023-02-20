@ECHO OFF

SET "localdomain=wp-base.local"
SET "adminpath=/wp-admin"

chromium --args --allow-insecure-localhost --host-resolver-rules="MAP %localdomain% localhost" "https://%localdomain%" "https://%localdomain%%adminpath%"

