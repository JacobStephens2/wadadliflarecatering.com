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
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            $filePath = $galleryPath . $file;
            $images[] = [
                'file' => $file, 
                'path' => 'gallery/', 
                'type' => 'gallery',
                'mtime' => filemtime($filePath),
                'hasDate' => preg_match('/^\d{8}_/', $file)
            ];
        }
    }
    // Sort: images with dates first (by date descending), then images without dates (by modification time descending)
    usort($images, function($a, $b) {
        // If both have dates, sort by filename (date descending)
        if ($a['hasDate'] && $b['hasDate']) {
            return strcmp($b['file'], $a['file']);
        }
        // If only one has a date, date-prefixed images come first
        if ($a['hasDate'] && !$b['hasDate']) {
            return -1;
        }
        if (!$a['hasDate'] && $b['hasDate']) {
            return 1;
        }
        // If neither has a date, sort by modification time (newest first)
        if (!$a['hasDate'] && !$b['hasDate']) {
            return $b['mtime'] - $a['mtime'];
        }
        return 0;
    });
}

// Categorize images based on filename keywords
function getImageCategories($imageData) {
    $categories = [];
    $filename = $imageData['file'];
    $lower = strtolower($filename);
    
    // Charcuterie (also wedding)
    if (strpos($lower, 'charcuterie') !== false) {
        $categories[] = 'charcuterie';
        $categories[] = 'wedding';
    }
    
    // Caribbean
    if (strpos($lower, 'jerk') !== false || strpos($lower, 'caribbean') !== false || strpos($lower, 'wadadli') !== false) {
        $categories[] = 'caribbean';
    }
    
    // BBQ (includes smoked meats, ribs, wings, grilling)
    if (strpos($lower, 'smoked') !== false || strpos($lower, 'bbq') !== false || strpos($lower, 'ribs') !== false || 
        strpos($lower, 'wings') !== false || strpos($lower, 'grilling') !== false || strpos($lower, 'grilled') !== false ||
        strpos($lower, 'brisket') !== false || strpos($lower, 'pork_butts') !== false || strpos($lower, 'smoker') !== false) {
        $categories[] = 'bbq';
    }
    
    // Buffet/Setup
    if (strpos($lower, 'buffet') !== false || strpos($lower, 'chafing') !== false || strpos($lower, 'table') !== false ||
        strpos($lower, 'cutlery') !== false || strpos($lower, 'plates') !== false) {
        $categories[] = 'buffet';
    }
    
    // Wedding
    if (strpos($lower, 'wedding') !== false) {
        $categories[] = 'wedding';
    }
    
    // Vegetarian
    if (strpos($lower, 'vegetable') !== false || strpos($lower, 'vegan') !== false || strpos($lower, 'salad') !== false ||
        strpos($lower, 'vegetarian') !== false) {
        $categories[] = 'vegetarian';
    }
    
    // Appetizers/Cocktail
    if (strpos($lower, 'cocktail') !== false || strpos($lower, 'appetizer') !== false) {
        $categories[] = 'appetizers';
    }
    
    // Seafood
    if (strpos($lower, 'salmon') !== false || strpos($lower, 'tilapia') !== false || strpos($lower, 'shrimp') !== false ||
        strpos($lower, 'fish') !== false || strpos($lower, 'lox') !== false) {
        $categories[] = 'seafood';
    }
    
    // Mexican/Latin
    if (strpos($lower, 'enchilada') !== false || strpos($lower, 'fajita') !== false || strpos($lower, 'taco') !== false) {
        $categories[] = 'mexican';
    }
    
    // Sides
    if (strpos($lower, 'rice') !== false || strpos($lower, 'mac_and_cheese') !== false || 
        strpos($lower, 'macaroni') !== false || strpos($lower, 'beans') !== false) {
        $categories[] = 'sides';
    }
    
    return $categories;
}

// Generate alt text from filename
function generateAltText($imageData) {
    $filename = $imageData['file'];
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-'], ' ', $name);
    $name = preg_replace('/\d{8}\s*/', '', $name); // Remove date prefix
    return ucwords($name) . ' - Wadadli Flare Catering';
}
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Gallery</h1>
        <p style="text-align: center; max-width: 800px; margin: 2rem auto;">
            Browse our gallery to see examples of our diverse cuisine offerings, beautiful presentations, and the quality you can expect for your event.
        </p>
        
        <?php if (!empty($images)): ?>
        <div class="gallery-filters">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="charcuterie">Charcuterie</button>
            <button class="filter-btn" data-filter="appetizers">Appetizers</button>
            <button class="filter-btn" data-filter="bbq">BBQ</button>
            <button class="filter-btn" data-filter="caribbean">Caribbean</button>
            <button class="filter-btn" data-filter="seafood">Seafood</button>
            <button class="filter-btn" data-filter="mexican">Mexican</button>
            <button class="filter-btn" data-filter="sides">Sides</button>
            <button class="filter-btn" data-filter="vegetarian">Vegetarian</button>
            <button class="filter-btn" data-filter="buffet">Buffet</button>
            <button class="filter-btn" data-filter="wedding">Weddings</button>
        </div>
        
        <div class="gallery-grid">
            <?php foreach ($images as $imageData): 
                $categories = getImageCategories($imageData);
                $categoryString = implode(' ', $categories);
                $altText = generateAltText($imageData);
                $imageUrl = GALLERY_URL;
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

