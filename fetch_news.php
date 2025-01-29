<?php
require 'config.php';

$apiKey = "0301b9924c6213ba5258659b4a8864b4";
$apiUrl = "https://gnews.io/api/v4/search";
$query = "everything";

// Fetch news from the external API
$response = file_get_contents("$apiUrl?q=$query&max=10&apikey=$apiKey");
$newsData = json_decode($response, true);

if ($newsData && isset($newsData['articles'])) {
    foreach ($newsData['articles'] as $article) {
        $title = $article['title'];
        $description = $article['description'];
        $content = $article['content'];
        $imageUrl = $article['image'];
        $publishedAt = $article['publishedAt'];
        $sourceName = $article['source']['name'];
        $sourceUrl = $article['source']['url'];

        // Insert news into the database
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO news (title, description, content, image_url, published_at, source_name, source_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $content, $imageUrl, $publishedAt, $sourceName, $sourceUrl]);
    }
    echo "News fetched and stored successfully!";
} else {
    echo "Failed to fetch news from API.";
}
?>
