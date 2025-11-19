<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'About Chef Jamie';
$pageDescription = 'Learn about Chef Jamie Francis, owner of Wadadli Flare Catering. 34 years of culinary experience from 5-star resorts to your event.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">About Chef Jamie Francis</h1>
        
        <div class="grid grid-2" style="align-items: start; margin-top: 2rem;">
            <div>
                <h2 class="section-subtitle">34 Years of Culinary Excellence</h2>
                <p>I have been working in the Culinary Field for the past 34 years. Upon graduating from Culinary school in the Caribbean in 1989, I was immediately employed at St. James Club, Antigua, a 5 Star Resort. During my 14 years of employment, I climbed the ranks, starting as a Line Cook and finished as the Executive Chef, managing the 5 Restaurants on the property of different cuisines (Italian, French, Caribbean, American & Asian).</p>
                
                <p>Also during that time, I intermittently attended the Culinary Institutes of America, for a variety of courses that included Soups, Stocks & Sauces, Asian, Grade Manger, Flavor Dynamics, Smoke House and Mediterranean.</p>
            </div>
            
            <div>
                <h2 class="section-subtitle">International Experience</h2>
                <p>In 2000, I moved to the United States, and lived in Long Beach, California, for 2 years. I worked as a Chef at Le Jardin Du Tea, and as a Banquet Sous Chef at The Ritz Carlton Marina Del Rey Hotel.</p>
                
                <p>I then moved to Pennsylvania, where I am currently living with my family for the past 20 years. For the first 4 years, I worked at several restaurants, which included Bamboo Club, PF Chang's and Applebee's. Then for the past 16 years, I worked for 12th Street Catering Company, as the Chef/General Managing the Café at United States Liability Insurance. During this time, I also worked on a variety of Catered Events, which included BBQs, Weddings, and Holiday Parties in the Corporate and Private sectors.</p>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px; text-align: center;">
            <h2 class="section-subtitle">Wadadli Flare Catering</h2>
            <p style="font-size: 1.1rem; max-width: 800px; margin: 1rem auto;">
                I am now the proud owner of Wadadli Flare Catering! Based on my experiences and knowledge mentioned above, I am looking forward to applying my unique culinary skills to satisfying my customers, and managing a successful business!
            </p>
        </div>
        
        <div class="grid grid-3" style="margin-top: 3rem;">
            <div class="card">
                <h3 class="card-title">Diverse Expertise</h3>
                <p>Italian, French, Caribbean, American, and Asian cuisines - I bring a world of flavors to your event.</p>
            </div>
            <div class="card">
                <h3 class="card-title">5-Star Background</h3>
                <p>From St. James Club Antigua to The Ritz Carlton, I've worked at the highest levels of hospitality.</p>
            </div>
            <div class="card">
                <h3 class="card-title">Custom Menus</h3>
                <p>Every event is unique. I specialize in creating custom menus tailored to your preferences and needs.</p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem;">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

