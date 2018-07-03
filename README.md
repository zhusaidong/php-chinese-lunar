# php-chinese-lunar
中国阴历与阳历转换

## Usage

```php
composer require zhusaidong/php-chinese-lunar:dev-master
```

```php
require('./vendor/autoload.php');

use ChineseLunar\Lunar;

$lunar = new Lunar;

$lunarArr= $lunar->toLunarDate(2018,07,03);
var_dump($lunarArr);

$solarArr= $lunar->toSolarDate(2018,'五月','二十');
var_dump($solarArr);
```
