# RLNKS PHP SDK

Official PHP SDK for the RLNKS API - Smart redirect and image routing.

## Requirements

- PHP 8.1 or higher
- Guzzle HTTP client

## Installation

Install via Composer:

```bash
composer require rlnks/sdk
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Rlnks\Client;

// Initialize the client with your API key
$client = new Client('rlnks_your_api_key');

// List your decision trees
$trees = $client->trees->list();

foreach ($trees as $tree) {
    echo $tree->name . ' - ' . $tree->getImageUrl() . "\n";
}
```

## Authentication

The SDK supports API key authentication. Your API key can be found in your RLNKS dashboard.

```php
// API keys start with 'rlnks_'
$client = new Client('rlnks_your_api_key');

// You can also specify custom options
$client = new Client('rlnks_your_api_key', [
    'base_url' => 'https://api.rlnks.com',  // Custom base URL
    'timeout' => 30,                          // Request timeout in seconds
    'connect_timeout' => 10,                  // Connection timeout
]);
```

## Decision Trees

Decision trees are the core of RLNKS. They define conditional logic for serving different images or redirecting to different URLs.

### List Trees

```php
// List all trees
$trees = $client->trees->list();

// Filter by type
$imageTrees = $client->trees->list(['type' => 'image']);
$redirectTrees = $client->trees->list(['type' => 'redirect']);

// Filter by status
$activeTrees = $client->trees->list(['status' => 'active']);

// Search and pagination
$trees = $client->trees->list([
    'search' => 'campaign',
    'sort_by' => 'name',
    'sort_dir' => 'asc',
    'per_page' => 50,
    'page' => 1,
]);

// Iterate through results
foreach ($trees as $tree) {
    echo "{$tree->name}: {$tree->total_requests} requests\n";
}

// Check pagination
if ($trees->hasNextPage()) {
    echo "More trees available...\n";
}
```

### Create a Tree

```php
$tree = $client->trees->create([
    'name' => 'Holiday Campaign',
    'type' => 'image',
    'description' => 'Banner images for holiday season',
    'tree_data' => [
        'root' => [
            'id' => 'node_1',
            'type' => 'condition',
            'criteria' => [
                'field' => 'device.type',
                'operator' => 'equals',
                'value' => 'mobile',
            ],
            'true_branch' => ['type' => 'output', 'id' => 'mobile_banner'],
            'false_branch' => ['type' => 'output', 'id' => 'desktop_banner'],
        ],
    ],
    'default_output' => [
        'type' => 'image',
        'image_id' => 'your-image-uuid',
    ],
]);

// Get the generated URLs
echo "Image URL: " . $tree->getImageUrl() . "\n";
echo "Short URL: " . $tree->getShortUrl() . "\n";
```

### Update a Tree

```php
$tree = $client->trees->update($treeId, [
    'name' => 'Updated Campaign Name',
    'is_active' => true,
]);
```

### Activate/Deactivate a Tree

```php
// Activate
$client->trees->activate($treeId);

// Deactivate
$client->trees->deactivate($treeId);

// Archive
$client->trees->archive($treeId);
```

### Clone a Tree

```php
$clonedTree = $client->trees->clone($treeId, [
    'name' => 'Copy of Holiday Campaign',
]);
```

### Test a Tree

```php
// Test with simulated context
$result = $client->trees->test($treeId, [
    'device' => [
        'type' => 'mobile',
        'brand' => 'Apple',
        'model' => 'iPhone 15',
    ],
    'location' => [
        'country' => 'CA',
    ],
]);

echo "Matched node: " . $result['node_id'] . "\n";
echo "Output: " . json_encode($result['output']) . "\n";
```

### Delete a Tree

```php
$client->trees->delete($treeId);
```

## Images

Upload and manage images that can be used in image-type decision trees.

### List Images

```php
// List all images
$images = $client->images->list();

// Filter by folder
$holidayImages = $client->images->list(['folder' => 'holiday2024']);

// Search
$images = $client->images->list(['search' => 'banner']);

foreach ($images as $image) {
    echo "{$image->filename} ({$image->dimensions}) - {$image->human_size}\n";
}
```

### Upload an Image

