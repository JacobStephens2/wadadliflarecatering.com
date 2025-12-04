<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Z8L71SEC9J"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-Z8L71SEC9J');
    </script>
    
    <!-- Primary Meta Tags -->
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Wadadli Flare Catering</title>
    <meta name="title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Wadadli Flare Catering">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Wadadli Flare Catering - Professional catering services in Pennsylvania. American, French, Italian, BBQ, and Caribbean cuisine. Serving Twin Valley, West Chester, and Main Line.'; ?>">
    <meta name="keywords" content="catering, Pennsylvania, Elverson, West Chester, Main Line, wedding catering, corporate catering, Caribbean food, BBQ catering, Chef Jamie Francis, Twin Valley catering, private events catering">
    <meta name="author" content="Wadadli Flare Catering">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <?php
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    // Remove query string for canonical URL
    $requestUri = strtok($requestUri, '?');
    // Remove .php extension if present
    $requestUri = preg_replace('/\.php$/', '', $requestUri);
    // Ensure leading slash
    if (substr($requestUri, 0, 1) !== '/') {
        $requestUri = '/' . $requestUri;
    }
    // Remove trailing slash except for root
    $requestUri = ($requestUri !== '/') ? rtrim($requestUri, '/') : '/';
    $currentUrl = SITE_URL . $requestUri;
    ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($currentUrl); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Wadadli Flare Catering">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Wadadli Flare Catering - Professional catering services in Pennsylvania. American, French, Italian, BBQ, and Caribbean cuisine. Serving Twin Valley, West Chester, and Main Line.'; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?><?php echo ASSETS_URL; ?>logo.webp">
    <meta property="og:site_name" content="Wadadli Flare Catering">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Wadadli Flare Catering">
    <meta name="twitter:description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Wadadli Flare Catering - Professional catering services in Pennsylvania. American, French, Italian, BBQ, and Caribbean cuisine. Serving Twin Valley, West Chester, and Main Line.'; ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?><?php echo ASSETS_URL; ?>logo.webp">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>favicon.ico">
    
    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Stylesheet -->
    <?php
    $cssFile = __DIR__ . '/../css/style.css';
    $cssVersion = file_exists($cssFile) ? filemtime($cssFile) : time();
    ?>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>style.css?v=<?php echo $cssVersion; ?>">
    
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FoodEstablishment",
        "name": "Wadadli Flare Catering",
        "image": "<?php echo SITE_URL; ?><?php echo ASSETS_URL; ?>logo.webp",
        "@id": "<?php echo SITE_URL; ?>",
        "url": "<?php echo SITE_URL; ?>",
        "telephone": "<?php echo CONTACT_PHONE; ?>",
        "email": "<?php echo CONTACT_EMAIL; ?>",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Elverson",
            "addressRegion": "PA",
            "addressCountry": "US"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "40.1568",
            "longitude": "-75.8327"
        },
        "servesCuisine": ["American", "French", "Italian", "BBQ", "Caribbean", "Asian"],
        "priceRange": "$$",
        "description": "Professional catering services in Pennsylvania. American, French, Italian, BBQ, and Caribbean cuisine. Serving Twin Valley, West Chester, and Main Line.",
        "areaServed": [
            {
                "@type": "City",
                "name": "Elverson"
            },
            {
                "@type": "City",
                "name": "West Chester"
            },
            {
                "@type": "City",
                "name": "Twin Valley"
            },
            {
                "@type": "City",
                "name": "Main Line"
            }
        ],
        "founder": {
            "@type": "Person",
            "name": "Chef Jamie Francis"
        },
        "sameAs": [
            "<?php echo FACEBOOK_URL; ?>",
            "<?php echo INSTAGRAM_URL; ?>"
        ]
    }
    </script>
</head>
<body<?php echo isset($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : ''; ?>>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
                    <img src="<?php echo ASSETS_URL; ?>logo.webp" alt="Wadadli Flare Catering Logo" class="logo-image">
                    <h1>Wadadli Flare Catering</h1>
                </a>
            </div>
            <nav class="main-nav">
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul class="nav-menu">
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>about.php">About</a></li>
                    <li class="has-submenu">
                        <a href="<?php echo BASE_URL; ?>services.php">Services <span class="arrow">▼</span></a>
                        <ul class="submenu">
                            <li><a href="<?php echo BASE_URL; ?>weddings.php">Weddings</a></li>
                            <li><a href="<?php echo BASE_URL; ?>corporate.php">Corporate Events</a></li>
                            <li><a href="<?php echo BASE_URL; ?>private-events.php">Private Events</a></li>
                            <li><a href="<?php echo BASE_URL; ?>bbq-experience.php">BBQ Experience</a></li>
                            <li><a href="<?php echo BASE_URL; ?>caribbean-experience.php">Caribbean Experience</a></li>
                            <li><a href="<?php echo BASE_URL; ?>pickup-dropoff.php">Pick Up & Drop Off</a></li>
                            <li><a href="<?php echo BASE_URL; ?>venues.php">Venues</a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>gallery.php">Gallery</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reviews.php">Reviews</a></li>
                    <li><a href="<?php echo BASE_URL; ?>policies.php">Policies</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>
                    <li><a href="<?php echo BASE_URL; ?>quote-request.php" class="cta-button">Get Quote</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>

