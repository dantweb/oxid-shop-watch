# Shop Watch - E2E Testing API for OXID eShop 7

A standalone OXID eShop 7.4+ module that provides an HTTP API for database state verification in E2E tests.

## Features

- Secure assumption-based database queries for test verification
- Two-factor authentication (IP whitelist + API key)
- SQL injection prevention with prepared statements
- Operator strategies (equality, comparison, LIKE, NULL checks)
- Comprehensive audit logging
- Rate limiting support
- CIDR notation for IP ranges

## Package Information

- **Package:** `dantweb/oxid-shop-watch`
- **Namespace:** `Dantweb\OxidShopWatch\`
- **Module ID:** `dantweb_shop_watch`

## Installation

### Via Composer (recommended)

```bash
composer require dantweb/oxid-shop-watch
```

### Manual Installation

1. Clone the repository into your OXID extensions directory:
```bash
cd /path/to/oxid/source/extensions
git clone https://github.com/dantweb/oxid-shop-watch.git shop-watch
```

2. Install the module:
```bash
bin/oe-console oe:module:install extensions/shop-watch
bin/oe-console oe:module:activate dantweb_shop_watch
```

## Configuration

Configure the module in the OXID admin panel under **Extensions > Modules > Shop Watch**.

### Settings

| Setting | Type | Description |
|---------|------|-------------|
| `shopwatchEnabled` | bool | Enable/disable the API |
| `shopwatchAllowedHosts` | array | List of allowed hosts with IP and API key |
| `shopwatchRateLimitEnabled` | bool | Enable rate limiting |
| `shopwatchRateLimitPerMinute` | int | Requests per minute limit |

### Allowed Hosts Format

```json
[
  {
    "ip": "192.168.1.100",
    "api_key": "a1b2c3d4e5f6...64 hex chars",
    "description": "CI Server"
  },
  {
    "ip": "10.0.0.0/24",
    "api_key": "f6e5d4c3b2a1...64 hex chars",
    "description": "Internal Network"
  }
]
```

## API Usage

### Endpoint

```
POST /index.php?cl=shopwatch_assumption&fnc=assume
```

### Request Format

```json
{
  "assumption": {
    "table_name.FIELD_NAME": "expected_value",
    "where": {
      "OXID": "record-id"
    },
    "operator": "=="
  }
}
```

### Response Format

```json
{
  "assumption": true,
  "query_time_ms": 12.5,
  "matched_rows": 1,
  "actual_value": "committed",
  "expected_value": "committed"
}
```

### Supported Operators

| Operator | Description |
|----------|-------------|
| `==` | Equal (loose comparison) |
| `!=` | Not equal |
| `>` | Greater than |
| `<` | Less than |
| `>=` | Greater than or equal |
| `<=` | Less than or equal |
| `%like%` | Contains (case-insensitive) |
| `like%` | Starts with |
| `%like` | Ends with |
| `IS NULL` | Is null |
| `IS NOT NULL` | Is not null |

## Examples

### cURL

```bash
# Check if an order has a specific status
curl -X POST "https://your-shop.com/index.php?cl=shopwatch_assumption&fnc=assume" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your64charApiKey..." \
  -H "X-Request-ID: test-$(date +%s)" \
  -d '{
    "assumption": {
      "oxorder.OXTRANSSTATUS": "OK",
      "where": {
        "OXID": "order-12345"
      },
      "operator": "=="
    }
  }'

# Check payment contract state
curl -X POST "https://your-shop.com/index.php?cl=shopwatch_assumption&fnc=assume" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your64charApiKey..." \
  -d '{
    "assumption": {
      "osc_payment_contract.OXSTATE": "committed",
      "where": {
        "OXORDERID": "order-12345"
      }
    }
  }'

# Check order total is greater than 100
curl -X POST "https://your-shop.com/index.php?cl=shopwatch_assumption&fnc=assume" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your64charApiKey..." \
  -d '{
    "assumption": {
      "oxorder.OXTOTALORDERSUM": 100,
      "where": {
        "OXID": "order-12345"
      },
      "operator": ">="
    }
  }'
```

### Playwright (TypeScript)

```typescript
import { test, expect } from '@playwright/test';

// Configuration
const SHOP_WATCH_URL = process.env.SHOP_URL || 'https://your-shop.com';
const SHOP_WATCH_API_KEY = process.env.SHOP_WATCH_API_KEY || 'your64charApiKey...';

// Helper function to make ShopWatch assertions
async function assumeDatabase(
  request: any,
  table: string,
  field: string,
  expectedValue: any,
  whereClause: Record<string, any>,
  operator: string = '=='
): Promise<boolean> {
  const response = await request.post(
    `${SHOP_WATCH_URL}/index.php?cl=shopwatch_assumption&fnc=assume`,
    {
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': SHOP_WATCH_API_KEY,
        'X-Request-ID': `playwright-${Date.now()}`
      },
      data: {
        assumption: {
          [`${table}.${field}`]: expectedValue,
          where: whereClause,
          operator: operator
        }
      }
    }
  );

  const result = await response.json();
  return result.assumption === true;
}

