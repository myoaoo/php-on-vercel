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

// 用于存储文件和对应的标题、封面、日期
$filesWithInfo = [];

// 打开文件夹
if ($handle = opendir($directory)) {
    // 遍历文件夹中的文件
    while (($file = readdir($handle)) !== false) {
        // 过滤掉 "." 和 ".." 文件夹
        if ($file !== "." && $file !== "..") {
            // 检查文件扩展名是否为 .md
            if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                $filePath = $directory . $file;
                $markdownContent = file_get_contents($filePath);

                // 提取元数据
                preg_match('/^---\s*(.*?)\s*---/s', $markdownContent, $matches);
                $metadata = [];
                if (isset($matches[1])) {
                    $lines = explode("\n", $matches[1]);
                    foreach ($lines as $line) {
                        if (strpos($line, ':')!== false) {
                            list($key, $value) = explode(':', $line, 2);
                            $metadata[trim($key)] = trim($value);
                        }
                    }
                }

                // 获取标题，如果没有标题则使用文件名
                $title = isset($metadata['title'])? $metadata['title'] : $file;

                // 获取封面图
                if (isset($metadata['cover'])) {
                    $cover = $metadata['cover'];
                } else {
                    $imageDir = __DIR__ . '/source/images/';
                    $images = glob($imageDir . '*');
                    if (!empty($images)) {
                        $cover = str_replace(__DIR__, '', $images[array_rand($images)]); // 修正图片路径
                    } else {
                        $cover = '';
                    }
                }

                // 获取日期
                $date = isset($metadata['date'])? $metadata['date'] : '';

                // 获取 link 值
                $linkValue = isset($metadata['link'])? $metadata['link'] : null;

                // 存储文件和对应的信息
                $filesWithInfo[$file] = [
                    'title' => $title,
                    'cover' => $cover,
                    'date' => $date,
                    'password' => isset($metadata['mima'])? $metadata['mima'] : null,
                    'link' => $linkValue
                ];

                // 输出标题、封面图、日期并提供链接
                echo '<div>';
                if (!empty($cover)) {
                    echo '<img src="'. htmlspecialchars($cover). '" alt="Cover" style="max-width: 200px;"><br>';
                }
                if ($linkValue) {
                    echo '<a href="?file='. urlencode($linkValue). '">'. htmlspecialchars($title). '</a><br>';
                } else {
                    echo '<a href="?file='. urlencode($file). '">'. htmlspecialchars($title). '</a><br>';
                }
                if (!empty($date)) {
                    echo '日期: '. htmlspecialchars($date). '<br>';
                }
                echo '</div>';
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
    // 这里需要根据实际情况处理 link 值与文件的映射关系
    // 假设 link 值与文件名有对应关系，需要额外逻辑处理
    $filePath = $directory . $file; 

    // 检查文件是否存在
    if (file_exists($filePath)) {
        $markdownContent = file_get_contents($filePath);

        // 提取元数据
        preg_match('/^---\s*(.*?)\s*---/s', $markdownContent, $matches);
        $metadata = [];
        if (isset($matches[1])) {
            $lines = explode("\n", $matches[1]);
            foreach ($lines as $line) {
                if (strpos($line, ':')!== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $metadata[trim($key)] = trim($value);
                }
            }
        }

        // 检查是否需要密码
        if (isset($metadata['mima'])) {
            if (!isset($_POST['password']) || $_POST['password']!== $metadata['mima']) {
                echo '<form method="post">';
                echo '<label for="password">请输入密码:</label>';
                echo '<input type="password" id="password" name="password">';
                echo '<input type="submit" value="提交">';
                echo '</form>';
                exit;
            }
        }

        // 移除元数据部分
        $markdownContent = preg_replace('/^---\s*(.*?)\s*---/s', '', $markdownContent);

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