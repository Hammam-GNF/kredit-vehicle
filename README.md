# Simple Credit Installment System (Laravel)

A simple backend application to calculate credit installments, payment schedules, overdue installments, and penalty calculations based on given business rules.

This project is built to answer a technical assessment focused on **data modeling, business logic, and SQL querying**, not UI/UX.

---

## 📌 Problem Overview

Pak Sugus purchases a car with the following terms:

- Car Price (OTR): Rp 240,000,000
- Down Payment: 20%
- Installment Period: 5 years (60 months)
- Monthly installment is fixed
- Penalty: 0.1% per day from unpaid installment amount
- Reporting date reference: **14 August 2024**

---

## 🧩 Features Implemented

- Contract creation with OTR and Down Payment
- Automatic generation of installment schedule
- Monthly installment calculation
- Payment recording (supports partial & multiple payments)
- Outstanding installment calculation
- Overdue installment detection
- Late penalty calculation based on days overdue
- SQL queries for reporting needs

---

## 🗂 Database Structure (Core Tables)

### kontrak
| Field | Description |
|------|------------|
| kontrak_no | Contract number |
| client_name | Client name |
| otr | Car price |
| down_payment | Down payment value |
| tenor_bulan | Installment period (months) |

### jadwal_angsuran
| Field | Description |
|------|------------|
| kontrak_no | Contract reference |
| angsuran_ke | Installment sequence |
| angsuran_per_bulan | Monthly installment |
| tanggal_jatuh_tempo | Due date |

### pembayaran
| Field | Description |
|------|------------|
| jadwal_angsuran_id | Related installment |
| jumlah_bayar | Paid amount |
| tanggal_bayar | Payment date |

---

## 📐 Business Logic Notes

- All monetary values are stored using `DECIMAL(15,2)` to avoid floating-point precision issues.
- Payment calculations use **numeric comparison**, not strict float comparison.
- Installment payment status is derived from the sum of related payments.
- Late penalty is calculated per installment, per day overdue.

---

## 🧮 Key SQL Queries

### 1️⃣ Total Installments Due as of 14 August 2024

```sql
SELECT 
    k.kontrak_no,
    k.client_name,
    SUM(j.angsuran_per_bulan) AS total_angsuran_jatuh_tempo
FROM kontrak k
JOIN jadwal_angsuran j ON j.kontrak_no = k.kontrak_no
WHERE j.tanggal_jatuh_tempo <= '2024-08-14'
GROUP BY k.kontrak_no, k.client_name;

### 2️⃣ Late Penalty Calculation

SELECT 
    k.kontrak_no,
    k.client_name,
    j.angsuran_ke,
    DATEDIFF('2024-08-14', j.tanggal_jatuh_tempo) AS hari_keterlambatan,
    (j.angsuran_per_bulan * 0.001 * 
     DATEDIFF('2024-08-14', j.tanggal_jatuh_tempo)) AS total_denda
FROM jadwal_angsuran j
JOIN kontrak k ON k.kontrak_no = j.kontrak_no
LEFT JOIN pembayaran p ON p.jadwal_angsuran_id = j.id
WHERE j.tanggal_jatuh_tempo < '2024-08-14'
GROUP BY j.id
HAVING COALESCE(SUM(p.jumlah_bayar), 0) < j.angsuran_per_bulan;

---

🛠 Tech Stack

Laravel 10

MySQL

PHP 8+

REST API architecture

📎 Notes for Reviewer

This project focuses on correctness of financial calculations and data integrity.

UI layer is intentionally excluded as it was not required by the problem statement.

The backend is designed to be reusable for any frontend implementation.

---

🚀 How to Run

git clone <repository-url>
cd project-folder
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

---

