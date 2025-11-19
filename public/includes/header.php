<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Wadadli Flare Catering - Professional catering services in Pennsylvania. Caribbean, BBQ, American, French, Italian cuisine. Serving Twin Valley, West Chester, and Main Line.'; ?>">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Wadadli Flare Catering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>style.css">
</head>
<body<?php echo isset($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : ''; ?>>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
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

