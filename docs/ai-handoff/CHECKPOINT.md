# MayaHamrah — Quick Checkpoint

Date: 2026-08-13

Current stable commit:

`0017b9c — make fresh database migrations test-safe`

Main feature commit:

`5359570 — archive exchange rates and snapshot USD on sales`

Status:

- app builds successfully
- `php artisan test` passes: 25 tests / 61 assertions
- exchange rates are archived in `exchange_rates`
- every new sale can store an immutable USD-rate snapshot
- today's sale rate is loaded automatically from Navasan
- historical sale dates can accept a manual USD rate when no archive exists
- sale details show sale-day USD and current USD in one compact comparison block
- financial summary UI on sale details was manually reviewed and approved
- inventory editing, sales filters, global navigation and dashboard remain operational

Latest completed:

1. Navasan currency logic was centralized in `CurrencyRateService`.
2. Daily USD/AED rates are archived in the database.
3. Sales now store `usd_rate`, `usd_rate_date`, and `usd_rate_source`.
4. Sale creation automatically resolves today's USD rate or allows manual historical entry.
5. Sale detail pages expose historical USD context for customer/market comparison.
6. The financial contract summary was reorganized into clearer purchase, sale, profit, installment, and currency sections.
7. Installment creation bug caused by `Jalalian::addMonths(0)` was fixed.
8. Apple catalog migration now works on a fresh database without depending on a seeder.
9. Registration tests now reflect the intentional removal of public registration.
10. Production build, manual sale testing, and the full test suite passed.

Next task:
Continue the historical-price estimation workflow using actual MayaHamrah sales normalized by historical USD rates.

Before starting:

- run `git status`
- preserve append-only notes and financial history
- keep current tested sales/device flows stable
