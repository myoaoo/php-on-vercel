<?php
// 博客配置
define('POSTS_DIR', __DIR__ . '/source/_posts/');

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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#165DFF',
                        secondary: '#69b1ff',
                        neutral: '#f5f7fa',
                        dark: '#1d2129',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .markdown-content h1 {
                @apply text-2xl font-bold mt-6 mb-3;
            }
            .markdown-content h2 {
                @apply text-xl font-bold mt-5 mb-2;
            }
            .markdown-content h3 {
                @apply text-lg font-bold mt-4 mb-1;
            }
            .markdown-content p {
                @apply mb-4 leading-relaxed;
            }
            .markdown-content a {
                @apply text-primary hover:underline;
            }
            .markdown-content ul {
                @apply list-disc pl-5 mb-4;
            }
            .markdown-content ol {
                @apply list-decimal pl-5 mb-4;
            }
            .markdown-content img {
                @apply max-w-full rounded-lg my-4;
            }
        }
       
    </style>
</head>
<body class="bg-neutral text-dark min-h-screen flex flex-col">
    <?php include 'header.php'; ?>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-[clamp(1.75rem,3vw,2.5rem)] font-bold mb-8 text-center">我的 Markdown 博客</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <article class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                        <div class="relative">
                            <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($post['meta']['title'] ?? '封面图'); ?>" class="w-full h-48 object-cover">
                            <?php if ($isProtected): ?>
                                <div class="absolute top-3 right-3 bg-dark/70 text-white text-sm px-3 py-1 rounded-full backdrop-blur-sm">
                                    <i class="fa fa-lock mr-1"></i> 加密
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <div class="text-sm text-primary font-medium mb-2">#<?php echo $id; ?></div>
                            <h2 class="text-xl font-bold mb-2">
                                <a href="view.php?id=<?php echo $id; ?>" class="text-dark hover:text-primary transition-colors duration-200">
                                    <?php echo htmlspecialchars($post['meta']['title'] ?? basename($file, '.md')); ?>
                                </a>
                            </h2>
                            <div class="text-sm text-gray-500 mb-3 flex items-center">
                                <i class="fa fa-calendar-o mr-2"></i>
                                <?php echo $date; ?>
                            </div>
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                <?php echo strip_tags($parsedown->text(substr($post['body'], 0, 300))); ?>
                            </p>
                            <a href="view.php?id=<?php echo $id; ?>" class="inline-flex items-center text-primary hover:text-secondary transition-colors duration-200">
                                阅读更多 <i class="fa fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- 没有文章时的提示 -->
            <?php if (empty($files)): ?>
                <div class="bg-white rounded-xl p-8 text-center">
                    <i class="fa fa-file-text-o text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-500">暂无文章</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <p>&copy; <?php echo date('Y'); ?> My Markdown Blog. All rights reserved.</p>
                <div class="mt-4 flex justify-center space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fa fa-github text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fa fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fa fa-envelope text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

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