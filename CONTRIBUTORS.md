# Contributors

## By order of appearance

* Alexandre Salomé
* Grégoire Pineau
* Matt Parker
* Garanzha Dmitriy
* Igor
* Adrov Igor
* Ilya Troy
* Jordan Eldredge
* Andrew Kovalyov
* πR
* Francois PAULIN
* Joeri Timmermans
* Nicolas Hart
* Sergio Gómez
* OwlyCode

## Command used to generate:

```
git log --reverse --format="%aN" \
| sed "s/alexandresalome/Alexandre Salomé/g" \
| perl -ne 'if (!defined $x{$_}) { print $_; $x{$_} = 1; }'
```
