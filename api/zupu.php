<?php
// 定义 POSTS_DIR 常量
define('POSTS_DIR', __DIR__ . '/');

// 引入 header.php
include 'header.php';

// 引入 Parsedown 解析器
require_once 'Parsedown.php';
$parsedown = new Parsedown();

// 读取 index.md 文件内容
$markdownContent = file_get_contents('pages/index.md');

// 解析元数据和内容
$meta = [];
$content = '';

if ($markdownContent) {
    // 分离元数据和内容
    $parts = preg_split('/\n---\n/', $markdownContent, 2);
    
    if (count($parts) > 1) {
        // 解析元数据部分
        $metaLines = explode("\n", trim($parts[0]));
        foreach ($metaLines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }
        
        // 获取内容部分
        $content = $parsedown->text(trim($parts[1]));
    } else {
        // 如果没有元数据分隔符，直接解析整个内容
        $content = $parsedown->text(trim($markdownContent));
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABB 页面</title>
	<style>
	.profile {
		background-color: var(--oaoo-5, #fff);
		border: 1px solid var(--oaoo-2, #e3e8f7);
	}
	*{font-weight: 400;}
	h4 {margin: 10px 0;}

	</style>
</head>
<body>
    <div class="banner-container">
        {% banner %}
        https://www.oaoo.top/music/images/5.jpg,自然风景01,
        /public/images/linfrom.mp4
        images/example1.jpg,城市风光
        https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4
        {% endbanner %}
    </div>
    
<main style=" margin-top: 50px; ">

<div class="text">
	<h1>林氏起源</h1>
	<p>追溯林氏起源，了解家族发展脉络</p>
	<hr>
</div>

<div class="container">
	<?php if (isset($meta['img'])): ?>
		<div class="image">
			<img src="<?php echo htmlspecialchars($meta['img']); ?>" alt="公司图片">
		</div>
	<?php endif; ?>
<div class="content">
    <div>
        <?php 
        if (isset($content)) {
            // 1. 先获取纯文本（用于计算字数）
            $plainText = strip_tags($content);
            
            // 2. 按字符数组处理（支持中文）
            $chars = preg_split('//u', $plainText, -1, PREG_SPLIT_NO_EMPTY);
            $charCount = count($chars);
            
            // 3. 如果超过 200 字，截取 HTML 内容（保留格式）
            if ($charCount > 180) {
                // 找到第 200 个字符在 HTML 中的位置
                $subPlainText = implode('', array_slice($chars, 0, 180));
                $pos = strpos($content, $subPlainText);
                
                // 截取 HTML 并确保标签闭合
                $shortContent = substr($content, 0, $pos + strlen($subPlainText));
                $shortContent .= '...';
                
                // 防止截断 HTML 标签（可选）
                $shortContent = preg_replace('/<[^>]*$/', '', $shortContent);
            } else {
                $shortContent = $content;
            }
            
            echo $shortContent;
        }
        ?>  
		<p class="more"><a href="page.php?id=1" >更多...</a></p>
    </div>  
</div>
</div>
 
<div class="text">
	<h1>林氏历史名人谱</h1>
	<p>夏商周秦汉 · 魏晋南北朝 · 隋唐五代 · 宋元明清</p>
	<p class="more"><a href="page.php?id=2" >更多...</a></p>
	<hr>
</div>

<div id="profiles-container-only"></div>

<div class="text">
	<h1>岁月印记</h1>
	<p>一砖一瓦藏故事，一草一木寄深情</p>
	<p class="more"><a href="yinji.php" >更多...</a></p>
	<hr>
</div>
<?php
// 在任何页面调用文章列表

require_once 'ArticleHelper.php';
ArticleHelper::showArticleList(4);
?>
</main>
<?php include 'footer.php'; ?>
<script src="/public/Plugin.js"></script>
</body>
</html>