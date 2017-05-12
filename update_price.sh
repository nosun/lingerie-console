#!/bin/sh



while read sn price
do
        mysql -hlocalhost -uadmin -ptrade@mingDA123 mdtradeconsole -e"update product_site set price=$price where p_sn=\"$sn\""
done < price.txt
