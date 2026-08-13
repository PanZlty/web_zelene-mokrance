# Pozemky â€“ dĂˇtovĂ˝ model a Google Sheets synchronizĂˇcia

## WordPress

- CPT key: `pozemok`
- VerejnĂ˝ slug: `/pozemky/`
- PoÄŤet inicializovanĂ˝ch zĂˇznamov: 55 (`Pozemok 1` aĹľ `Pozemok 55`)
- PĂˇrovacĂ­ kÄľĂşÄŤ: ACF `plot_id`, celĂ© ÄŤĂ­slo 1â€“55

## ACF polia

| Pole | Typ | VĂ˝znam |
| --- | --- | --- |
| `plot_id` | Number | StabilnĂ© ID pozemku 1â€“55 |
| `area_m2` | Number | Rozloha v mÂ˛ |
| `price` | Number | Cena v eurĂˇch; synchronizovanĂˇ zo Sheets |
| `status` | Select | `available`, `reserved`, `sold`; synchronizovanĂ© zo Sheets |

## Google Sheets

TabuÄľku publikujte ako CSV. PovinnĂ© hlaviÄŤky:

```csv
plot_id,price,status
1,75000,available
2,82000,reserved
3,91000,sold
```

VoliteÄľnĂˇ hlaviÄŤka `area_m2` mĂ´Ĺľe synchronizovaĹĄ aj rozlohu. Ak chĂ˝ba, rozloha sa spravuje ruÄŤne v ACF a cron ju nemenĂ­.

URL CSV sa nezapisuje napevno do repozitĂˇra. NastavĂ­ sa v `wp-config.php` alebo v hostiteÄľskom prostredĂ­:

```php
define('ZM_POZEMKY_GOOGLE_SHEET_CSV_URL', 'https://docs.google.com/spreadsheets/d/.../export?format=csv&gid=...');
```

Cron beĹľĂ­ kaĹľdĂ˝ch 15 minĂşt. Frontend vĹľdy ÄŤĂ­ta uloĹľenĂ© ACF hodnoty, nie Google Sheets priamo.

PovolenĂ© statusy:

- `available` â€“ DostupnĂ˝
- `reserved` â€“ RezervovanĂ˝
- `sold` â€“ PredanĂ˝

SlovenskĂ© hodnoty (`dostupnĂ˝`, `rezervovanĂ˝`, `predanĂ˝`) synchronizĂˇcia normalizuje na internĂ© anglickĂ© hodnoty.
