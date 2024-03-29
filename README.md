# Validatore di XML della Fattura Elettronica

[![Latest Stable Version](https://img.shields.io/packagist/v/slam/php-validatore-fattura-elettronica.svg)](https://packagist.org/packages/slam/php-validatore-fattura-elettronica)
[![Downloads](https://img.shields.io/packagist/dt/slam/php-validatore-fattura-elettronica.svg)](https://packagist.org/packages/slam/php-validatore-fattura-elettronica)
[![Integrate](https://github.com/Slamdunk/php-validatore-fattura-elettronica/workflows/Integrate/badge.svg?branch=master)](https://github.com/Slamdunk/php-validatore-fattura-elettronica/actions)
[![Code Coverage](https://codecov.io/gh/Slamdunk/php-validatore-fattura-elettronica/coverage.svg?branch=master)](https://codecov.io/gh/Slamdunk/php-validatore-fattura-elettronica?branch=master)

**WARNING**: This package only works for _ITALIAN_ standards!

## Installazione

```
composer require slam/php-validatore-fattura-elettronica
```

## Utilizzo

```php
use SlamFatturaElettronica\Validator;

$feValidator = new Validator();
$feValidator->assertValidXml('<xml ...>');

// In caso di struttura XML errata, viene lanciata una
//      SlamFatturaElettronica\Exception\InvalidXmlStructureException
// In caso di XML valido ma non aderente all'XSD, viene lanciata una
//      SlamFatturaElettronica\Exception\InvalidXsdStructureComplianceException
```

## Elencare tutte le eccezioni

```php
use SlamFatturaElettronica\Validator;

$feValidator = new Validator();
$feValidator->getAllExceptions('<xml ...>');

// Restituisce un array contentente le eccezioni rilevati. Se non ci sono errori l'array sarà vuoto:
```

### Notifiche

```php
use SlamFatturaElettronica\Validator;

$feValidator = new Validator();
$feValidator->assertValidXml('<xml ...>', Validator::XSD_MESSAGGI_LATEST);
```

## Riferimenti

I due siti di riferimento sono al momento:

1. https://www.agenziaentrate.gov.it/wps/content/Nsilib/Nsi/Schede/Comunicazioni/Fatture+e+corrispettivi/Fatture+e+corrispettivi+ST/ST+invio+di+fatturazione+elettronica/?page=schedecomunicazioni
1. http://www.fatturapa.gov.it/export/fatturazione/it/normativa/f-2.htm

Gli XSD usati da questa libreria sono quelli presi dal primo dei due siti,
ovvero `www.agenziaentrate.gov.it`, che a dispetto del numero di versione
esplicitato sembra quello più aggiornato (vedi ad esempio tra i tipi di
documento la differenza su `Autofattura`).

## Validazione Email in versione `1.2.1`

La versione `1.2.1` introduce una regex per la validazione delle email, che tuttavia è [costruita male](https://github.com/Slamdunk/php-validatore-fattura-elettronica/issues/11#issuecomment-706079124).
Visto che la finalità di questa libreria è di più ampio respiro, è stata sovrascritta la regex delle email
con una più permissiva. La validazione della mail è in capo all'utente:

```diff
diff --git a/xsd/Schema_VFPR121a.xsd b/xsd/Schema_VFPR121a.xsd
index e999199..fa5696b 100644
--- a/xsd/Schema_VFPR121a.xsd
+++ b/xsd/Schema_VFPR121a.xsd
@@ -1364,8 +1364,9 @@
   </xs:simpleType>
   <xs:simpleType name="EmailType">
     <xs:restriction base="xs:token">
+      <xs:minLength value="7" />
       <xs:maxLength value="256" />
-      <xs:pattern value="([!#-'*+/-9=?A-Z^-~-]+(\.[!#-'*+/-9=?A-Z^-~-]+)*|&quot;(\[\]!#-[^-~ \t]|(\\[\t -~]))+&quot;)@([!#-'*+/-9=?A-Z^-~-]+(\.[!#-'*+/-9=?A-Z^-~-]+)*|\[[\t -Z^-~]*\])" />
+      <xs:pattern value=".+@.+[.]+.+" />
     </xs:restriction>
   </xs:simpleType>
   <xs:simpleType name="EmailContattiType">
```
