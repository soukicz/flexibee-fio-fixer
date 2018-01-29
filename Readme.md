## Opravovač popisku FIO transakcí ve Flexibee
Flexibee při napojení na FIO stahuje jen prvních 20 znaků z popisu. Tento balíček stáhne kompletní popisky přes FIO API a doplní je do Flexibee.

Balíček pouze aktualizuje popisy existujících pohybů, které vyhledá podle ID. Původní popis přepisuje a neexistující transakce ignoruje.


## Instalace
```bash
composer require soukicz/flexibee-fio-fixer
```

## Použití
##### Aktualizovat transakce za posledních 24 hodin:
```php
require 'vendor/autoload.php';

$fixer = \Soukicz\FlexibeeFioFixer\FlexibeeFioFixer::factory(
    'flexibee-user', 
    'flexibee-password',
    'demo.flexibee.eu', 
    5434, 
    'demo',
    'fio-token-absdflkgsdjkgjdfkljgdkljg'
);

$fixer->update(new DateTimeImmutable('-24 hours'), new DateTimeImmutable('-24 hours'));

```

##### Aktualizovat transakce za poslední rok:
Pro delší časové úseky je lepší aktualizaci rozdělit na dávky.
```php
require 'vendor/autoload.php';

$fixer = \Soukicz\FlexibeeFioFixer\FlexibeeFioFixer::factory(
    'flexibee-user', 
    'flexibee-password',
    'demo.flexibee.eu', 
    5434, 
    'demo',
    'fio-token-absdflkgsdjkgjdfkljgdkljg'
);

$date = time();
$endDate = strtotime('-1 year'); 
$timeStep = 60 * 60 * 24 * 30;
while ($date > $endDate) {
    $last = time();
    $fixer->update(DateTime::createFromFormat('U', $date - $timeStep), DateTime::createFromFormat('U', $date));
    echo date('Y-m-d', $date) . "\n";
    while (time() < $last + 30) sleep(1); // FIO dovoluje stažení jednou za 30 sekund 
    $date -= $timeStep;
}

```
