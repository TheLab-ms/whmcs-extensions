#/bin/sh

sudo rm -rf /data/whmcs-staging/website/includes/hooks/*
sudo cp src/hooks/* /data/whmcs-staging/website/includes/hooks/
sudo chown -R www-data:www-data /data/whmcs-staging/website/includes/hooks/