# VATMATE - Full System Specification & Business Logic Documentation

> **Document Version:** 1.0.0  
> **Target Audience:** Engineering Team / System Architects (Node.js Re-engineering Roadmap)  
> **Domain Standards:** National Board of Revenue (NBR), Bangladesh — Value Added Tax & Supplementary Duty Act, 2012 and VAT Rules, 2016.

---

## 1. Executive Summary & Purpose

**VATMATE** is a comprehensive, multi-tenant VAT accounting and compliance management platform tailored specifically for Bangladeshi commercial importers, manufacturers, traders, and corporate enterprises operating under the **Value Added Tax and Supplementary Duty Act, 2012**.

The system automates the complete statutory lifecycle of VAT administration:
1. **Entity & Taxpayer Setup**: Business Identification Number (BIN), VAT Commissionerate, Circle, Division, and Bank setup.
2. **Form 4.3 (Input-Output Coefficient / Price Declaration)**: Input materials, conversion factors, value addition breakdown, and profit markup.
3. **Purchases (Local & Import / Bill of Entry)**: Duty calculations (CD, RD, SD, VAT, AT, MCU), 4-month tax period rebate validation, and stock registration.
4. **Sales & Mushak 6.3 (Tax Invoices)**: Sequential invoice numbering (`INV-yy/xxxxx`), tiered VAT rates (0%, 7.5%, 15%), statutory banking payment threshold (`sale_limit = 100,000 BDT`), and real-time inventory deductions.
5. **Form 6.2.1 (Purchase-Sale Register Ledger)**: Real-time cumulative balance ledger maintaining strict chronologically ordered stock records.
6. **Adjustments (Mushak 6.7, 6.8, Form 4.4, Form 4.5, Treasury)**: Increasing/Decreasing adjustments, Credit/Debit notes, damaged goods write-offs, and treasury challan deposits.
7. **Mushak 9.1 (Monthly VAT Return)**: Automated generation of the 65-note statutory VAT return, closing balance roll-overs, and PDF generation via headless browser rendering.

---

## 2. Technology Stack & Re-engineering Target

### Current Stack (Reference Implementation)
- **Framework:** Laravel 12 (Modular via `nwidart/laravel-modules`)
- **Frontend:** Inertia.js + Vue 3 (Composition API) + Vite + Bootstrap 5
- **Database:** PostgreSQL 16 (Relational schema, sequence counters, date truncation, JSONB)
- **Cache & Queue:** Redis 8 / Valkey 9 + Laravel Horizon
- **Real-time WebSockets:** Laravel Reverb (Pusher-compatible, port 6001)
- **PDF Generation:** Spatie Browsershot (Headless Chromium / Puppeteer)

### Recommended Node.js Re-engineering Stack (1:1 Architectural Parity)
- **Runtime:** Node.js (v22+ LTS) with TypeScript
- **Backend Framework:** Fastify or NestJS (Strict typing, high throughput)
- **ORM / Query Builder:** Prisma or Kysely / TypeORM (PostgreSQL dialect)
- **Queue / Background Jobs:** BullMQ + Redis (Replaces Laravel Horizon)
- **Real-time WebSockets:** Socket.io or `@fastify/websocket` (or Pusher protocol server)
- **PDF Engine:** Puppeteer (Headless Chrome shell)
- **Authentication & Security:** JWT / HttpOnly secure cookies + bcrypt / argon2id

---

## 3. Database Schema & Data Dictionary

The PostgreSQL database consists of 30 relational tables. All decimal fields dealing with money or tax are standard `DECIMAL(12, 2)` or `DECIMAL(14, 2)`.

### 3.1. Authentication & System Administration
- `roles`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 255, Unique: `superadmin`, `admin`, `manager`, `guest`)
  - `created_at`, `updated_at` (TIMESTAMP)
- `users`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 255)
  - `email` (VARCHAR 255, Unique)
  - `mobile` (VARCHAR 20)
  - `password` (VARCHAR 255, Hashed)
  - `forget_password` (VARCHAR 255, Nullable)
  - `role_id` (FK -> `roles.id`, ON UPDATE CASCADE, ON DELETE SET NULL)
  - `status` (CHAR(1), Default '1': '1'=Active, '0'=Inactive)
  - `remember_token` (VARCHAR 100, Nullable)
  - `created_at`, `updated_at` (TIMESTAMP)
- `password_reset_tokens`: `email` (PK), `token`, `created_at`
- `personal_access_tokens`: Token authentication storage

### 3.2. Administrative Jurisdictions & Lookups
- `divisions`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 100: VAT Commissionerates e.g. Sylhet, Dhaka South, Chattogram)
  - `created_at`, `updated_at`
- `stationcodes`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 100: Customs entry points e.g. Tamabil, Benapole, Chattogram Port)
  - `code` (VARCHAR 20)
  - `created_at`, `updated_at`
- `banks`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 100 e.g. Sonali Bank, Dutch-Bangla Bank)
  - `created_at`, `updated_at`