```php
// Upload from file path
$image = $client->images->upload('/path/to/banner.jpg', [
    'name' => 'Holiday Banner Mobile',
    'folder' => 'holiday2024',
]);

echo "Uploaded: " . $image->url . "\n";
echo "Thumbnail: " . $image->thumbnail_url . "\n";

// Upload from string content
$imageContent = file_get_contents('https://example.com/image.jpg');
$image = $client->images->uploadFromString($imageContent, 'downloaded.jpg', [
    'folder' => 'external',
]);
```

### Get Folders

```php
$folders = $client->images->getFolders();
// ['holiday2024', 'banners', 'products']
```

### Update Image

```php
// Rename
$image = $client->images->rename($imageId, 'New Name');

// Move to folder
$image = $client->images->moveToFolder($imageId, 'new-folder');
```

### Delete an Image

```php
$client->images->delete($imageId);
```

## Analytics

Access performance analytics for your decision trees.

### Get Tree Analytics

```php
// Get last 7 days (default)
$analytics = $client->analytics->getTreeAnalytics($treeId);

// Specific periods
$analytics = $client->analytics->getToday($treeId);
$analytics = $client->analytics->getLast7Days($treeId);
$analytics = $client->analytics->getLast30Days($treeId);

// Custom date range
$analytics = $client->analytics->getDateRange($treeId, '2024-01-01', '2024-01-31');

// Access the data
echo "Total views: " . $analytics['summary']['total_views'] . "\n";
echo "Click rate: " . $analytics['summary']['click_rate'] . "%\n";

// Timeline data
foreach ($analytics['timeline'] as $day) {
    echo "{$day['date']}: {$day['views']} views, {$day['clicks']} clicks\n";
}

// Device distribution
foreach ($analytics['devices'] as $device => $count) {
    echo "{$device}: {$count}\n";
}
```

### Get Breakdown by Dimension

```php
// Device breakdown
$breakdown = $client->analytics->getDeviceBreakdown($treeId);

// Browser breakdown
$breakdown = $client->analytics->getBrowserBreakdown($treeId);

// Country breakdown
$breakdown = $client->analytics->getCountryBreakdown($treeId);

// Output node breakdown (A/B test results)
$breakdown = $client->analytics->getOutputBreakdown($treeId);

// Custom dimension
$breakdown = $client->analytics->getBreakdown($treeId, 'os', [
    'period' => '30d',
]);
```

## Webhooks

Set up webhooks to receive real-time notifications about events in your account.

### List Webhooks

```php
$webhooks = $client->webhooks->list();

foreach ($webhooks as $webhook) {
    echo "{$webhook->name}: {$webhook->getSuccessRate()}% success rate\n";
}
```

### Create a Webhook

```php
$webhook = $client->webhooks->create([
    'name' => 'My Integration',
    'url' => 'https://example.com/webhook',
    'events' => [
        'tree.created',
        'tree.updated',
        'usage.limit_warning',
    ],
    'headers' => [
        'X-Custom-Header' => 'value',
    ],
    'timeout_seconds' => 30,
    'retry_count' => 3,
]);

// Save the secret for signature verification
$secret = $webhook->secret;
```

### Update a Webhook

```php
// Enable/disable
$client->webhooks->enable($webhookId);
$client->webhooks->disable($webhookId);

// Add events
$client->webhooks->addEvents($webhookId, ['tree.deleted']);

// Remove events
$client->webhooks->removeEvents($webhookId, ['tree.updated']);
```

### Test a Webhook

```php
$result = $client->webhooks->test($webhookId);
echo "Test delivery status: " . $result['status'] . "\n";
```

### Get Deliveries

```php
$deliveries = $client->webhooks->getDeliveries($webhookId);

foreach ($deliveries['data'] as $delivery) {
    echo "{$delivery['event']} - {$delivery['status_code']} - {$delivery['created_at']}\n";
}
```

### Regenerate Secret

```php
$result = $client->webhooks->regenerateSecret($webhookId);
$newSecret = $result['secret'];
```

## Handling Webhooks

When receiving webhooks, always verify the signature before processing:

