# OptStack Demo - Complete Guide

This demo showcases a complete implementation of OptStack with a custom post type.

---

## What's Included

### 1. **Custom Post Type: Demo Products**
- Post type slug: `demo_product`
- Menu location: WordPress admin sidebar
- Icon: Shopping cart (dashicons-cart)
- Features: Title, Editor, Thumbnail, Excerpt

### 2. **OptStack Meta Fields**
Comprehensive meta fields including:
- **Basic Info**: SKU, Status, Featured toggle
- **Pricing**: Regular price, Sale price, Currency, Tax class
- **Inventory**: Stock management, Quantity, Warehouse location
- **Specifications**: Repeatable group for product specs
- **Gallery**: Repeatable group for product images
- **SEO**: Title, Description, Keywords (all searchable)
- **Shipping**: Weight, Dimensions, Shipping class
- **Notes**: Internal notes field

### 3. **Searchable Fields**
Fields optimized for efficient WP_Query:
- `sku`
- `status`
- `featured`
- `pricing.regular_price`
- `inventory.quantity`
- `inventory.warehouse`
- `seo.title`
- `seo.keywords`
- `seo.focus_keyword`

---

## How to Use

### Step 1: Activate the Demo

The demo is already active! It's loaded via `functions.php`:
```php
require_once __DIR__ . '/test.php';
```

### Step 2: Run the Test

1. Visit your WordPress dashboard
2. You'll see **Demo Products** in the admin menu
3. Click the **"Run Test"** link in the admin notice, OR
4. Visit: `http://yoursite.local/?demo_test=1`

**The test will:**
- ✅ Create a sample product
- ✅ Test `updateField()` with various field types
- ✅ Verify data is saved correctly
- ✅ Check searchable fields are synced
- ✅ Run sample queries

### Step 3: Create Products Manually

1. Go to **Demo Products → Add New**
2. Enter product title and content
3. Scroll down to **Product Information** meta box
4. Fill in the fields:
   - Basic info (SKU, Status, Featured)
   - Pricing details
   - Inventory settings
   - Specifications (click "Add Item" to add more)
   - Gallery images
   - SEO settings
   - Shipping information
4. Click **Publish**

---

## Testing updateField()

### Quick Test in Code

```php
add_action('wp_loaded', function() {
    // Get first demo product
    $products = get_posts([
        'post_type' => 'demo_product',
        'posts_per_page' => 1,
    ]);
    
    if (empty($products)) {
        return;
    }
    
    $product_id = $products[0]->ID;
    
    // Update simple field
    OptStack::updateField('demo_product_meta', 'sku', 'SKU-' . time(), $product_id);
    
    // Update nested field (in group)
    OptStack::updateField('demo_product_meta', 'pricing.regular_price', 199.99, $product_id);
    
    // Update deeply nested
    OptStack::updateField('demo_product_meta', 'seo.title', 'Updated SEO Title', $product_id);
    
    // Verify
    $meta = get_post_meta($product_id, 'demo_product_meta', true);
    echo "SKU: {$meta['sku']}<br>";
    echo "Price: {$meta['pricing']['regular_price']}<br>";
    echo "SEO: {$meta['seo']['title']}<br>";
});
```

---

## Helper Functions Available

The demo includes ready-to-use helper functions:

### Get Product Meta
```php
$meta = get_demo_product_meta($post_id);
// Returns complete meta array
```

### Get Pricing
```php
$regular = get_demo_product_price($post_id);
$sale = get_demo_product_sale_price($post_id);
$display = get_demo_product_display_price($post_id); // Sale if available, else regular
```

### Check Stock Status
```php
$in_stock = is_demo_product_in_stock($post_id);
$low_stock = is_demo_product_low_stock($post_id);
```

---

## Query Functions Available

### Query by Price Range
```php
$products = query_demo_products_by_price(50, 200);
// Find products between $50 and $200

if ($products->have_posts()) {
    while ($products->have_posts()) {
        $products->the_post();
        echo get_the_title() . '<br>';
    }
    wp_reset_postdata();
}
```