- `branches`:
  - `id` (PK, Serial)
  - `bank_id` (FK -> `banks.id`)
  - `division_id` (FK -> `divisions.id`)
  - `name` (VARCHAR 100)
  - `code` (VARCHAR 20)
  - `created_at`, `updated_at`
- `customer_management`:
  - `id` (PK, Serial)
  - `customer_name` (VARCHAR 100)
  - `mobile` (VARCHAR 11, Unique)
  - `bin_or_nid` (VARCHAR 17, Nullable)
  - `address` (VARCHAR 150, Nullable)
  - `created_at`, `updated_at`

### 3.3. Taxpayer Entities (Clients)
- `clients`:
  - `id` (PK, Serial)
  - `division_id` (FK -> `divisions.id`)
  - `bin` (VARCHAR 20, Unique 13-digit Business Identification Number, formatted as `xxxxxxxxx-xxxx`)
  - `company_name` (VARCHAR 150)
  - `name` (VARCHAR 100, Proprietor / MD name)
  - `vat_username` (VARCHAR 50, NBR portal username)
  - `vat_password` (VARCHAR 100, NBR portal password)
  - `email` (VARCHAR 100, Nullable)
  - `mobile` (VARCHAR 20)
  - `address` (TEXT)
  - `reference` (VARCHAR 100, Nullable)
  - `tin` (VARCHAR 30, Tax Identification Number)
  - `circle` (VARCHAR 100, VAT Circle office)
  - `user_id` (FK -> `users.id`: The assigned manager/admin)
  - `status` (SMALLINT, Default 1: 1=Active, 0=Inactive)
  - `search_text` (TEXT, Indexed for ILIKE full searches)
  - `created_at`, `updated_at`

### 3.4. Item Catalog, Units & Tax Configurations
- `units`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 50 e.g. MT, KG, CFT, PCS, BOX, LTR)
  - `created_at`, `updated_at`
- `items`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 150)
  - `hs_code` (VARCHAR 30, 8-digit Harmonized System Tariff Code)
  - `description` (TEXT, Nullable)
  - `cd` (DECIMAL(5,2), Default 0.00: Customs Duty %)
  - `rd` (DECIMAL(5,2), Default 0.00: Regulatory Duty %)
  - `sd` (DECIMAL(5,2), Default 0.00: Supplementary Duty %)
  - `vat` (DECIMAL(5,2), Default 15.00: Standard VAT %)
  - `at` (DECIMAL(5,2), Default 7.50: Advance Tax %)
  - `search_text` (TEXT)
  - `created_at`, `updated_at`
- `economic_areas`:
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `item_id` (FK -> `items.id`)
  - Unique Constraint: `(client_id, item_id)`
  - `created_at`, `updated_at`
- `vatfactors`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 50: `vatfact`, `sale_limit`, `atfact`, `month_gap`)
  - `value` (VARCHAR 50)
  - `description` (TEXT)
- `mushaknotes`:
  - `id` (PK, Serial)
  - `name_note_no` (VARCHAR 50: `note_13`=0%, `note_15`=15%, `note_22`=7.5%)
  - `percent` (DECIMAL(5,2))
- `rates`:
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `item_id` (FK -> `items.id`)
  - `vat` (DECIMAL(5,2))
  - `sd` (DECIMAL(5,2), Default 0.00)
  - `last` (SMALLINT, Default 1: 1 indicates currently active rate for client-item pair)
  - `created_at`, `updated_at`
- `costsheets`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 100: Cost element e.g. Raw Material, Direct Labor, Overhead, Power, Depreciation, Margin)
  - `created_at`, `updated_at`
- `expenses`:
  - `id` (PK, Serial)
  - `name` (VARCHAR 100: `Closing Balance`, `Increase`, `Decrease`, `Penalty`, `VDS`)
  - `created_at`, `updated_at`

### 3.5. Form 4.3 (Price Declaration) & Additions
- `form43s`:
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `item_id` (FK -> `items.id`)
  - `lc_no` (VARCHAR 50)
  - `boe_no` (VARCHAR 50, Bill of Entry number)
  - `boe_date` (DATE)
  - `submission_date` (DATE)
  - `first_delivery_date` (DATE, Nullable)
  - `purchase_unit_id` (FK -> `units.id`)
  - `sale_unit_id` (FK -> `units.id`)
  - `factor` (DECIMAL(10,4), Conversion ratio between purchase unit and sale unit)
  - `item_code` (INTEGER, Unique per client item)
  - `mcu` (DECIMAL(12,2), Minimum Customs Unit value)
  - `quantity` (DECIMAL(12,2))
  - `excess_quantity` (DECIMAL(12,2), Default 0.00)
  - `wastage` (DECIMAL(5,2), Wastage percentage 0.00-100.00)
  - `base_value` (DECIMAL(14,2), Total base landed cost)
  - `unit_price` (DECIMAL(14,2), Derived base unit price)
  - `last` (SMALLINT, 1=Active latest declaration)
  - `created_at`, `updated_at`
