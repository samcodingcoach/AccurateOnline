# Payment Term API Documentation

## Overview
API untuk mengambil daftar syarat pembayaran (payment terms) dari sistem Accurate.

## Endpoint Information
- **File**: `/payterm/list_term.php`
- **HTTP Method**: `GET`
- **Scope Required**: `payment_term_view`
- **Accurate Endpoint**: `/accurate/api/payment-term/list.do`

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 25 | Number of records per page (max: 100) |
| `page` | integer | No | 1 | Page number |

## Request Example

```bash
# Get default payment terms (25 records, page 1)
GET /payterm/list_term.php

# Get 10 payment terms from page 1
GET /payterm/list_term.php?limit=10

# Get 10 payment terms from page 2
GET /payterm/list_term.php?limit=10&page=2
```

## Response Format

### Success Response
```json
{
    "success": true,
    "message": "Data payment term berhasil diambil",
    "data": {
        "paymentTerms": {
            "d": [
                {
                    "id": 1,
                    "name": "Cash",
                    "description": "Cash payment",
                    "dueDays": 0,
                    "discountDays": 0,
                    "discountRate": 0,
                    "suspended": false,
                    "createDate": "2024-01-01"
                },
                {
                    "id": 2,
                    "name": "NET 30",
                    "description": "30 days payment term",
                    "dueDays": 30,
                    "discountDays": 0,
                    "discountRate": 0,
                    "suspended": false,
                    "createDate": "2024-01-01"
                }
            ],
            "sp": {
                "pageCount": 1,
                "hasMore": false
            }
        },
        "pagination": {
            "current_page": 1,
            "per_page": 25,
            "total": 1,
            "has_more": false
        },
        "meta": {
            "scope_required": "payment_term_view",
            "http_code": 200,
            "endpoint": "/accurate/api/payment-term/list.do"
        }
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Gagal mengambil data payment term: [error_message]",
    "data": null
}
```

## Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique identifier for payment term |
| `name` | string | Payment term name |
| `description` | string | Payment term description |
| `dueDays` | integer | Number of days until payment is due |
| `discountDays` | integer | Number of days for early payment discount |
| `discountRate` | float | Discount rate for early payment (percentage) |
| `suspended` | boolean | Whether the payment term is suspended |
| `createDate` | string | Date when payment term was created |

## Usage in Sales Order

This API is used to populate payment term options in sales order forms. The `id` field should be used as the value when storing payment terms.

## Error Handling

The API handles the following error scenarios:
1. **Method not allowed**: Returns error if method is not GET
2. **API connection errors**: Returns error if unable to connect to Accurate API
3. **Authentication errors**: Returns error if invalid session or token
4. **Validation errors**: Returns error if invalid parameters

## Dependencies

- Requires valid Accurate API session (`X-Session-ID`)
- Requires valid access token (`Authorization: Bearer`)
- Requires `payment_term_view` scope permission
