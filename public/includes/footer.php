    </main>
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Wadadli Flare Catering</h3>
                <p>Bringing diverse culinary excellence to Pennsylvania</p>
                <p>Based in Elverson, PA</p>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p>Phone: <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', CONTACT_PHONE); ?>"><?php echo CONTACT_PHONE; ?></a></p>
                <p>Email: <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a></p>
            </div>
            <div class="footer-section">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">Facebook</a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">Instagram</a>
                    <a href="<?php echo GOOGLE_MAPS_URL; ?>" target="_blank" rel="noopener noreferrer" aria-label="Google Maps">Find Us</a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>about.php">About Chef Jamie</a></li>
                    <li><a href="<?php echo BASE_URL; ?>quote-request.php">Request a Quote</a></li>
                    <li><a href="<?php echo BASE_URL; ?>policies.php">Policies</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Wadadli Flare Catering. All rights reserved.</p>
        </div>
    </footer>
    <script src="<?php echo JS_URL; ?>main.js"></script>
</body>
</html>