- `additions`:
  - `id` (PK, Serial)
  - `form43_id` (FK -> `form43s.id`, ON DELETE CASCADE)
  - `costsheet_id` (FK -> `costsheets.id`)
  - `value` (DECIMAL(12,2), Monitory value of cost component)
  - `rate` (DECIMAL(5,2), Percentage: `(value / form43.unit_price) * 100`)
  - `created_at`, `updated_at`

### 3.6. Transactions & Register Ledgers
- `purchases`:
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `item_id` (FK -> `items.id`)
  - `stationcode_id` (FK -> `stationcodes.id`)
  - `mushaknote_id` (FK -> `mushaknotes.id`)
  - `unit_id` (FK -> `units.id`)
  - `lc_no` (VARCHAR 50)
  - `lc_date` (DATE, Nullable)
  - `boe` (VARCHAR 20)
  - `boe_date` (DATE)
  - `entry_date` (DATE, Stock ledger entry date)
  - `receipt_number` (VARCHAR 50, Nullable)
  - `receipt_date` (DATE, Nullable)
  - `assessable_value` (DECIMAL(14,2))
  - `cd` (DECIMAL(5,2)), `cd_value` (DECIMAL(14,2))
  - `rd` (DECIMAL(5,2)), `rd_value` (DECIMAL(14,2))
  - `sd` (DECIMAL(5,2)), `sd_value` (DECIMAL(14,2))
  - `vat` (DECIMAL(5,2)), `vat_value` (DECIMAL(14,2))
  - `at` (DECIMAL(5,2)), `at_value` (DECIMAL(14,2))
  - `mcu` (DECIMAL(5,2)), `mcu_amount` (DECIMAL(14,2))
  - `df_cvat_cf` (DECIMAL(14,2), Port/C&F/handling expenses)
  - `base_value` (DECIMAL(14,2), `av + cd_val + rd_val + sd_val`)
  - `quantity` (DECIMAL(12,2))
  - `excess_quantity` (DECIMAL(12,2))
  - `wastage` (DECIMAL(5,2))
  - `total_quantity` (DECIMAL(12,2), Net quantity in purchase units)
  - `quantity_converted` (DECIMAL(12,2), Net quantity in sale units)
  - `unit_price` (DECIMAL(14,2), Converted cost price per sale unit)
  - `total_price` (DECIMAL(14,2))
  - `opening_stock` (DECIMAL(12,2))
  - `stock` (DECIMAL(12,2), Balance after purchase)
  - `created_at`, `updated_at`
- `sales`:
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `item_id` (FK -> `items.id`)
  - `unit_id` (FK -> `units.id`)
  - `bank_id` (FK -> `banks.id`, Nullable, required if `grand_total > sale_limit`)
  - `invoice_no` (VARCHAR 30, Format: `INV-yy/xxxxx`)
  - `date` (DATE)
  - `customer_name` (VARCHAR 100, Nullable)
  - `customer_mobile` (VARCHAR 20, Nullable)
  - `bin_or_nid` (VARCHAR 17, Nullable)
  - `address` (VARCHAR 150, Nullable)
  - `transaction_no` (VARCHAR 50, Nullable)
  - `transaction_date` (DATE, Nullable)
  - `opening_stock` (DECIMAL(12,2))
  - `quantity` (DECIMAL(12,2))
  - `stock` (DECIMAL(12,2), Balance after sale)
  - `last_purchase_unit_price` (DECIMAL(14,2))
  - `unit_price` (DECIMAL(14,2))
  - `item_price` (DECIMAL(14,2), `quantity * unit_price`)
  - `sd` (DECIMAL(5,2)), `sd_value` (DECIMAL(14,2))
  - `vat` (DECIMAL(5,2)), `vat_value` (DECIMAL(14,2))
  - `total_price` (DECIMAL(14,2), `item_price + sd_value + vat_value`)
  - `created_at`, `updated_at`
- `form621s` (Central Ledger - Mushak 6.2.1):
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `item_id` (FK -> `items.id`)
  - `unit_id` (FK -> `units.id`)
  - `form43_id` (FK -> `form43s.id`, Nullable)
  - `lc_or_invoice_no` (VARCHAR 50)
  - `boe` (VARCHAR 20, Nullable)
  - `importable_type` (VARCHAR 50: `purchase`, `sale`, `creditnote`, `debitnote`, `form44`, `form45`)
  - `importable_id` (BIGINT: Polymorphic ID to originating table)
  - `opening_stock` (DECIMAL(12,2))
  - `quantity` (DECIMAL(12,2))
  - `stock` (DECIMAL(12,2), Current cumulative stock balance)
  - `date` (DATE)
  - `last` (SMALLINT, 1=Current active tail of ledger, 0=Historical entry)
  - `userid` (FK -> `users.id`)
  - `search_text` (TEXT)
  - `created_at`, `updated_at`

