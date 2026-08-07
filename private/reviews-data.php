<?php
/**
 * Curated review archive.
 *
 * This is the floor the site falls back to. The Google Places API only ever
 * returns 5 reviews and ranks them by relevance, so older reviews drop out of
 * the live feed permanently. Anything captured here keeps showing regardless.
 *
 * Live Google reviews are merged over this list by
 * private/includes/google_reviews.php; a live review with the same author
 * replaces its archive copy.
 *
 * Fields:
 *   source     - 'google' or 'facebook'
 *   author     - reviewer's display name
 *   rating     - 1-5
 *   text       - review body
 *   time       - unix timestamp, used for ordering. 0 means the date is unknown,
 *                which sorts the review last and hides its date entirely.
 *   precision  - 'day' when the exact date is known, 'month' when approximate
 *
 * Reviews.md recorded Google dates as relative ages ("2 years ago"), which is
 * not enough to date a review. Where the Places API has since supplied a real
 * publishTime it is recorded below. The three reviews the API does not return
 * carry time => 0 rather than a guess: showing an invented date is worse than
 * showing none. If Google ever surfaces them, the merge fills in the real date.
 */

return [
    // --- Google ---
    [
        // Added by hand on 2026-08-07: Google had this review but the Places API
        // did not return it, since the API caps at 5 and ranks by relevance. The
        // merge will swap in the live copy, with its exact publishTime, if it
        // ever makes the top 5. Text is verbatim, typo included.
        'source' => 'google',
        'author' => 'Don Menya',
        'rating' => 5,
        'text' => "We had the pleasure of enjoying an excellent catering service from Chef Jamie Francis and Wadadil Flare Catering. We requested several Caribbean Experience dishes (Jerk and Airline Chicken, Plantains, and tropical juices - Tamarind and Passion Fruit) which our 60 birthday party guests thoroughly enjoyed!\n\nI have no hesitation recommending Chef Francis and his company to help make your event special, with your guests happily enjoying his well-prepared culinary delights!",
        'time' => 1786108500, // 2026-08-07, posted the morning it was added
        'precision' => 'day',
    ],
    [
        'source' => 'google',
        'author' => 'Taylore Harris',
        'rating' => 5,
        'text' => 'I had Wadadli Flare cater a work event on short notice and the food and service were amazing!!!! The employees loved the food and there were absolutely no left overs. The food was very well packed and was hot on delivery. So many people asked about the food so they could have them come back and cater another event! I highly recommend them for any and all catering needs, you will not be disappointed. Thank you so much for coming through for us on such short notice!',
        'time' => 1750205628, // 2025-06-17, from the Places API
        'precision' => 'day',
    ],
    [
        'source' => 'google',
        'author' => 'Todd Kennedy',
        'rating' => 5,
        'text' => 'We hosted a large party this past weekend to celebrate our son\'s high school graduation. Everything about our experience with Wadadli Flare Catering was exceptional. The food was outstanding and we received many compliments on the variety of smoked meats and delicious sides. The service and attention to detail was equally as impressive. Do not hesitate to contact them for your next event. You will not be disappointed and your guests will thank you!',
        'time' => 1719873044, // 2024-07-01, from the Places API
        'precision' => 'day',
    ],
    [
        'source' => 'google',
        'author' => 'Lisa Betz',
        'rating' => 5,
        'text' => 'Wadadli Flare catered our memorial service. Everyone was complimenting us on how delicious the food was and how beautiful it was presented. My niece said it was the best catered meal she\'d ever eaten. Highly recommended. The cold grilled veggies are amazing.',
        'time' => 1697483540, // 2023-10-16, from the Places API
        'precision' => 'day',
    ],
    [
        'source' => 'google',
        'author' => 'Scott Caley',
        'rating' => 5,
        'text' => 'I was invited to an event catered by Wadadli Flare Catering today… The baked salmon was tender, flaky, and flavorful. The best salmon I ever had! Airline Chicken Breast was new to me… what a delectable treat! The serving consisted of the wing portion and the breast portion. The wing portion included the drumette that made a convincing challenge to be picked up and eaten, while the breast portion was content to be carved and eaten with a fork. Both were absolutely scrumptious! The meal was rounded out for me by a grilled vegetable medley, with an intriguing variety colors, flavors, and textures. Wadadli Flare Catering receives my highest recommendation!',
        'time' => 1697321150, // 2023-10-14, from the Places API
        'precision' => 'day',
    ],
    [
        'source' => 'google',
        'author' => 'Katie D',
        'rating' => 5,
        'text' => 'We just had the pleasure of using Wadadli Flare to cater my sons birthday party. We couldn\'t be happier. Everything was amazing and our guests are still raving about the food weeks after. We highly recommend and will be using them again for all family gatherings.',
        'time' => 1691889901, // 2023-08-12, from the Places API
        'precision' => 'day',
    ],
    [
        'source' => 'google',
        'author' => 'deborah Hargreaves',
        'rating' => 5,
        'text' => 'The food was delicious n so beautifully prepared n presented in celebrating a friend\'s life who loved parties n great food..she would have loved it too! What a great tribute..God bless you as u bless others with the joy of great food n a wonderful experience! I will recommend with pleasure!',
        'time' => 0, // date unknown; the Places API does not return this one
        'precision' => 'month',
    ],
    [
        'source' => 'google',
        'author' => 'Anna Carter',
        'rating' => 5,
        'text' => 'Literally the best catering I\'ve ever had! The salmon was sublime and I never knew potatoes could taste so good. Wonderful!',
        'time' => 0, // date unknown; the Places API does not return this one
        'precision' => 'month',
    ],
    [
        'source' => 'google',
        'author' => 'Gretchen Geyer',
        'rating' => 5,
        'text' => 'I was recently at an event that they catered. Their food is delicious, their service is excellent, and their staff is professional and friendly. I highly recommend them!',
        'time' => 0, // date unknown; the Places API does not return this one
        'precision' => 'month',
    ],

    // --- Facebook (no API; update by hand) ---
    [
        'source' => 'facebook',
        'author' => 'Shana Kennedy',
        'rating' => 5,
        'text' => 'We hosted a large party this past weekend to celebrate our son\'s graduation. Everything about our experience with Wadadli Flare Catering was exceptional. The food and service was outstanding!',
        'time' => 1719792000, // 2024-07-01
        'precision' => 'day',
    ],
    [
        'source' => 'facebook',
        'author' => 'Fred Rife',
        'rating' => 5,
        'text' => 'Chef Jamie crafts mouth watering smoked ribs that you can\'t stop eating. The spices the Jamie creates are so unique that it keeps you coming back for more. The smoked Mac n cheese is the perfect side along with his other wonderful creations. I would most definitely use Wadadli Flare Catering again and would most definitely recommend to anyone looking for deliciously prepared handcrafted food.',
        'time' => 1691193600, // 2023-08-05
        'precision' => 'day',
    ],
    [
        'source' => 'facebook',
        'author' => 'Jacob C Stephens',
        'rating' => 5,
        'text' => 'Chef Jamie Francis has elevated events he catered to the next level. Recommended.',
        'time' => 1691107200, // 2023-08-04
        'precision' => 'day',
    ],
];
