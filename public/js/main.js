// Wadadli Flare Catering - Main JavaScript

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const hasSubmenuItems = document.querySelectorAll('.has-submenu');
    const header = document.querySelector('header');
    
    // Set mobile menu top position based on header height
    function setMenuPosition() {
        if (window.innerWidth <= 768 && navMenu && header) {
            const headerHeight = header.offsetHeight;
            navMenu.style.top = headerHeight + 'px';
        }
    }
    
    // Set initial position
    setMenuPosition();
    
    // Update on resize
    window.addEventListener('resize', setMenuPosition);
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
            // Ensure position is correct when menu opens
            setMenuPosition();
        });
    }
    
    // Handle submenu toggle on mobile
    hasSubmenuItems.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    item.classList.toggle('active');
                }
            });
        }
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && navMenu && navMenu.classList.contains('active')) {
            if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                if (mobileMenuToggle) {
                    mobileMenuToggle.classList.remove('active');
                }
            }
        }
    });
});

// Gallery filtering
function initGalleryFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter gallery items
            galleryItems.forEach(item => {
                const categories = item.getAttribute('data-categories') || '';
                if (filter === 'all' || categories.includes(filter)) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
            
            // Update visible images list for lightbox navigation
            updateVisibleImages();
        });
    });
}

// Initialize gallery filters if on gallery page
if (document.querySelector('.gallery-filters')) {
    initGalleryFilters();
}

// Lightbox functionality for gallery
let currentLightbox = null;
let visibleImages = [];
let currentImageIndex = 0;

function initLightbox() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function() {
            const img = this.querySelector('img');
            if (img) {
                // Get all currently visible gallery items
                updateVisibleImages();
                // Find the index of the clicked image
                const clickedIndex = Array.from(document.querySelectorAll('.gallery-item')).indexOf(this);
                currentImageIndex = visibleImages.indexOf(clickedIndex);
                if (currentImageIndex === -1) {
                    currentImageIndex = 0;
                }
                openLightbox(img.src, img.alt);
            }
        });
    });
}

function updateVisibleImages() {
    const allItems = document.querySelectorAll('.gallery-item');
    visibleImages = [];
    allItems.forEach((item, index) => {
        const style = window.getComputedStyle(item);
        if (style.display !== 'none' && style.opacity !== '0') {
            visibleImages.push(index);
        }
    });
}

function getImageData(index) {
    const allItems = document.querySelectorAll('.gallery-item');
    if (index >= 0 && index < allItems.length) {
        const item = allItems[index];
        const img = item.querySelector('img');
        if (img) {
            return { src: img.src, alt: img.alt };
        }
    }
    return null;
}