### 3.7. Adjustments & Ancillary Forms
- `creditnotes` (Mushak 6.7):
  - `id` (PK, Serial)
  - `client_id`, `item_id`, `unit_id`
  - `lc_or_invoice_no` (Must start with `INV`)
  - `issued_date`, `vat_challan_date`
  - `value_in_challan`, `quantity_in_challan`, `vat_in_challan`, `sd_in_challan`
  - `value_of_decreasing_adjustment`, `quantity_of_decreasing_adjustment`
  - `vat_of_decreasing_adjustment`, `sd_of_decreasing_adjustment`
  - `reason_for_issuance`, `remarks`
  - `opening_stock`, `stock` (Stock **increases** by returned quantity)
- `debitnotes` (Mushak 6.8):
  - `id` (PK, Serial)
  - `client_id`, `item_id`, `unit_id`
  - `lc_or_invoice_no`, `boe`
  - `issued_date`, `vat_challan_date`
  - `value_in_challan`, `quantity_in_challan`, `vat_in_challan`, `sd_in_challan`
  - `value_of_increasing_adjustment`, `quantity_of_increasing_adjustment`
  - `vat_of_increasing_adjustment`, `sd_of_increasing_adjustment`
  - `reason_for_issuance`, `remarks`
  - `opening_stock`, `stock` (Stock **decreases** by returned quantity)
- `form44s` (Destroyed / Damaged Goods):
  - `id` (PK, Serial)
  - `client_id`, `item_id`, `unit_id`, `lc_or_invoice_no`, `boe`, `date`
  - `quantity`, `value`, `vat`, `proposed_value`
  - `increasing_adjustment_vat`, `net_increase`, `reason_ineligibility`
  - `opening_stock`, `stock` (Stock **decreases**)
- `form45s` (Substandard / By-Products):
  - `id` (PK, Serial)
  - `client_id`, `item_id`, `unit_id`, `lc_or_invoice_no`, `boe`, `date`
  - `quantity`, `value`, `vat`, `proposed_value`
  - `increasing_adjustment_vat`, `net_increase`, `reason_ineligibility`
  - `opening_stock`, `stock` (Stock **increases**)
- `treasuries` (TR Challans):
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `branch_id` (FK -> `branches.id`)
  - `tax_period` (VARCHAR 7, Format: `YYYY-MM`)
  - `challan_no` (VARCHAR 16)
  - `amount` (DECIMAL(14,2))
  - `remarks` (TEXT, Nullable)
- `adjustments`:
  - `id` (PK, Serial)
  - `client_id` (FK -> `clients.id`)
  - `expense_id` (FK -> `expenses.id`)
  - `tax_period` (VARCHAR 7, Format: `YYYY-MM`)
  - `amount` (DECIMAL(14,2))
  - `remarks` (TEXT, Nullable)

---

## 4. User Roles & Permission Matrix

| Role | Role ID | Scope of Authorization |
| :--- | :---: | :--- |
| **`superadmin`** | 1 | Full universal access across all modules, routes, and actions. Can manage users, alter global configurations, view queue health in Horizon, and operate as any client. |
| **`admin`** | 2 | Administrative access across all clients. Can create/edit clients, items, banks, declarations, ledgers, and reports. Cannot view Horizon or delete superadmins. |
| **`manager`** | 3 | Operational scope restricted **strictly** to clients assigned to their `user_id` (`client.user_id === user.id`). Can enter purchases, issue sales invoices, manage stock, and view reports for their clients. |
| **`guest`** | 4 | Read-only view for audit purposes. Cannot create, update, or delete transactions. |

---

## 5. Mathematical Formulations & Core Business Rules

### 5.1. Import Purchase Calculation (Bill of Entry)
When an import purchase is recorded from a Customs Bill of Entry (BoE):
$$\text{CD Value} = \text{Assessable Value (AV)} \times \frac{\text{CD \%}}{100}$$
$$\text{RD Value} = \text{AV} \times \frac{\text{RD \%}}{100}$$
$$\text{SD Base} = \text{AV} + \text{CD Value} + \text{RD Value}$$
$$\text{SD Value} = \text{SD Base} \times \frac{\text{SD \%}}{100}$$
$$\text{Base Value (VAT Base)} = \text{AV} + \text{CD Value} + \text{RD Value} + \text{SD Value}$$
$$\text{VAT Value (15\%)} = \text{Base Value} \times \frac{\text{VAT \%}}{100}$$
$$\text{Advance Tax (AT)} = \text{Base Value} \times \frac{\text{AT \%}}{100}$$
$$\text{Total Gross Quantity} = \text{Quantity} + \text{Excess Quantity}$$
$$\text{Net Purchase Quantity} = \text{Total Gross Quantity} \times \left(1 - \frac{\text{Wastage \%}}{100}\right)$$
$$\text{Converted Sale Quantity} = \text{Net Purchase Quantity} \times \text{Factor (from Form 4.3)}$$
$$\text{Landed Unit Cost (Sale Unit)} = \frac{\text{Base Value}}{\text{Converted Sale Quantity}}$$

