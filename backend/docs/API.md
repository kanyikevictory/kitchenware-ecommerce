# Kitchen Store REST API

API version: `v1`  
Local base URL: `http://localhost:8000/api/v1`  
Content type: `application/json`, except multipart image uploads.

## Conventions

Send these headers with JSON requests:

```http
Accept: application/json
Content-Type: application/json
```

Authenticated requests also require:

```http
Authorization: Bearer <sanctum-token>
```

Successful single-resource responses use a `data` object. Collection endpoints use a `data` array and Laravel pagination metadata. Monetary values are decimal strings and use `UGX` unless a response says otherwise. Dates are ISO-8601 strings.

## Authentication

### Register

```http
POST /auth/register
```

```json
{
  "name": "Jane Customer",
  "email": "jane@example.com",
  "phone": "+256700123456",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "device_name": "React storefront"
}
```

Response: `201 Created`

```json
{
  "message": "Account created. Please verify your email address.",
  "data": {
    "user": {
      "id": 12,
      "name": "Jane Customer",
      "email": "jane@example.com",
      "email_verified_at": null,
      "role": { "id": 3, "name": "Customer", "slug": "customer" },
      "permissions": []
    },
    "token": "1|plain-text-sanctum-token",
    "token_type": "Bearer"
  }
}
```

### Login

```http
POST /auth/login
```

```json
{
  "email": "jane@example.com",
  "password": "SecurePassword123!",
  "device_name": "React storefront"
}
```

Returns the same `user`, `token`, and `token_type` structure as registration.

### Authentication endpoints

| Method | Endpoint | Authentication | Description |
|---|---|---:|---|
| POST | `/auth/register` | No | Create a customer account and token |
| POST | `/auth/login` | No | Create a Sanctum token |
| POST | `/auth/logout` | Bearer | Revoke the current token |
| POST | `/auth/forgot-password` | No | Queue a reset email without exposing account existence |
| POST | `/auth/reset-password` | No | Reset a password and revoke existing tokens |
| GET | `/auth/email/verify/{id}/{hash}` | Signed URL | Verify an email address |
| POST | `/auth/email/verification-notification` | Bearer | Queue another verification email |
| GET | `/me` | Bearer | Return the user, role, and permissions |

Reset request:

```json
{
  "email": "jane@example.com",
  "token": "token-from-email",
  "password": "NewSecurePassword123!",
  "password_confirmation": "NewSecurePassword123!"
}
```

## Public catalogue

| Method | Endpoint | Description |
|---|---|---|
| GET | `/health` | API health response |
| GET | `/categories` | Active category search and pagination |
| GET | `/categories/{slug}` | Active category details and children |
| GET | `/products` | Active product catalogue |
| GET | `/products/{slug}` | Active product details |
| GET | `/products/{slug}/reviews` | Approved product reviews and rating summary |
| GET | `/search` | Global product/category search |

Category query parameters:

```text
search, parent_id, roots_only, per_page, page
```

Product query parameters:

```text
search, category_id, brand, min_price, max_price,
in_stock, featured, sort, per_page, page
```

Product sort values:

```text
newest, price_asc, price_desc, name_asc, name_desc
```

Global search example:

```http
GET /search?q=pan&type=all&category_id=2&in_stock=true&sort=price_asc
```

Global search uses separate `product_page` and `category_page` parameters. Its nested product and category blocks each contain `data` and `meta`.

## Profile and addresses

These routes require a bearer token. Address routes additionally require verified email.

| Method | Endpoint | Description |
|---|---|---|
| GET | `/profile` | View profile |
| PUT | `/profile` | Update name, email, and phone |
| PUT | `/profile/password` | Change password and revoke other device tokens |
| GET | `/shipping-addresses` | List owned addresses |
| POST | `/shipping-addresses` | Create an address |
| GET | `/shipping-addresses/{id}` | View an owned address |
| PUT/PATCH | `/shipping-addresses/{id}` | Update an owned address |
| DELETE | `/shipping-addresses/{id}` | Delete an owned address |

Profile update:

```json
{
  "name": "Jane Customer",
  "email": "jane.new@example.com",
  "phone": "+256700123456"
}
```

Changing email clears verification and queues a new verification message.

Password change:

```json
{
  "current_password": "SecurePassword123!",
  "password": "NewSecurePassword123!",
  "password_confirmation": "NewSecurePassword123!"
}
```

