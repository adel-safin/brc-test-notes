#!/bin/sh
php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'