```php
<?php

use Rlnks\Webhook\SignatureVerifier;
use Rlnks\Webhook\WebhookEvent;

$verifier = new SignatureVerifier('your_webhook_secret');

// Get the raw payload and headers
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RLNKS_SIGNATURE'] ?? '';
$timestamp = $_SERVER['HTTP_X_RLNKS_TIMESTAMP'] ?? '';

try {
    // Verify and parse the webhook
    $data = $verifier->verifyAndParse($payload, $signature, $timestamp);

    // Create an event object for easier handling
    $event = WebhookEvent::fromPayload($data);

    // Handle different event types
    switch ($event->getEventType()) {
        case WebhookEvent::TREE_CREATED:
            echo "Tree created: " . $event->getTreeName();
            break;

        case WebhookEvent::TREE_UPDATED:
            echo "Tree updated: " . $event->getTreeId();
            break;

        case WebhookEvent::USAGE_LIMIT_WARNING:
            echo "Usage warning! Current: " . $event->get('current_usage');
            break;

        case WebhookEvent::USAGE_LIMIT_REACHED:
            echo "Usage limit reached!";
            break;
    }

    // Check event categories
    if ($event->isTreeEvent()) {
        // Handle all tree events
    }

    if ($event->isUsageEvent()) {
        // Handle usage alerts
    }

    http_response_code(200);

} catch (\Rlnks\Exceptions\RlnksException $e) {
    // Invalid signature or payload
    http_response_code(401);
    echo "Webhook verification failed: " . $e->getMessage();
}
```

## Account

Manage your account settings and monitor usage.

### Get Account Info

```php
$account = $client->account->get();

echo "Name: " . $account['name'] . "\n";
echo "Plan: " . $account['plan']['name'] . "\n";
echo "Email: " . $account['email'] . "\n";
```

### Get Usage

```php
$usage = $client->account->getUsage();

echo "Requests this month: " . $usage['current_month']['requests'] . "\n";
echo "Limit: " . $usage['current_month']['limit'] . "\n";
echo "Remaining: " . $usage['current_month']['remaining'] . "\n";

// Convenience methods
echo "Usage: " . $client->account->getUsagePercentage() . "%\n";
echo "Remaining: " . $client->account->getRemainingRequests() . "\n";

if ($client->account->isOverLimit()) {
    echo "Warning: Over usage limit!\n";
}
```

### Get Plan Limits

Get a comprehensive summary of all plan limits and current usage:

```php
$limits = $client->account->getLimits();

// Plan info
echo "Plan: " . $limits['plan']['name'] . "\n";

// Request limits (monthly)
echo "Requests: " . $limits['requests']['used'] . "/" . $limits['requests']['limit'] . "\n";
echo "Remaining: " . $limits['requests']['remaining'] . "\n";
echo "Period: " . $limits['requests']['period'] . "\n";

// Trees limits
echo "Trees: " . $limits['trees']['used'] . "/" . $limits['trees']['limit'] . "\n";
if ($limits['trees']['unlimited']) {
    echo "Trees: Unlimited\n";
}

// Images limits
echo "Images: " . $limits['images']['used'] . "/" . $limits['images']['limit'] . "\n";

// Storage info
echo "Storage used: " . $limits['storage']['used_human'] . "\n";
echo "Max file size: " . $limits['storage']['max_file_size_mb'] . " MB\n";

// Convenience methods
$client->account->getRemainingTrees();      // int or null if unlimited
$client->account->getRemainingImages();     // int or null if unlimited
$client->account->getStorageUsed();         // bytes
$client->account->getStorageUsedHuman();    // "45.2 MB"
$client->account->hasReachedTreesLimit();   // bool
$client->account->hasReachedImagesLimit();  // bool

// Check before creating resources
if ($client->account->hasReachedTreesLimit()) {
    echo "Cannot create more trees - limit reached!\n";
}
```

### Update Account

```php
$client->account->update([
    'name' => 'John Doe',
    'company' => 'Acme Inc',
    'timezone' => 'America/Toronto',
    'locale' => 'en',
]);
```

## Error Handling

The SDK throws specific exceptions for different error types:

