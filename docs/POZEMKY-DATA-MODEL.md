# Pozemky – dátový model a Google Sheets synchronizácia

## WordPress

- CPT key: `pozemok`
- Verejný slug: `/pozemky/`
- Počet inicializovaných záznamov: 55 (`Pozemok 01` až `Pozemok 55`)
- Párovací kľúč: ACF `plot_id`, dvojmiestny textový kód `01`–`55`

## ACF polia

| Pole | Typ | Význam |
| --- | --- | --- |
| `plot_id` | Text | Stabilný dvojmiestny kód pozemku `01`–`55` |
| `area_m2` | Number | Rozloha v m²; synchronizovaná zo Sheets |
| `price` | Number | Cena v eurách; synchronizovaná zo Sheets |
| `status` | Select | `available`, `reserved`, `sold`; synchronizovaný zo Sheets |

## Google Sheets

Tabuľku publikujte ako CSV. Povinné hlavičky:

```csv
plot_id,price,area_m2,status
01,75000,650,available
02,82000,712,reserved
03,91000,805,sold
```

Presná hlavička tabuľky:

```csv
plot_id,price,area_m2,status
```

Google Sheets je zdroj pre cenu, rozlohu aj stav. Import zabezpečuje `WP All Import Pro + ACF Add-On`, rovnaký model ako pri Rezidencii Štúrova. Unique identifier importu je `{plot_id[1]}`. Import smie aktualizovať iba ACF polia `price`, `area_m2` a `status`; nesmie meniť názov, slug, obsah, obrázky ani Bricks dáta.

Plánovanie importu sa nastaví vo WP All Import po vložení publikovanej Google Sheets CSV adresy. Frontend vždy číta uložené ACF hodnoty, nie Google Sheets priamo.

Povolené statusy:

- `available` – Dostupný
- `reserved` – Rezervovaný
- `sold` – Predaný

V Google Sheets sa používajú presne interné hodnoty `available`, `reserved`, `sold`, aby bol import jednoznačný.