Address request:

```json
{
  "label": "Home",
  "first_name": "Jane",
  "last_name": "Customer",
  "phone": "+256700123456",
  "country": "Uganda",
  "state": "Central",
  "city": "Kampala",
  "address_line_1": "1 Kitchen Street",
  "address_line_2": null,
  "postal_code": "00000",
  "is_default": true
}
```

## Cart

Verified customer routes:

| Method | Endpoint | Description |
|---|---|---|
| GET | `/cart` | Return cart items and totals |
| DELETE | `/cart` | Clear the cart |
| POST | `/cart/items` | Add or merge a product |
| PATCH | `/cart/items/{cartItem}` | Replace line quantity |
| DELETE | `/cart/items/{cartItem}` | Remove a line |

Add item:

```json
{ "product_id": 24, "quantity": 2 }
```

Update item:

```json
{ "quantity": 3 }
```

Cart totals are server calculated. `subtotal` is the regular-price total, `discount_total` is savings, and `grand_total` is the payable total. A line cannot exceed available stock or 100 units.

## Wishlist

| Method | Endpoint | Description |
|---|---|---|
| GET | `/wishlist` | Return the current wishlist |
| POST | `/wishlist/items` | Add a product idempotently |
| DELETE | `/wishlist/items/{wishlistItem}` | Remove an owned entry |

```json
{ "product_id": 24 }
```

Out-of-stock products may be wishlisted. Removed products remain visible as unavailable until the entry is deleted.

## Coupons and checkout

### Preview coupon

```http
POST /coupons/validate
```

```json
{ "coupon_code": "SAVE10" }
```

```json
{
  "message": "Coupon is valid.",
  "data": {
    "code": "SAVE10",
    "type": "percentage",
    "value": "10.00",
    "eligible_amount": "200000.00",
    "discount_amount": "20000.00",
    "total_after_discount": "180000.00"
  }
}
```

### Checkout

```http
POST /checkout
```

```json
{
  "shipping_address_id": 4,
  "coupon_code": "SAVE10",
  "notes": "Leave at reception"
}
```

Response: `201 Created`. Checkout revalidates prices, product availability, stock, coupon eligibility, and address ownership. It creates snapshots, reduces stock, increments coupon use, and clears the cart atomically.

## Orders

| Method | Endpoint | Description |
|---|---|---|
| GET | `/orders` | Paginated order history |
| GET | `/orders/{order}` | Owned order details |
| POST | `/orders/{order}/cancel` | Cancel a pending or confirmed order |

Statuses:

```text
pending -> confirmed -> processing -> shipped -> delivered
pending/confirmed/processing -> cancelled
```

Customer cancellation is limited to pending or confirmed orders. Cancellation restores stock once.

## Payments

| Method | Endpoint | Description |
|---|---|---|
| GET | `/orders/{order}/payments` | List payments for an owned order |
| POST | `/orders/{order}/payments` | Prepare a payment |

Cash on delivery:

```json
{ "method": "cash_on_delivery" }
```

Mobile Money:

```json
{
  "method": "mobile_money",
  "phone": "+256700123456"
}
```

Mobile Money currently returns `202 Accepted` with `pending_configuration`. It creates a local reference but does not contact or claim success from a provider. COD returns `201 Created` with `pending` status.

## Reviews

| Method | Endpoint | Description |
|---|---|---|
| POST | `/products/{productId}/reviews` | Submit a review after a delivered purchase |
| PUT | `/reviews/{review}` | Edit an owned review and return it to moderation |
| DELETE | `/reviews/{review}` | Soft-delete an owned review |

```json
{
  "rating": 5,
  "title": "Excellent cookware",
  "comment": "This product performed very well in my kitchen."
}
```

Ratings must be 1–5. Only approved reviews appear publicly.

## Admin API

Admin routes require bearer authentication, `admin.access`, and the module permission enforced by policies.

