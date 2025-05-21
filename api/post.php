<?php
// 博客配置
define('POSTS_DIR', __DIR__ . '/posts/');

// 获取所有 Markdown 文件并按修改时间排序
$files = glob(POSTS_DIR . '/*.md');
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

// 解析 Markdown 文件元数据和内容
function parseMarkdownFile($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    $meta = [];
    $bodyStart = 0;
    
    // 解析 YAML 元数据
    if (isset($lines[0]) && trim($lines[0]) === '---') {
        for ($i = 1; $i < count($lines); $i++) {
            if (trim($lines[$i]) === '---') {
                $bodyStart = $i + 1;
                break;
            }
            $line = trim($lines[$i]);
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }
    }
    
    // 获取文章主体内容
    $body = implode("\n", array_slice($lines, $bodyStart));
    
    return [
        'meta' => $meta,
        'body' => $body,
        'file' => basename($filePath),
        'mtime' => filemtime($filePath)
    ];
}

// 引入 Parsedown 类
require __DIR__ . '/Parsedown.php';
$parsedown = new Parsedown();

// 生成文章ID映射表 (ID => 文件路径)
$postIdMap = [];
foreach ($files as $index => $file) {
    $post = parseMarkdownFile($file);
    // 使用文章中的ID，如果没有则使用索引+1
    $id = isset($post['meta']['id']) ? (int)$post['meta']['id'] : ($index + 1);
    $postIdMap[$id] = $file;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>我的 Markdown 博客</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>



    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- 主内容区 -->
    <main>         
            <div class="grid">
                <?php foreach ($files as $file): ?>
                    <?php 
                    $post = parseMarkdownFile($file);
                    // 计算文章ID
                    $id = isset($post['meta']['id']) ? (int)$post['meta']['id'] : array_search($file, $files) + 1;
                    // 获取封面图
                    $cover = isset($post['meta']['cover']) ? $post['meta']['cover'] : 'https://picsum.photos/600/400';
                    // 检查是否需要密码
                    $isProtected = isset($post['meta']['password']);
                    // 获取日期（优先使用元数据中的日期）
                    $date = isset($post['meta']['date']) ? $post['meta']['date'] : date('Y-m-d', $post['mtime']);
                    ?>
                    <div class="post-list">
                        <div class="up">
                            <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($post['meta']['title'] ?? '封面图'); ?>">
                            <?php if ($isProtected): ?>
                                <div class="absolute top-3 right-3 bg-dark/70">
                                    <i class="fa fa-lock mr-1"></i> 加密
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="down">
                            
                            <h2>
                                <a href="view.php?id=<?php echo $id; ?>" class="">
                                    <?php echo htmlspecialchars($post['meta']['title'] ?? basename($file, '.md')); ?>
                                </a>
                            </h2>

                            <p class="">
                                <?php echo strip_tags($parsedown->text(substr($post['body'], 0, 300))); ?>
                            </p>
                            <div>
                                <div>
									<i class="fas fa-calendar-alt"></i>
									<?php echo $date; ?>
								</div>
								<div>
									<a href="view.php?id=<?php echo $id; ?>" class="">
										阅读更多 <i class="fa fa-arrow-right ml-1"></i>
									</a>
								</div>								
                            </div>							

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 没有文章时的提示 -->
            <?php if (empty($files)): ?>
                <div class="bg-white rounded-xl p-8 text-center">
                    <i class="fa fa-file-text-o text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-500">暂无文章</p>
                </div>
            <?php endif; ?>
    </main>

    <!-- 页脚 -->
    <?php include 'footer.php'; ?>

    <script>
        // 导航栏滚动效果
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('py-2');
                header.classList.remove('py-3');
            } else {
                header.classList.add('py-3');
                header.classList.remove('py-2');
            }
        });
    </script>
</body>
</html>