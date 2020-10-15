#/bin/sh

# Example usage:
# ./install
# OR 
# BASE_DIR=/data/whmcs ./install.sh

BASE_DIR=${BASE_DIR:-/data/whmcs-staging}

sudo rm -rf $BASE_DIR/website/includes/hooks/*
sudo cp src/hooks/* $BASE_DIR/website/includes/hooks/
sudo chown -R www-data:www-data $BASE_DIR/website/includes/hooks/
sudo cp assets/img/logo.png $BASE_DIR/website/assets/img/logo.png
sudo chown -R www-data:www-data $BASE_DIR/website/assets/img
