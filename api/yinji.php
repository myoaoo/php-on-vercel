<?php
// yinji.php

require_once 'ArticleHelper.php';
$articleHelper = new ArticleHelper();

// 定义常量
define('POSTS_DIR', __DIR__ . '/');

// 处理请求
$requestedId = $_GET['id'] ?? null;

// 开始输出缓冲
ob_start();

// 输出页面内容
if ($requestedId) {
    // 显示单篇文章
    $article = $articleHelper->getArticleById($requestedId);
    
    if (!$article) {
        die('未找到指定ID的文章');
    }
    
    $pageTitle = htmlspecialchars($article['title']);
    ?>
    <div class="article-container">
        <?php if ($article['img']): ?>
            <img src="<?= htmlspecialchars($article['img']) ?>" class="article-cover" alt="<?= htmlspecialchars($article['title']) ?>">
        <?php endif; ?>
        <h1><?= htmlspecialchars($article['title']) ?></h1>
        <div class="article-content">
            <?= $article['content'] ?>
        </div>
    </div>
    <?php
} else {
    // 显示文章列表
    $articles = $articleHelper->getAllArticles();
    $pageTitle = '岁月印记';
    ?>
    <?= $articleHelper->renderArticleList($articles) ?>
    <?php
}

// 获取并清空缓冲区内容
$pageContent = ob_get_clean();

// 输出公共HTML结构
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?></title>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
	<div class="post">
    <?= $pageContent ?>
	</div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>