| Method | Endpoint | Permission | Description |
|---|---|---|---|
| GET | `/admin/dashboard` | `dashboard.view` | Dashboard metrics; optional `year` |
| GET | `/admin/users` | `users.view` | Search and paginate users |
| PATCH | `/admin/users/{user}/status` | `users.update-status` | Activate/deactivate user |
| GET/POST | `/admin/categories` | `categories.manage` | List/create categories |
| GET/PUT/PATCH/DELETE | `/admin/categories/{category}` | `categories.manage` | Category CRUD |
| GET/POST | `/admin/products` | `products.manage` | List/create products |
| GET/PUT/PATCH/DELETE | `/admin/products/{product}` | `products.manage` | Product CRUD |
| DELETE | `/admin/products/{product}/images/{image}` | `products.manage` | Delete image |
| PATCH | `/admin/products/{product}/images/{image}/primary` | `products.manage` | Set primary image |
| GET | `/admin/orders` | `orders.manage` | Search/filter orders |
| GET | `/admin/orders/{order}` | `orders.manage` | Order details |
| PATCH | `/admin/orders/{order}/status` | `orders.manage` | Advance/cancel order |
| PATCH | `/admin/payments/{payment}/cash-on-delivery` | `payments.manage` | Complete/cancel COD |
| GET | `/admin/reviews` | `reviews.manage` | Moderation queue |
| PATCH | `/admin/reviews/{review}/moderation` | `reviews.manage` | Approve/unapprove review |
| GET/POST | `/admin/coupons` | `coupons.manage` | List/create coupons |
| GET/PUT/PATCH/DELETE | `/admin/coupons/{coupon}` | `coupons.manage` | Coupon CRUD |

User status:

```json
{ "status": "inactive" }
```

Order status:

```json
{ "status": "processing" }
```

Review moderation:

```json
{ "is_approved": true }
```

COD settlement:

```json
{ "status": "completed" }
```

COD can only be completed after delivery.

### Category multipart request

```text
name: Cookware
parent_id: 1
description: Premium cookware
sort_order: 1
is_active: 1
image: <jpeg|png|webp>
```

Category images: maximum 4 MB and minimum 200×200.

### Product multipart request

```text
category_id: 1
name: Premium Frying Pan
description: Durable cookware
price: 150000
discount_price: 125000
brand: Kitchen Pro
sku: PAN-001
stock_quantity: 25
status: active
is_featured: 1
images[]: <image>
```

Product images: maximum eight, 6 MB each, minimum 300×300. Images are resized to a maximum 1600-pixel edge and converted to WebP. For file updates, send `POST` multipart data with `_method=PUT` because PHP does not reliably parse multipart bodies sent directly with PUT.

### Coupon request

```json
{
  "code": "LAUNCH20",
  "type": "percentage",
  "value": 20,
  "minimum_order_amount": 100000,
  "usage_limit": 500,
  "starts_at": "2026-07-01T00:00:00Z",
  "expires_at": "2026-07-31T23:59:59Z",
  "is_active": true
}
```

Coupon type is `percentage` or `fixed`. Percentage values cannot exceed 100.

## Pagination

Typical collection response:

```json
{
  "data": [],
  "links": {
    "first": "http://localhost:8000/api/v1/products?page=1",
    "last": "http://localhost:8000/api/v1/products?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/v1/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 42
  }
}
```

## Errors

### Validation — `422 Unprocessable Entity`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field must be a valid email address."],
    "quantity": ["Only 3 units are currently available."]
  }
}
```

### Unauthenticated — `401 Unauthorized`

```json
{ "message": "Unauthenticated." }
```

### Forbidden — `403 Forbidden`

```json
{ "message": "This action is unauthorized." }
```

### Not found — `404 Not Found`

```json
{ "message": "No query results for model." }
```

### Rate limited — `429 Too Many Requests`

```json
{ "message": "Too Many Attempts." }
```

All `/api/*` exceptions are rendered as JSON. Clients should branch on HTTP status and use `errors` for field-level validation messages.

## React integration

1. Register or log in and retain the returned bearer token using the frontend's secure token strategy.
2. Send `Accept: application/json` and the bearer token on protected requests.
3. Use `email_verified_at` to guide verification UX; the backend still enforces verification.
4. Use returned permission slugs to hide unavailable admin navigation; backend middleware and policies remain authoritative.
5. Treat money as decimal strings rather than JavaScript floating-point values.
6. Keep product/category pages separately when consuming global search.
7. Never calculate trusted cart, coupon, checkout, or payment totals in React.

## Operational requirements

Create the public storage link:

```bash
php artisan storage:link
```

Run queued email notifications:

```bash
php artisan queue:work --queue=notifications,default --tries=3
```

Configure `APP_URL`, `FRONTEND_URL`, Sanctum domains, CORS origins, database, queue, and mail values in `.env`.
