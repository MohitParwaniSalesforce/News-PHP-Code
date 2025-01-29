<?php
require 'config.php';
// Fetch and store the latest news if it hasn't been stored yet
$apiKey = "0301b9924c6213ba5258659b4a8864b4";
$apiUrl = "https://gnews.io/api/v4/search";
$query = "everything"; // Query for fetching news

// Function to fetch news and store in the database
function fetchAndStoreNews($pdo, $apiKey, $apiUrl, $query) {
    // Fetch news from API
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

            // Insert news into database
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO news (title, description, content, image_url, published_at, source_name, source_url) 
                VALUES (?, ?, ?, ?, ?, ?,?)
            ");
            $stmt->execute([$title, $description, $content, $imageUrl, $publishedAt, $sourceName, $sourceUrl]);
        }
       // echo "News fetched and stored successfully!";
    } else {
        echo "Failed to fetch news from API.";
    }
}

// Fetch and store the news on each page load
fetchAndStoreNews($pdo, $apiKey, $apiUrl, $query);

// Check if a search query is provided
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// If there's a search query, fetch news based on it
if ($searchQuery) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE title LIKE ? OR description LIKE ? ORDER BY published_at DESC");
    $stmt->execute(['%' . $searchQuery . '%', '%' . $searchQuery . '%']);
} else {
    // If no search query, fetch all news
    $stmt = $pdo->query("SELECT * FROM news ORDER BY published_at DESC");
}

$newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incodev News</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Company Logo with Home Link -->
    <a href="index.php" class="company-logo">
        <img src="Image/incodevtech.png" alt="Company logo">
    </a>

    <h1>Latest News</h1>

    <!-- Search Form -->
    <form method="get" action="index.php">
        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search news..." />
        <button type="submit">Search</button>
    </form>

    <div class="news-container">
        <?php if (empty($newsList)): ?>
            <p>No news found for your search query.</p>
        <?php else: ?>
            <?php foreach ($newsList as $news): ?>
                <div class="news-item">
                    <img src="<?= htmlspecialchars($news['image_url']) ?>" alt="News Image" style="width:100%; height:auto; border-radius: 5px;">
                    <h2><a href="<?= htmlspecialchars($news['source_url']) ?>" target="_blank"><?= htmlspecialchars($news['title']) ?></a></h2>
                    <p><?= htmlspecialchars($news['description']) ?></p>
                    <small>Published at: <?= htmlspecialchars($news['published_at']) ?> | Source: <?= htmlspecialchars($news['source_name']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