// Helper to wait for database state
async function waitForDatabaseState(
  request: any,
  table: string,
  field: string,
  expectedValue: any,
  whereClause: Record<string, any>,
  options: { timeout?: number; interval?: number; operator?: string } = {}
): Promise<void> {
  const { timeout = 30000, interval = 500, operator = '==' } = options;
  const startTime = Date.now();

  while (Date.now() - startTime < timeout) {
    const isMatch = await assumeDatabase(
      request,
      table,
      field,
      expectedValue,
      whereClause,
      operator
    );

    if (isMatch) {
      return;
    }

    await new Promise(resolve => setTimeout(resolve, interval));
  }

  throw new Error(
    `Timeout waiting for ${table}.${field} to equal ${expectedValue}`
  );
}

// Example Tests
test.describe('Order E2E Tests with ShopWatch', () => {

  test('should create order and verify database state', async ({ page, request }) => {
    // 1. Complete checkout flow
    await page.goto('/');
    await page.click('[data-testid="add-to-cart"]');
    await page.goto('/checkout');
    // ... complete checkout steps

    // 2. Get order ID from success page
    const orderId = await page.locator('[data-order-id]').getAttribute('data-order-id');

    // 3. Verify order was created in database
    const orderCreated = await assumeDatabase(
      request,
      'oxorder',
      'OXID',
      orderId,
      { OXID: orderId },
      '!='
    );
    expect(orderCreated).toBe(true);

    // 4. Wait for payment to be processed
    await waitForDatabaseState(
      request,
      'oxorder',
      'OXTRANSSTATUS',
      'OK',
      { OXID: orderId },
      { timeout: 60000 }
    );

    // 5. Verify order total
    const totalCorrect = await assumeDatabase(
      request,
      'oxorder',
      'OXTOTALORDERSUM',
      50,
      { OXID: orderId },
      '>='
    );
    expect(totalCorrect).toBe(true);
  });

  test('should verify payment contract state after checkout', async ({ page, request }) => {
    // ... checkout flow ...
    const orderId = 'order-12345';

    // Wait for payment contract to be committed
    await waitForDatabaseState(
      request,
      'osc_payment_contract',
      'OXSTATE',
      'committed',
      { OXORDERID: orderId },
      { timeout: 30000 }
    );

    // Verify payment capture
    const isCaptured = await assumeDatabase(
      request,
      'osc_payment_transaction',
      'TXTYPE',
      'capture',
      { OXCONTRACTID: 'contract-id' }
    );
    expect(isCaptured).toBe(true);
  });

});

// Playwright Fixture for easier usage
test.describe('Using Playwright Fixtures', () => {
  // Define a fixture for ShopWatch
  const shopWatchFixture = test.extend<{
    shopWatch: {
      assume: typeof assumeDatabase;
      waitFor: typeof waitForDatabaseState;
    };
  }>({
    shopWatch: async ({ request }, use) => {
      await use({
        assume: (table, field, expected, where, op) =>
          assumeDatabase(request, table, field, expected, where, op),
        waitFor: (table, field, expected, where, opts) =>
          waitForDatabaseState(request, table, field, expected, where, opts),
      });
    },
  });

  shopWatchFixture('verify user creation', async ({ page, shopWatch }) => {
    const userId = 'user-123';

    // Verify user exists
    const userExists = await shopWatch.assume(
      'oxuser',
      'OXACTIVE',
      '1',
      { OXID: userId }
    );
    expect(userExists).toBe(true);
  });
});
```

### Playwright Configuration

Add to your `playwright.config.ts`:

```typescript
import { defineConfig } from '@playwright/test';

export default defineConfig({
  use: {
    baseURL: process.env.SHOP_URL || 'https://your-shop.com',
    extraHTTPHeaders: {
      'X-API-Key': process.env.SHOP_WATCH_API_KEY || '',
    },
  },
});
```

### Environment Variables

```bash
# .env or shell export
export SHOP_URL=https://your-shop.com
export SHOP_WATCH_API_KEY=a1b2c3d4e5f6g7h8i9j0... # 64 hex characters
```

## Generating API Keys

API keys must be 64 hexadecimal characters. Generate one with:

```bash
# Using OpenSSL
openssl rand -hex 32

# Using PHP
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

# Using Python
python3 -c "import secrets; print(secrets.token_hex(32))"
```

## Security Considerations

1. **IP Whitelisting**: Only allow specific IPs or CIDR ranges
2. **API Key Rotation**: Regularly rotate API keys
3. **Rate Limiting**: Enable rate limiting in production
4. **TLS**: Always use HTTPS in production
5. **Disable in Production**: Only enable this module in test environments

## Testing

```bash
# Run all tests
./bin/pre-commit-check.sh --full

# Run unit tests only
./bin/pre-commit-check.sh

# Run specific test file
docker compose exec php vendor/bin/phpunit -c extensions/shop-watch/tests/phpunit.xml \
  extensions/shop-watch/tests/Unit/ValueObject/AssumptionRequestTest.php
```

## License

CC - Creative Commons

## Author

Daniil Tkachev - [dantweb](https://github.com/dantweb)