#### Statutory BoE Rebate Period Rule (4-Month Tax Period Limit):
An importer can only claim input tax rebate (Note 15) if the Bill of Entry is entered within the statutory tax period gap:
$$\text{BoE Index} = \text{Year}_{\text{boe}} \times 12 + \text{Month}_{\text{boe}}$$
$$\text{Entry Index} = \text{Year}_{\text{entry}} \times 12 + \text{Month}_{\text{entry}}$$
$$\text{Month Gap} = (\text{Entry Index} - \text{BoE Index}) + 1$$
**Rule:** If $\text{Month Gap} > \text{vatfactor.month\_gap}$ (Default: 7 or statutory 4 months), input tax rebate is disallowed, and the purchase must be flagged under non-rebateable note.

---

### 5.2. Sales & Mushak 6.3 Tax Invoice Calculation
For each line item in a sales invoice:
$$\text{Item Price} = \text{Quantity} \times \text{Unit Price}$$
$$\text{SD Value} = \text{Item Price} \times \frac{\text{SD \%}}{100}$$
$$\text{VAT Base} = \text{Item Price} + \text{SD Value}$$
$$\text{VAT Value} = \text{VAT Base} \times \frac{\text{VAT \%}}{100}$$
$$\text{Line Total} = \text{Item Price} + \text{SD Value} + \text{VAT Value}$$

#### Statutory Invoice Rules:
1. **Invoice Number Generation:** Sequentially generated per client per calendar year:
   $$\text{Format: } \text{INV-yy/xxxxx} \quad (\text{e.g., } \text{INV-26/00001}, \text{INV-26/00002})$$
2. **Banking Channel Payment Rule:**
   $$\text{If } \sum \text{Line Total (15\% VAT items)} > \text{sale\_limit (100,000 BDT)}$$
   The transaction MUST record `bank_id`, `transaction_no`, and `transaction_date`. Cash payment above 100,000 BDT disallows input tax rebate to the buyer under Section 46 of the VAT Act.
3. **Stock Availability Constraint:**
   $$\text{Quantity Sold} \le \text{Current Available Stock in Form 6.2.1}$$
   Negative stock transactions are strictly rejected.
4. **Chronological Integrity Rule:**
   $$\text{Date of Sale} \le \text{Today} \quad \text{AND} \quad \text{Date of Sale} \ge \max(\text{Form 6.2.1 Date for Client})$$
   Backdated entries prior to already recorded ledger transactions are blocked to prevent ledger corruption.

---

### 5.3. Form 6.2.1 Real-Time Ledger Mechanics (Purchase-Sale Register)
`form621s` tracks every movement of inventory for each client and item pair:
$$\text{Purchase: } \text{Stock}_{\text{new}} = \text{Stock}_{\text{previous}} + \text{Quantity}_{\text{converted}}$$
$$\text{Sale: } \text{Stock}_{\text{new}} = \text{Stock}_{\text{previous}} - \text{Quantity}$$
$$\text{Credit Note (Sales Return): } \text{Stock}_{\text{new}} = \text{Stock}_{\text{previous}} + \text{Quantity}_{\text{returned}}$$
$$\text{Debit Note (Purchase Return): } \text{Stock}_{\text{new}} = \text{Stock}_{\text{previous}} - \text{Quantity}_{\text{returned}}$$
$$\text{Form 4.4 (Damaged Goods): } \text{Stock}_{\text{new}} = \text{Stock}_{\text{previous}} - \text{Quantity}$$
$$\text{Form 4.5 (By-Product Addition): } \text{Stock}_{\text{new}} = \text{Stock}_{\text{previous}} + \text{Quantity}$$

#### Ledger Immutability Lock:
Only the row having `last = 1` (the tail of the ledger chain) can be updated or deleted. Modifying historical rows (`last = 0`) is forbidden because it would invalidate subsequent stock levels. When deleting the tail row, the preceding row must be restored to `last = 1`.

---

### 5.4. Form 4.3 (Price Declaration) & 7.5% Rate Deviation Rule
Every manufactured or traded product requires a registered Form 4.3 before it can be sold.
1. **Conversion Factor:** Used to convert raw material / import unit (e.g. Metric Ton) into retail sale unit (e.g. CFT, KG, Bag).
2. **Value Addition Elements (`additions`):**
   $$\text{Addition Rate \%} = \left(\frac{\text{Component Value}}{\text{Form 4.3 Unit Price}}\right) \times 100$$
