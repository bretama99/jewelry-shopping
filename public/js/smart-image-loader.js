
// Smart Image Loader - Check once, use default if not found, never request again
class SmartImageLoader {
    constructor() {
        this.imageCache = new Map(); // Cache results to avoid repeated checks
        this.defaultImage = '/images/products/default-placeholder.jpg'; // Your actual default image
        this.checking = new Map(); // Track ongoing checks to prevent duplicates
    }

    // Main method to handle image loading
    loadImage(imgElement, imageUrl) {
        // If we already checked this URL, use cached result
        if (this.imageCache.has(imageUrl)) {
            const result = this.imageCache.get(imageUrl);
            if (result.exists) {
                imgElement.src = imageUrl;
            } else {
                imgElement.src = this.defaultImage;
            }
            return;
        }

        // If we're already checking this URL, wait for result
        if (this.checking.has(imageUrl)) {
            this.checking.get(imageUrl).then(exists => {
                imgElement.src = exists ? imageUrl : this.defaultImage;
            });
            return;
        }

        // Check if image exists (only once!)
        const checkPromise = this.checkImageExists(imageUrl);
        this.checking.set(imageUrl, checkPromise);

        checkPromise.then(exists => {
            // Cache the result forever
            this.imageCache.set(imageUrl, { exists, checkedAt: Date.now() });
            this.checking.delete(imageUrl);

            // Set the appropriate image
            imgElement.src = exists ? imageUrl : this.defaultImage;
        }).catch(() => {
            // On error, assume image doesn't exist
            this.imageCache.set(imageUrl, { exists: false, checkedAt: Date.now() });
            this.checking.delete(imageUrl);
            imgElement.src = this.defaultImage;
        });
    }

    // Check if image exists using a lightweight HEAD request
    async checkImageExists(url) {
        try {
            const response = await fetch(url, {
                method: 'HEAD', // Only get headers, not the full image
                cache: 'force-cache' // Use browser cache if available
            });
            return response.ok;
        } catch (error) {
            return false;
        }
    }

    // Process all images on the page
    processAllImages() {
        document.querySelectorAll('img[src*="/images/products/"]').forEach(img => {
            const originalSrc = img.src;

            // Skip if already processed or is the default image
            if (img.hasAttribute('data-smart-loaded') || originalSrc.includes('default-placeholder.jpg')) {
                return;
            }

            // Mark as processed to prevent re-processing
            img.setAttribute('data-smart-loaded', 'true');

            // Set a temporary placeholder while checking
            img.style.backgroundColor = '#f8f9fa';
            img.style.border = '1px solid #dee2e6';

            // Load the image smartly
            this.loadImage(img, originalSrc);
        });
    }

    // Set up automatic processing for dynamically added images
    setupAutoProcessing() {
        // Process existing images
        this.processAllImages();

        // Watch for new images added to the DOM
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is an image
                        if (node.tagName === 'IMG' && node.src && node.src.includes('/images/products/')) {
                            if (!node.hasAttribute('data-smart-loaded')) {
                                node.setAttribute('data-smart-loaded', 'true');
                                this.loadImage(node, node.src);
                            }
                        }

                        // Check for images inside the added node
                        const images = node.querySelectorAll && node.querySelectorAll('img[src*="/images/products/"]');
                        if (images) {
                            images.forEach(img => {
                                if (!img.hasAttribute('data-smart-loaded')) {
                                    img.setAttribute('data-smart-loaded', 'true');
                                    this.loadImage(img, img.src);
                                }
                            });
                        }
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Initialize the smart image loader
const smartImageLoader = new SmartImageLoader();

// Start processing when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    smartImageLoader.setupAutoProcessing();
});

// Also process images if the script loads after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        smartImageLoader.setupAutoProcessing();
    });
} else {
    smartImageLoader.setupAutoProcessing();
}

// Make it available globally for manual use if needed
window.smartImageLoader = smartImageLoader;
