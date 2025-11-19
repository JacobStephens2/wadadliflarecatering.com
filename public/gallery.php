<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Gallery';
$pageDescription = 'View photos of our catering services, food presentations, and events. See examples of our diverse cuisine offerings.';
include __DIR__ . '/includes/header.php';

// Get all gallery images
$galleryPath = __DIR__ . '/gallery/';
$images = [];
if (is_dir($galleryPath)) {
    $files = scandir($galleryPath);
    foreach ($files as $file) {
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
            $images[] = ['file' => $file, 'path' => 'gallery/', 'type' => 'gallery'];
        }
    }
    // Sort by filename (which includes date)
    usort($images, function($a, $b) {
        return strcmp($a['file'], $b['file']);
    });
    $images = array_reverse($images); // Most recent first
}

// Get menu images
$menuPath = __DIR__ . '/menu/';
$menuImages = [];
if (is_dir($menuPath)) {
    $files = scandir($menuPath);
    foreach ($files as $file) {
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
            $menuImages[] = ['file' => $file, 'path' => 'menu/', 'type' => 'menu'];
        }
    }
    // Sort menu images by filename
    usort($menuImages, function($a, $b) {
        return strcmp($a['file'], $b['file']);
    });
}

// Categorize images based on filename keywords
function getImageCategories($imageData) {
    $categories = [];
    $filename = $imageData['file'];
    $lower = strtolower($filename);
    
    // Menu images always get the menu category
    if ($imageData['type'] === 'menu') {
        $categories[] = 'menu';
        return $categories;
    }
    
    if (strpos($lower, 'charcuterie') !== false) {
        $categories[] = 'charcuterie';
        $categories[] = 'wedding';
    }
    if (strpos($lower, 'jerk') !== false || strpos($lower, 'caribbean') !== false) {
        $categories[] = 'caribbean';
    }
    if (strpos($lower, 'smoked') !== false || strpos($lower, 'bbq') !== false || strpos($lower, 'ribs') !== false) {
        $categories[] = 'bbq';
    }
    if (strpos($lower, 'buffet') !== false || strpos($lower, 'chafing') !== false || strpos($lower, 'table') !== false) {
        $categories[] = 'buffet';
    }
    if (strpos($lower, 'wedding') !== false) {
        $categories[] = 'wedding';
    }
    if (strpos($lower, 'vegetable') !== false || strpos($lower, 'vegan') !== false || strpos($lower, 'salad') !== false) {
        $categories[] = 'vegetarian';
    }
    
    return $categories;
}

// Generate alt text from filename
function generateAltText($imageData) {
    $filename = $imageData['file'];
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-'], ' ', $name);
    $name = preg_replace('/\d{8}\s*/', '', $name); // Remove date prefix
    if ($imageData['type'] === 'menu') {
        return 'Wadadli Flare Catering Menu - ' . ucwords($name);
    }
    return ucwords($name) . ' - Wadadli Flare Catering';
}

// Combine all images
$allImages = array_merge($menuImages, $images);
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Gallery</h1>
        <p style="text-align: center; max-width: 800px; margin: 2rem auto;">
            Browse our gallery to see examples of our diverse cuisine offerings, beautiful presentations, and the quality you can expect for your event.
        </p>
        
        <?php if (!empty($allImages)): ?>
        <div class="gallery-filters">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="menu">Menu</button>
            <button class="filter-btn" data-filter="charcuterie">Charcuterie</button>
            <button class="filter-btn" data-filter="caribbean">Caribbean</button>
            <button class="filter-btn" data-filter="bbq">BBQ</button>
            <button class="filter-btn" data-filter="buffet">Buffet</button>
            <button class="filter-btn" data-filter="wedding">Weddings</button>
            <button class="filter-btn" data-filter="vegetarian">Vegetarian</button>
        </div>
        
        <div class="gallery-grid">
            <?php foreach ($allImages as $imageData): 
                $categories = getImageCategories($imageData);
                $categoryString = implode(' ', $categories);
                $altText = generateAltText($imageData);
                $imageUrl = ($imageData['path'] === 'menu/') ? MENU_URL : GALLERY_URL;
            ?>
            <div class="gallery-item" data-categories="<?php echo htmlspecialchars($categoryString); ?>">
                <img src="<?php echo $imageUrl . htmlspecialchars($imageData['file']); ?>" alt="<?php echo htmlspecialchars($altText); ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="text-align: center; margin: 3rem 0;">Gallery images coming soon!</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

