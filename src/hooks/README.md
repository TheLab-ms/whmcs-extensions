## Installation

# WHMCS Custom Fields:

* Badge Number: Text Box
* Voting Eligible: Tick Box
  * Admin Only

# WHMCS Support Department

* Create General Support Department

# Hook installs
```
docker exec -it members bash
apt-get install git
cd /var/www/html/includes/hooks 
php /var/www/html/vendor/composer/composer/bin/composer install
```