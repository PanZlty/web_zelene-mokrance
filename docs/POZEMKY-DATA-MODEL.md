# Pozemky – dátový model a Google Sheets synchronizácia

## WordPress

- CPT key: `pozemok`
- Verejný slug: `/pozemky/`
- Počet inicializovaných záznamov: 55 (`Pozemok 1` až `Pozemok 55`)
- Párovací kľúč: ACF `plot_id`, celé číslo 1–55

## ACF polia

| Pole | Typ | Význam |
| --- | --- | --- |
| `plot_id` | Number | Stabilné ID pozemku 1–55 |
| `area_m2` | Number | Rozloha v m² |
| `price` | Number | Cena v eurách; synchronizovaná zo Sheets |
| `status` | Select | `available`, `reserved`, `sold`; synchronizované zo Sheets |

## Google Sheets

Tabuľku publikujte ako CSV. Povinné hlavičky:

```csv
plot_id,price,status
1,75000,available
2,82000,reserved
3,91000,sold
```

Voliteľná hlavička `area_m2` môže synchronizovať aj rozlohu. Ak chýba, rozloha sa spravuje ručne v ACF a cron ju nemení.

URL CSV sa nezapisuje napevno do repozitára. Nastaví sa v `wp-config.php` alebo v hostiteľskom prostredí:

```php
define('ZM_POZEMKY_GOOGLE_SHEET_CSV_URL', 'https://docs.google.com/spreadsheets/d/.../export?format=csv&gid=...');
```

Cron beží každých 15 minút. Frontend vždy číta uložené ACF hodnoty, nie Google Sheets priamo.

Povolené statusy:

- `available` – Dostupný
- `reserved` – Rezervovaný
- `sold` – Predaný

Slovenské hodnoty (`dostupný`, `rezervovaný`, `predaný`) synchronizácia normalizuje na interné anglické hodnoty.
