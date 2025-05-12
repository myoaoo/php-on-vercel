<?php
// 引入 Parsedown.php 文件
require 'Parsedown.php';

// 设置文件夹路径
$directory = __DIR__ . '/source/_posts/';

// 检查文件夹是否存在
if (!is_dir($directory)) {
    echo "文件夹不存在！";
    exit;
}

// 打开文件夹
if ($handle = opendir($directory)) {
    // 遍历文件夹中的文件
    while (($file = readdir($handle)) !== false) {
        // 过滤掉 "." 和 ".." 文件夹
        if ($file !== "." && $file !== "..") {
            // 检查文件扩展名是否为 .md
            if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                // 输出文件名并提供链接
                echo '<a href="?file=' . urlencode($file) . '">' . htmlspecialchars($file) . '</a><br>';
            }
        }
    }
    closedir($handle);
} else {
    echo "无法打开文件夹！";
}

// 如果有查询参数 file，显示文件内容
if (isset($_GET['file'])) {
    $file = urldecode($_GET['file']);
    $filePath = $directory . $file;

    // 检查文件是否存在
    if (file_exists($filePath)) {
        // 读取文件内容
        $markdownContent = file_get_contents($filePath);

        // 创建 Parsedown 实例并解析 Markdown 内容为 HTML
        $parsedown = new Parsedown();
        $htmlContent = $parsedown->text($markdownContent);

        // 显示 HTML 内容
        echo '<h2>文件内容：</h2>';
        echo $htmlContent;
    } else {
        echo "文件不存在！";
    }
}
?>