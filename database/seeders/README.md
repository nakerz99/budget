# Database Seeders

## Available Seeders

### 1. AdminUserSeeder
Creates the default admin user for the application.
- Username: admin
- PIN: 123456

### 2. SampleDataSeeder
Creates sample data for testing the application with various transactions, budgets, and bills.
- Username: testuser
- PIN: 123456

### 3. FinancialPlanSeeder
Creates a comprehensive financial plan based on real budget data:
- Username: planner
- PIN: 123456

#### Financial Plan Details:
**Monthly Income: ₱227,000.00**
- Hammerulo: ₱98,000 (paid on 5th and 20th)
- Mile Marker: ₱98,000 (paid on 30th)
- Remotify: ₱20,000 (paid on 30th)
- PisoNet: ₱11,000 (paid on 30th)

**Monthly Expenses: ₱151,434.57**
- Loans & Debt: ₱54,004.57
- Food & Groceries: ₱21,700.00
- Utilities: ₱11,350.00
- Baby Necessities: ₱6,600.00
- Pets: ₱4,000.00
- Household: ₱10,500.00
- Insurance: ₱2,000.00
- Credit Cards: ₱22,000.00
- Personal & Leisure: ₱15,000.00
- Subscriptions: ₱4,280.00

**Expected Monthly Savings: ₱75,565.43**

## Running Seeders

### Run all seeders:
```bash
php artisan db:seed
```

### Run specific seeder:
```bash
php artisan db:seed --class=FinancialPlanSeeder
```

### Fresh migration with seeds:
```bash
php artisan migrate:fresh --seed
```

## Notes
- The FinancialPlanSeeder creates realistic financial data including:
  - Income sources with proper payment schedules
  - All recurring bills with due dates
  - Budget allocations for each expense category
  - Sample transactions to show activity
- All amounts are in Philippine Pesos (₱)
- Bills are set up as recurring monthly payments
- The seeder automatically calculates and updates account balances