```php
use Rlnks\Exceptions\AuthenticationException;
use Rlnks\Exceptions\AuthorizationException;
use Rlnks\Exceptions\NotFoundException;
use Rlnks\Exceptions\ValidationException;
use Rlnks\Exceptions\RateLimitException;
use Rlnks\Exceptions\RlnksException;

try {
    $tree = $client->trees->get('invalid-id');
} catch (AuthenticationException $e) {
    // Invalid API key
    echo "Authentication failed: " . $e->getMessage();

} catch (AuthorizationException $e) {
    // Insufficient permissions
    echo "Access denied: " . $e->getMessage();
    echo "Error code: " . $e->getErrorCode();

} catch (NotFoundException $e) {
    // Resource not found
    echo "Not found: " . $e->getMessage();

} catch (ValidationException $e) {
    // Validation errors
    echo "Validation failed: " . $e->getMessage();

    // Get field-level errors
    foreach ($e->getValidationErrors() as $field => $errors) {
        echo "{$field}: " . implode(', ', $errors) . "\n";
    }

    // Check specific field
    if ($e->hasFieldError('name')) {
        echo "Name error: " . $e->getFieldErrors('name')[0];
    }

} catch (RateLimitException $e) {
    // Rate limited
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds\n";
    echo "Limit: " . $e->getLimit() . "\n";
    echo "Remaining: " . $e->getRemaining() . "\n";

} catch (RlnksException $e) {
    // Other API errors
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->getErrorCode();
}
```

## Rate Limiting

The SDK automatically extracts rate limit information from responses:

```php
// Make any API call
$trees = $client->trees->list();

// Check rate limit status
$rateLimit = $client->getRateLimitInfo();

echo "Limit: " . $rateLimit['limit'] . "\n";
echo "Remaining: " . $rateLimit['remaining'] . "\n";
echo "Reset at: " . date('Y-m-d H:i:s', $rateLimit['reset']) . "\n";
```

## Pagination

Paginated responses provide convenient methods for iteration:

```php
$trees = $client->trees->list(['per_page' => 20]);

// Basic info
echo "Page " . $trees->getCurrentPage() . " of " . $trees->getTotalPages() . "\n";
echo "Showing " . $trees->count() . " of " . $trees->getTotalCount() . " total\n";

// Navigation
if ($trees->hasNextPage()) {
    $nextPage = $client->trees->list([
        'per_page' => 20,
        'page' => $trees->getCurrentPage() + 1,
    ]);
}

// Iterate
foreach ($trees as $tree) {
    echo $tree->name . "\n";
}

// Get first/last
$firstTree = $trees->first();
$lastTree = $trees->last();

// Check if empty
if ($trees->isEmpty()) {
    echo "No trees found.\n";
}
```

## Models

Response data is returned as model objects with helpful methods:

### Tree Model

```php
$tree = $client->trees->get($treeId);

// Properties
echo $tree->id;
echo $tree->name;
echo $tree->type; // 'image' or 'redirect'
echo $tree->short_code;
echo $tree->is_active;
echo $tree->total_requests;

// Methods
echo $tree->getImageUrl();    // https://api.rlnks.com/i/{uuid}
echo $tree->getRedirectUrl(); // https://api.rlnks.com/r/{uuid}
echo $tree->getShortUrl();    // https://api.rlnks.com/go/{code}

$tree->isImageTree();    // true/false
$tree->isRedirectTree(); // true/false

$tree->getCreatedAt();     // DateTimeImmutable
$tree->getLastRequestAt(); // DateTimeImmutable or null
```

### Image Model

```php
$image = $client->images->get($imageId);

echo $image->url;
echo $image->thumbnail_url;
echo $image->filename;
echo $image->dimensions;  // '1200x600'
echo $image->human_size;  // '44.26 KB'

$image->getWidth();  // 1200
$image->getHeight(); // 600
$image->getSize();   // bytes

$image->isJpeg();
$image->isPng();
$image->isWebp();
$image->inFolder('holiday2024');
```

## Testing

The SDK includes helpers for testing your integration:

```php
use Rlnks\Webhook\SignatureVerifier;

// Create test signatures
$verifier = new SignatureVerifier('test_secret');

$payload = json_encode(['event' => 'tree.created', 'data' => []]);
$signed = $verifier->sign($payload);

// Use in tests
$signature = $signed['signature'];
$timestamp = $signed['timestamp'];
```

## License

MIT License. See LICENSE file for details.