3. **Declaration Locking:** Once a `form43_id` has been referenced in `form621s`, cost sheet additions cannot be inserted, updated, or removed (`Can not insert: already used in 6.2.1`).
4. **Statutory 7.5% Rate Deviation Rule (`vatfactor.vatfact = 7.5`):**
   Before a selling rate or invoice price is accepted, the system checks variance against declared base:
   $$\text{Form 4.3 Base Cost} = \text{Form 4.3 Unit Price} + \sum \text{Costsheet Additions}$$
   $$\text{Deviation \%} = \left(\frac{\text{Sale Value} - \text{Form 4.3 Base Cost}}{\text{Form 4.3 Base Cost}}\right) \times 100$$
   - If $\text{Deviation \%} > +7.5\%$ or $\text{Deviation \%} < -7.5\%$, the system strictly rejects the transaction:  
     `"Over 7.5% (calc%)"` or `"Less 7.5% (calc%)"`.
   - *Legal Mandate:* Under NBR VAT Rule 4.3, any selling price change beyond $\pm 7.5\%$ necessitates an officially submitted revised Form 4.3 Price Declaration before sales can proceed.

---

### 5.5. Form 4.4 (Damaged / Destroyed Goods) & Form 4.5 (Wastage Goods)
1. **Invoice Numbering:** `F44-yy/xxxxx` and `F45-yy/xxxxx` (sequentially generated per client per calendar year).
2. **Form 6.2.1 Real-Time Stock Impact:** Decreases available inventory balance in the ledger chain.
3. **Increasing Adjustment & Net Increase Formula:**
   $$\text{price} = \text{quantity} \times \text{purchase\_unit\_price}$$
   $$\text{sdValue} = \text{price} \times \left(\frac{\text{purchase\_sd}}{100}\right)$$
   $$\text{vatValue} = (\text{price} + \text{sdValue}) \times \left(\frac{\text{purchase\_vat}}{100}\right)$$
   $$\text{adj} = \text{quantity} \times \text{proposed\_value}$$
   $$\text{pvsd} = \text{adj} \times \left(\frac{\text{rate\_sd}}{100}\right)$$
   $$\text{pvvat} = (\text{adj} + \text{pvsd}) \times \left(\frac{\text{rate\_vat}}{100}\right)$$
   - If $\text{Note 15 \%} == \text{rate\_vat}$:
     $$\text{Increasing Adjustment VAT} = \text{pvvat}, \quad \text{Net Increase} = \text{vatValue} - \text{pvvat}$$
   - Else:
     $$\text{Increasing Adjustment VAT} = \text{pvvat}, \quad \text{Net Increase} = \text{pvvat}$$
4. **Validation Constraints:**
   - $\text{Quantity} \le \text{Current Stock in Form 6.2.1}$
   - $\text{Proposed Value} \le \text{Actual Purchase Unit Price}$
   - Date cannot exceed today and cannot be earlier than client's last transaction in Form 6.2.1.

---

### 5.6. Credit Note (Mushak 6.7) & Debit Note (Mushak 6.8)
1. **Credit Note (Mushak 6.7 - Sales Return / Rebate):**
   - Sequential Numbering: `CRN-yy/xxxxx`.
   - Mandatory reference to original sales invoice (`INV-...`).
   - Generates a **Decreasing Adjustment** on VAT Return (reduces payable tax to NBR).
   - **Increases stock** in Form 6.2.1 (goods returned to store).
2. **Debit Note (Mushak 6.8 - Price or Quantity Increase):**
   - Sequential Numbering: `DBN-yy/xxxxx`.
   - Mandatory reference to original sales invoice (`INV-...`).
   - Generates an **Increasing Adjustment** on VAT Return (increases payable tax to NBR).
   - **Decreases stock** or increases revenue value in Form 6.2.1.
3. **Ledger Date Guard:** Date cannot be in the future, and cannot be earlier than `max(Form 6.2.1 Date)`.

---

### 5.7. Economic Area Matrix (`economic_areas`)
- Pivot table linking `client_id` and `item_id` with composite unique constraint `(client_id, item_id)`.
- Defines which items each entity (taxpayer) is licensed to trade, manufacture, or import.
- **De-linking Lock:** An item cannot be detached from a client if `EXISTS (SELECT 1 FROM form621s WHERE client_id = ? AND item_id = ?)`.

---

### 5.8. Asynchronous Excel Exports & WebSocket Notifications
- When an Excel export is triggered for Form 6.2.1 (`Report621ExportJob`) or Form 6.3 (`Report63ExportJob`):
  1. The job executes asynchronously in the background queue.
  2. Data is written directly to disk (`temp_exports/{fileName}`).
  3. Upon completion, the system broadcasts `ExcelExportEvent` (alias: `excelready`) over private WebSocket channel `App.Models.User.{userId}`.
  4. The frontend listener automatically downloads the generated spreadsheet without freezing the user interface.

---

### 5.9. Mushak 9.1 (Monthly VAT Return) Master Calculation Architecture

The monthly VAT return (Form 9.1) aggregates all transactional records for a given `tax_period` (`YYYY-MM`) into standardized statutory notes:

