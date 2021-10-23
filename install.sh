if [ ! `whoami` = "root" ]; then
  echo -e "\033[31mYou must run this script under root user.\033[0m"
  exit
fi

ipInfo=`curl -s ip.343.re/info | grep "City\|Region\|Country"`
resSite="raw.githubusercontent.com"
if [ -n `echo $ipInfo | grep -o "China"` ]; then
  isSpecial=`echo $ipInfo | grep -o "Hong Kong\|Macau\|Taiwan"`
  if [ ! -n "$isSpecial" ]; then
    resSite="raw.fastgit.org"
  fi
fi
echo -e "\033[33mChoose\033[0m \033[36m$resSite\033[0m \033[33mfor download...\033[0m\c"

workDir="/etc/encryption365"
mkdir -p $workDir
resPath="https://$resSite/dnomd343/Encryption365/master"
curl -sL $resPath/GB2260.json -o $workDir/GB2260.json
curl -sL $resPath/encryption365.php -o $workDir/encryption365.php
curl -sL $resPath/openssl.cnf -o $workDir/openssl.cnf
curl -sL $resPath/run.sh -o $workDir/run.sh
curl -sL $resPath/validation.php -o $workDir/validation.php
chmod +x $workDir/run.sh
ln -s $workDir/run.sh /usr/bin/encryption365
echo -e "\033[32m OK\033[0m\n"

echo -e "\033[33mPlease ensure that the following modules are exist\033[0m"
echo -e "\033[36mphp / php-cli / php-fpm / php-json / php-openssl / php-mbstring\033[0m\n"

if [ ! -n `crontab -l | grep -o encryption365` ]; then
  echo "0 * * * * encryption365 autorenew" >> /var/spool/cron/root
fi

echo -e "\033[33mYou can use \"\033[0m\033[36mencryption365 help\033[0m\033[33m\" command for details.\033[0m\n"
echo -e "\033[32mInstall OK\033[0m"