function openLightbox(src, alt) {
    // Close existing lightbox if open
    if (currentLightbox) {
        closeLightbox();
    }
    
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <button class="lightbox-nav lightbox-prev" aria-label="Previous image">&#8249;</button>
            <button class="lightbox-nav lightbox-next" aria-label="Next image">&#8250;</button>
            <img src="${src}" alt="${alt}">
        </div>
    `;
    
    lightbox.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    const content = lightbox.querySelector('.lightbox-content');
    content.style.cssText = `
        position: relative;
        max-width: 90%;
        max-height: 90%;
    `;
    
    const img = lightbox.querySelector('img');
    img.style.cssText = `
        max-width: 100%;
        max-height: 90vh;
        object-fit: contain;
        transition: opacity 0.3s ease;
    `;
    
    const closeBtn = lightbox.querySelector('.lightbox-close');
    closeBtn.style.cssText = `
        position: absolute;
        top: -40px;
        right: 0;
        color: white;
        font-size: 40px;
        cursor: pointer;
        line-height: 1;
        z-index: 10001;
    `;
    
    const prevBtn = lightbox.querySelector('.lightbox-prev');
    const nextBtn = lightbox.querySelector('.lightbox-next');
    
    // Style navigation buttons
    [prevBtn, nextBtn].forEach(btn => {
        btn.style.cssText = `
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        `;
        btn.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(255, 255, 255, 0.3)';
            this.style.borderColor = 'rgba(255, 255, 255, 0.8)';
            this.style.transform = 'translateY(-50%) scale(1.1)';
        });
        btn.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
            this.style.borderColor = 'rgba(255, 255, 255, 0.5)';
            this.style.transform = 'translateY(-50%) scale(1)';
        });
    });
    
    prevBtn.style.left = '20px';
    nextBtn.style.right = '20px';
    
    // Hide navigation buttons if only one image
    if (visibleImages.length <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    }
    
    document.body.appendChild(lightbox);
    currentLightbox = lightbox;
    
    setTimeout(() => {
        lightbox.style.opacity = '1';
    }, 10);
    
    function showImage(index) {
        if (visibleImages.length === 0) return;
        
        // Ensure index is within bounds
        if (index < 0) {
            index = visibleImages.length - 1;
        } else if (index >= visibleImages.length) {
            index = 0;
        }
        
        currentImageIndex = index;
        const imageData = getImageData(visibleImages[currentImageIndex]);
        
        if (imageData) {
            // Fade out current image
            img.style.opacity = '0';
            setTimeout(() => {
                img.src = imageData.src;
                img.alt = imageData.alt;
                // Fade in new image
                setTimeout(() => {
                    img.style.opacity = '1';
                }, 10);
            }, 150);
        }
    }
    
    function nextImage() {
        if (visibleImages.length > 1) {
            showImage(currentImageIndex + 1);
        }
    }
    
    function prevImage() {
        if (visibleImages.length > 1) {
            showImage(currentImageIndex - 1);
        }
    }
    
    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        prevImage();
    });
    nextBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        nextImage();
    });
    
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    
    // Prevent image click from closing lightbox
    img.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    function keyHandler(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowLeft') {
            prevImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        }
    }
    
    document.addEventListener('keydown', keyHandler);
    
    function closeLightbox() {
        if (lightbox && lightbox.parentNode) {
            lightbox.style.opacity = '0';
            setTimeout(() => {
                if (lightbox.parentNode) {
                    document.body.removeChild(lightbox);
                }
                currentLightbox = null;
            }, 300);
        }
    }
    
    // Store close function for external access
    lightbox.closeLightbox = closeLightbox;
}

// Initialize lightbox if on gallery page
if (document.querySelector('.gallery-item')) {
    updateVisibleImages(); // Initialize visible images list
    initLightbox();
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (field.value && !emailRegex.test(field.value)) {
            isValid = false;
            field.classList.add('error');
        }
    });
    
    return isValid;
}

// Add error styling
const style = document.createElement('style');
style.textContent = `
    .form-group input.error,
    .form-group textarea.error,
    .form-group select.error {
        border-color: #c41e3a;
        box-shadow: 0 0 0 3px rgba(196, 30, 58, 0.1);
    }
`;
document.head.appendChild(style);

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// Hero slideshow - automatically cycle through images with manual navigation
function initHeroSlideshow() {
    const slides = document.querySelectorAll('.hero-slide');
    const prevButton = document.querySelector('.hero-nav-prev');
    const nextButton = document.querySelector('.hero-nav-next');
    
    if (slides.length === 0) return;
    
    let currentSlide = 0;
    const slideInterval = 5000; // Change image every 5 seconds
    let autoSlideInterval = null;
    
    function showSlide(index) {
        // Remove active class from current slide
        slides[currentSlide].classList.remove('active');
        
        // Update current slide index
        currentSlide = index;
        
        // Ensure index is within bounds
        if (currentSlide < 0) {
            currentSlide = slides.length - 1;
        } else if (currentSlide >= slides.length) {
            currentSlide = 0;
        }
        
        // Add active class to new slide
        slides[currentSlide].classList.add('active');
        
        // Reset auto-advance timer
        resetAutoSlide();
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    function startAutoSlide() {
        if (slides.length > 1) {
            autoSlideInterval = setInterval(nextSlide, slideInterval);
        }
    }
    
    function resetAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
        }
        startAutoSlide();
    }
    
    // Add click handlers for navigation buttons
    if (prevButton) {
        prevButton.addEventListener('click', function(e) {
            e.preventDefault();
            prevSlide();
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', function(e) {
            e.preventDefault();
            nextSlide();
        });
    }
    
    // Start the slideshow
    startAutoSlide();
}

// Initialize hero slideshow if on home page
if (document.querySelector('.hero-slideshow')) {
    initHeroSlideshow();
}