### Query Featured Products
```php
$featured = query_demo_featured_products();
echo "Found {$featured->found_posts} featured products";
```

### Query by Status
```php
$active = query_demo_products_by_status('active');
$discontinued = query_demo_products_by_status('discontinued');
```

### Query Low Stock Products
```php
$low_stock = query_demo_low_stock_products();
// Products with quantity > 0 and <= 5
```

---

## Examples of Use Cases

### 1. Display Product Price in Template
```php
<?php
$price = get_demo_product_display_price(get_the_ID());
$meta = get_demo_product_meta(get_the_ID());
$currency = $meta['pricing']['currency'] ?? 'USD';
?>

<div class="product-price">
    <?php if ($sale = get_demo_product_sale_price(get_the_ID())): ?>
        <del><?php echo $currency; ?> <?php echo number_format($price, 2); ?></del>
        <ins><?php echo $currency; ?> <?php echo number_format($sale, 2); ?></ins>
    <?php else: ?>
        <?php echo $currency; ?> <?php echo number_format($price, 2); ?>
    <?php endif; ?>
</div>
```

### 2. Show Stock Status
```php
<?php if (is_demo_product_in_stock(get_the_ID())): ?>
    <?php if (is_demo_product_low_stock(get_the_ID())): ?>
        <span class="stock low">Only a few left!</span>
    <?php else: ?>
        <span class="stock in-stock">In Stock</span>
    <?php endif; ?>
<?php else: ?>
    <span class="stock out">Out of Stock</span>
<?php endif; ?>
```

### 3. Display Product Specifications
```php
<?php
$meta = get_demo_product_meta(get_the_ID());
$specs = $meta['specifications'] ?? [];
?>

<?php if (!empty($specs)): ?>
    <table class="product-specs">
        <?php foreach ($specs as $spec): ?>
            <tr>
                <th><?php echo esc_html($spec['name']); ?></th>
                <td>
                    <?php echo esc_html($spec['value']); ?>
                    <?php if (!empty($spec['unit'])): ?>
                        <?php echo esc_html($spec['unit']); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
```

### 4. Product Gallery
```php
<?php
$meta = get_demo_product_meta(get_the_ID());
$gallery = $meta['gallery'] ?? [];
?>

<?php if (!empty($gallery)): ?>
    <div class="product-gallery">
        <?php foreach ($gallery as $item): ?>
            <?php if (!empty($item['image'])): ?>
                <figure>
                    <?php echo wp_get_attachment_image($item['image'], 'medium'); ?>
                    <?php if (!empty($item['caption'])): ?>
                        <figcaption><?php echo esc_html($item['caption']); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

### 5. Update Inventory After Purchase
```php
// Hook into your checkout/payment process
add_action('your_payment_complete_hook', function($order_id) {
    // Assuming you have order items with product IDs
    $items = get_order_items($order_id);
    
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Get current stock
        $meta = get_demo_product_meta($product_id);
        $current_stock = intval($meta['inventory']['quantity'] ?? 0);
        
        // Calculate new stock
        $new_stock = max(0, $current_stock - $quantity);
        
        // Update using OptStack
        OptStack::updateField('demo_product_meta', 'inventory.quantity', $new_stock, $product_id);
        
        // Searchable field is auto-synced!
        // Can now query low stock products efficiently
    }
});
```

### 6. Featured Products Shortcode
```php
add_shortcode('demo_featured_products', function($atts) {
    $atts = shortcode_atts(['limit' => 4], $atts);
    
    $products = query_demo_featured_products();
    $products->set('posts_per_page', $atts['limit']);
    
    if (!$products->have_posts()) {
        return '<p>No featured products found.</p>';
    }
    
    ob_start();
    ?>
    <div class="featured-products">
        <?php while ($products->have_posts()): $products->the_post(); ?>
            <div class="product">
                <?php the_post_thumbnail('medium'); ?>
                <h3><?php the_title(); ?></h3>
                <p class="price">
                    <?php echo number_format(get_demo_product_display_price(get_the_ID()), 2); ?>
                </p>
                <a href="<?php the_permalink(); ?>">View Product</a>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
});