#### Part 3: Supply / Output VAT (Sales)
- **Note 3 (Zero Rated):** $\sum \text{Sales with VAT } 0\%$
- **Note 4 (Standard 15% Rated):**
  - `Note 4a (Value)`: $\sum \text{Item Price for 15\% Sales}$
  - `Note 4b (SD)`: $\sum \text{SD Value for 15\% Sales}$
  - `Note 4c (Output VAT)`: $\sum \text{VAT Value for 15\% Sales}$
- **Note 8 (Reduced Rate / 7.5%):**
  - `Note 8a, 8b, 8c`: Item Price, SD, and VAT for 7.5% Sales
- **Note 9 (Total Output Tax):**
  - `Note 9a`: $\text{Note 3} + \text{Note 4a} + \text{Note 8a}$
  - `Note 9b`: $\text{Note 4b} + \text{Note 8b}$
  - `Note 9c`: $\text{Note 4c} + \text{Note 8c}$

#### Part 4: Purchases / Input Tax Rebate
- **Note 13 (Zero Rated Imports):** $\sum \text{Base Value with 0\% VAT}$
- **Note 15 (Standard 15% Rated Purchases - Rebateable):**
  - `Note 15a (Base Value)`: $\sum \text{Base Value for 15\% Purchases}$
  - `Note 15b (Input VAT Rebate)`: $\sum \text{VAT Value for 15\% Purchases}$
- **Note 22 (Other Non-rebateable Purchases):** Purchases under Note 22
- **Note 23 (Total Input Tax Rebate):**
  - `Note 23a`: $\sum \text{Purchase Base Values}$
  - `Note 23b`: $\text{Note 15b}$ (Total Input VAT eligible for rebate)

#### Part 5: Increasing Adjustments (Payable Additions)
- **Note 26 (Debit Note):** $\sum \text{VAT from Debit Notes issued during tax period}$
- **Note 27 (Other Increasing Adjustments):**
  $$\text{Note 27} = \sum \text{Manual Increasing Adjustments} + \sum \text{Net Increase (Form 4.4)} + \sum \text{Net Increase (Form 4.5)}$$
- **Note 28 (Total Increasing Adjustments):** $\text{Note 26} + \text{Note 27}$

#### Part 6: Decreasing Adjustments (Rebate / Credit Deductions)
- **Note 30 (Advance Tax - AT on Imports):** $\sum \text{AT Value from Purchases in this period}$
- **Note 31 (Credit Note):** $\sum \text{VAT from Credit Notes issued during tax period}$
- **Note 32 (Other Decreasing Adjustments):** $\sum \text{Manual Decreasing Adjustments (e.g. VDS paid)}$
- **Note 33 (Total Decreasing Adjustments):** $\text{Note 30} + \text{Note 31} + \text{Note 32}$

#### Part 7: Net Tax Calculation
- **Note 34 (Net Tax for Current Period):**
  $$\text{Note 34} = \text{Note 9c} - \text{Note 23b} + \text{Note 28} - \text{Note 33}$$
- **Note 52 / Previous Closing Balance (`prevNetPayable`):** Latest `Closing Balance` from `adjustments` where $\text{tax\_period} < \text{current\_period}$.
- **Note 35 (Net Tax Payable after Previous Balance):**
  $$\text{Note 35} = \text{Note 34} - \text{prevNetPayable}$$

#### Part 8: Supplementary Duty Adjustments
- **Note 38:** $\sum \text{SD from Debit Notes}$ (Increasing SD)
- **Note 39:** $\sum \text{SD from Credit Notes}$ (Decreasing SD)

#### Part 9: Penalties, Interest & Treasury Payment
- **Note 44 (Interest & Penalties):** $\sum \text{Adjustments where expense = 'Penalty'}$
- **Note 50 (Total Net Payable to Government):**
  $$\text{Note 50} = \text{Note 35} + \text{Note 44}$$
- **Note 58 (Payment via Treasury Challan):**
  $$\text{Note 58} = \sum \text{Treasury Challans deposited for current tax period}$$
- **Note 65 (Closing Balance Carried Forward):**
  $$\text{Note 65} = \text{Note 58} - \text{Note 50}$$
  - **Automated Roll-Over Rule:** Whenever Form 9.1 is computed, `Note 65` is automatically updated/inserted into the `adjustments` table under `expense = 'Closing Balance'` for `current_period`. The subsequent month automatically consumes this as its opening `prevNetPayable`.

---

## 6. Complete API & Routing Contracts

