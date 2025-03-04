#!/bin/bash
echo "Running update_ftp_users.sh..."
/usr/local/bin/update_ftp_users.sh

echo "Sleeping for 5 seconds to ensure pureftpd.pdb is updated..."
sleep 5

echo "Starting pure-ftpd..."
exec pure-ftpd -c 30 -C 5 -j -l puredb:/etc/pure-ftpd/pureftpd.pdb -E