// Use in content: [demo_featured_products limit="6"]
```

---

## Field Types Demonstrated

This demo showcases all major OptStack field types:

| Field Type | Example | Location |
|------------|---------|----------|
| `text` | SKU, Spec names | Basic info, Specifications |
| `textarea` | SEO Description | SEO group |
| `wysiwyg` | Internal notes | Additional fields |
| `number` | Price, Quantity | Pricing, Inventory |
| `select` | Currency, Status | Various groups |
| `toggle` | Featured, Manage stock | Basic, Inventory |
| `media` | Gallery images | Gallery group |
| `date` | Last updated | Additional fields |
| **Repeatable Groups** | Specifications, Gallery | Multiple sections |
| **Nested Groups** | Pricing, Inventory, SEO | Throughout |
| **Conditional Fields** | Stock fields, Shipping | Inventory, Shipping |
| **Searchable Fields** | SKU, Price, Status | Various |

---

## Searchable Fields in Action

All searchable fields are automatically indexed with the format:
```
_optstack_idx_post_{field_path}
```

**Examples:**
- `_optstack_idx_post_sku`
- `_optstack_idx_post_pricing_regular_price`
- `_optstack_idx_post_inventory_quantity`
- `_optstack_idx_post_seo_title`

**Query them efficiently:**
```php
$products = new WP_Query([
    'post_type' => 'demo_product',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_optstack_idx_post_pricing_regular_price',
            'value' => 100,
            'compare' => '>=',
            'type' => 'NUMERIC',
        ],
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ],
        [
            'key' => '_optstack_idx_post_inventory_quantity',
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ],
    ],
]);
```

---

## Next Steps

1. **Run the test**: Visit `?demo_test=1` to see it in action
2. **Create products**: Add demo products via the admin
3. **Test queries**: Try the helper query functions
4. **Customize**: Modify `test.php` to fit your needs
5. **Build frontend**: Create templates to display products

---

## File Structure

```
wp-content/themes/twentytwentythree/
├── functions.php           # Loads test.php
├── test.php                # Complete demo (this file does everything!)
└── DEMO_GUIDE.md          # This guide
```

---

## Tips

1. **Always provide post ID** when using `updateField()` with post_type stacks
2. **Use searchable fields** for any field you'll query frequently
3. **Repeatable groups** are perfect for galleries, specs, FAQs, etc.
4. **Conditional fields** keep the UI clean and user-friendly
5. **Helper functions** make your template code cleaner

---

## Troubleshooting

### Products not showing in admin?
- Make sure you're logged in as admin
- Check `wp-content/themes/twentytwentythree/functions.php` includes `test.php`
- Visit `?demo_test=1` to verify setup

### Meta fields not appearing?
- Check if OptStack plugin is active
- Go to post edit screen for a demo product
- Look for "Product Information" meta box

### updateField not working?
- Make sure you provide the post ID (4th parameter)
- Verify the post exists: `get_post($post_id)`
- Check WordPress debug log for errors

### Searchable fields not syncing?
- Verify field has `'searchable' => true` in definition
- Check indexed meta: `get_post_meta($id, '_optstack_idx_post_sku', true)`
- Only scalar fields can be searchable (not arrays/objects)

---

## Resources

- **OptStack Documentation**: `wp-content/plugins/optstack/documents/FLOW.md`
- **updateField Guide**: `wp-content/plugins/optstack/documents/UPDATE_FIELD_FEATURE.md`
- **Troubleshooting**: `wp-content/plugins/optstack/documents/UPDATEFIELD_TROUBLESHOOTING.md`
- **Examples**: `wp-content/plugins/optstack/examples/`

---

**Enjoy testing OptStack! 🚀**