### 6.1. Authentication & System
| Method | Endpoint | Access Role | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/login` | Guest | Serves Login View |
| `POST` | `/userlogin` | Guest | Authenticates email, password, active status ('1') |
| `POST` | `/userlogout` | Authenticated | Terminates session & invalidates token |
| `GET` | `/horizon` | `superadmin` | Background queue dashboard |
| `GET` | `/user` | `superadmin`, `admin` | Paginated users list |
| `POST` | `/user` | `superadmin`, `admin` | Create new user (name, email, role, mobile) |
| `PUT` | `/user/{id}` | `superadmin`, `admin` | Update user details |
| `DELETE` | `/user/{id}` | `superadmin`, `admin` | Soft / hard delete user |
| `PUT` | `/user/status/{id}` | `superadmin`, `admin` | Toggle active status (1/0) |

### 6.2. Core VAT Operations
| Method | Endpoint | Access Role | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/client` | `admin`, `manager` | Paginated client list |
| `POST` | `/client` | `admin`, `manager` | Register client (BIN, company, circle, division) |
| `GET` | `/item` | `admin` | Item catalog & tariff lookup |
| `POST` | `/item` | `admin` | Create item with HS code and default duties |
| `GET` | `/form43` | `admin`, `manager` | List Form 4.3 declarations |
| `POST` | `/form43` | `admin`, `manager` | Submit new Form 4.3 declaration |
| `POST` | `/addition` | `admin`, `manager` | Add value-addition element to Form 4.3 |
| `GET` | `/purchase` | `admin`, `manager` | List purchase invoices (BoE / Challan) |
| `POST` | `/purchase` | `admin`, `manager` | Insert purchase, calculate taxes & update Form 6.2.1 |
| `GET` | `/sale` | `admin`, `manager` | List sales invoices |
| `POST` | `/sale` | `admin`, `manager` | Validate stock, generate `INV-yy/xxxxx`, update Form 6.2.1 |
| `GET` | `/form621` | `admin`, `manager` | View clients with Form 6.2.1 ledgers |
| `GET` | `/form621/client/{client_id}` | `admin`, `manager` | Individual client 6.2.1 running stock register |
| `POST` | `/creditnote` | `admin`, `manager` | Issue Mushak 6.7 (increases stock, decreasing adjustment) |
| `POST` | `/debitnote` | `admin`, `manager` | Issue Mushak 6.8 (decreases stock, increasing adjustment) |
| `POST` | `/form44` | `admin`, `manager` | Register damaged goods (decreases stock, increasing adjustment) |
| `POST` | `/form45` | `admin`, `manager` | Register by-products (increases stock, increasing adjustment) |
| `POST` | `/treasury` | `admin`, `manager` | Record TR challan payment for tax period |
| `POST` | `/adjustment` | `admin`, `manager` | Record manual increasing/decreasing adjustment |

### 6.3. Reports & PDF Generation
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/report/report91/{client_id}/{monthYear}` | Full Mushak 9.1 return JSON calculation |
| `GET` | `/report/report91/pdf` | Headless Chrome render of official NBR Form 9.1 PDF |
| `GET` | `/report/report63/{client_id}/{monthYear}` | List Mushak 6.3 tax invoices |
| `GET` | `/report/report63/view/{sale_id}` | Detailed Mushak 6.3 Invoice View |
| `GET` | `/report/report63/pdf` | Official Mushak 6.3 Tax Invoice PDF Export |
| `GET` | `/report/report621/{client_id}/{monthYear}` | Official Form 6.2.1 Purchase-Sale Book Report |
| `GET` | `/report/report43/{client_id}/{monthYear}` | Official Form 4.3 Input-Output Declaration Sheet |

---

## 7. Node.js Re-engineering Blueprint & Guidelines

When implementing this specification in Node.js:

1. **Transactional Consistency (ACID):**
   - Every Sale, Purchase, Credit Note, and Debit Note **MUST** execute inside an atomic database transaction (`BEGIN ... COMMIT`).
   - If stock update fails or Form 6.2.1 insertion encounters a conflict, the entire transaction must roll back.
2. **Numeric Precision:**
   - Never use standard IEEE-754 floating-point numbers for money, tax values, or stock.
   - Use `decimal.js`, `bignumber.js`, or Prisma's native `Decimal` type to prevent fractional penny drift.
3. **Date & Timezone Standards:**
   - All tax period, entry date, and invoice dates must be strictly bound to Bangladesh Standard Time (`Asia/Dhaka`, UTC+6).
   - Months must always follow `YYYY-MM` format.
4. **Proxy & Security Protocols:**
   - When deployed behind Cloudflare / Traefik / Nginx, ensure the HTTP application inspects `x-forwarded-proto` and enforces `HTTPS` across all generated absolute URLs to prevent Mixed Content security blocks.
5. **Headless Browser PDF Generation:**
   - Run a pooled Puppeteer instance (`puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] })`) rather than spawning a new Chrome process on every PDF request to ensure high throughput.
6. **Strict 100% UI Preservation Guarantee (Zero UI Modification):**
   - The entire frontend codebase located in `resources/js` (Vue 3 Composition API, Bootstrap 5, Vite, dark/light theme, layouts, modals, filters, alerts, and datatables) **MUST REMAIN 100% UNTOUCHED AND IDENTICAL**.
   - Not a single CSS rule, component structure, color scheme, or user interaction should be altered.
   - The Node.js backend must feed the exact same Inertia.js JSON contract / props payload (`component`, `props: { data, search, errors }`, `url`, `version`) to the existing Vue 3 components so the end-user sees zero visual or behavioral change.

---
*End of VATMATE Specification.*
