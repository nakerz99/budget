# Database Seeders Documentation

This document describes the available database seeders and how to use them.

## Available Seeders

### 1. **AdminUserSeeder**
Creates the default admin user for the system.
- **Username**: admin
- **PIN**: 123456
- **Currency**: PHP
- **Timezone**: Asia/Manila

### 2. **FinancialPlanSeeder**
Creates a user with your specific financial plan data:
- **Username**: planner
- **PIN**: 123456
- **Monthly Income**: ₱227,000.00
- **Monthly Expenses**: ₱151,434.57
- **Monthly Savings**: ₱75,565.43
- Includes all your specified categories, bills, budgets, and savings goals

### 3. **RealisticDataSeeder**
Creates a comprehensive realistic dataset for testing:
- **Username**: juandelacruz
- **PIN**: 789456
- **Features**:
  - 24 categories (income & expense)
  - 6 bank accounts (BPI, BDO, Metrobank, GCash, PayMaya, Emergency Fund)
  - 6 months of transaction history
  - Realistic daily expenses (groceries, transport, dining, etc.)
  - 8 recurring bills
  - Monthly budgets for current + 3 future months
  - 5 savings goals (Emergency Fund, Car, Vacation, Education, Renovation)

### 4. **DemoDataSeeder**
Creates minimal demo data for quick testing:
- **Username**: demo
- **PIN**: 000000
- Basic categories and simple transactions
- Suitable for quick demos or initial exploration

## Usage

### Run All Seeders (Fresh Install)
```bash
php artisan migrate:fresh --seed
```
This will:
1. Drop all existing tables
2. Run all migrations
3. Seed with AdminUserSeeder, FinancialPlanSeeder, and RealisticDataSeeder

### Run Individual Seeders
```bash
# Run only the admin seeder
php artisan db:seed --class=AdminUserSeeder

# Run only the financial plan seeder
php artisan db:seed --class=FinancialPlanSeeder

# Run only the realistic data seeder
php artisan db:seed --class=RealisticDataSeeder

# Run only the demo seeder
php artisan db:seed --class=DemoDataSeeder
```

### Custom Seeding Combinations

Edit `database/seeders/DatabaseSeeder.php` to customize which seeders run:

```php
public function run()
{
    $this->call([
        AdminUserSeeder::class,
        // Choose one of these:
        // FinancialPlanSeeder::class,    // Your specific plan
        // RealisticDataSeeder::class,    // Comprehensive test data
        // DemoDataSeeder::class,         // Minimal demo data
    ]);
}
```

## Data Overview

### RealisticDataSeeder Details

**Income Sources**:
- Hammerulo Salary: ₱98,000/month (5th & 20th)
- Remotify: ₱20,000/month (30th)
- PisoNet Business: ₱11,000/month (30th)

**Expense Categories**:
- Housing: Rent, Loans (NSJBI, Pag-IBIG)
- Utilities: Electric, Water, Internet, Phone
- Daily Living: Groceries, Transportation, Dining
- Family: Baby expenses, Pet care
- Financial: Credit cards, Insurance, Savings
- Lifestyle: Shopping, Entertainment, Personal care

**Bills Created**:
1. Condo Rent: ₱25,000 (1st)
2. NSJBI Loan: ₱20,866.57 (20th)
3. Pag-IBIG Loan: ₱31,048 (30th)
4. Meralco: ₱9,000 (15th)
5. PLDT Internet: ₱2,000 (15th)
6. Unionbank CC: ₱20,000 (10th)
7. Health Insurance: ₱2,000 (5th)
8. Car Insurance: ₱15,000 (Annual)

**Savings Goals**:
1. Emergency Fund: ₱900,000 target
2. Car Down Payment: ₱200,000 target
3. Europe Vacation: ₱300,000 target
4. Baby Education: ₱500,000 target
5. Home Renovation: ₱150,000 target

## Notes

- All amounts are in Philippine Peso (₱)
- Transaction dates are realistic (weekday patterns, bill due dates)
- Expenses vary to simulate real spending patterns
- Account balances update automatically based on transactions
- All users are pre-approved for immediate login

## Testing Different Scenarios

1. **Test Budget Tracking**: Use `juandelacruz` account - has full budget data
2. **Test Bill Management**: Check upcoming bills and payment tracking
3. **Test Reports**: 6 months of data available for analysis
4. **Test Savings Goals**: Multiple goals at different progress levels
5. **Test Your Plan**: Use `planner` account with your exact financial data
