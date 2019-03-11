php -S localhost:8080 -t ./web/ /web/dev-router.php &
PID=$!
phpunit
STATUS=$?
kill $PID
exit $STATUS
