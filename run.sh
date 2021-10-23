cmd="php /etc/encryption365/encryption365.php "
for i in "$@"; do
  cmd="$cmd '$i'"
done
eval $cmd
