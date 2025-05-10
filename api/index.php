<?php
session_start();

// Handle logout first
if (isset($_GET['logout'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: ".strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'lin' && $password === '123') {
        $_SESSION['logged_in'] = true;
    } else {
        $login_error = '帐号或密码错误，请核对后重新输入！';
    }
}

$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>林氏成员信息库</title>
</head>
<body>
  <?php if ($logged_in): ?>
<style>
    * {
      margin: 0;
      padding: 0;
      font-size: 15px;
	  text-decoration: none;
    }


    strong {
      font-size: 1.2em;
    }

    /* Common styles for both layouts */
    .tree li .aa {
      text-decoration: none;
      color: #666;
      font-family: arial, verdana, tahoma;

      display: inline-block;
      border-radius: 5px;
      -webkit-border-radius: 5px;
      -moz-border-radius: 5px;
      transition: all 0.5s;
      -webkit-transition: all 0.5s;
      -moz-transition: all 0.5s;
      white-space: nowrap;
      border: 1px solid #ccc;
      padding: 5px;
    }

    .tree li .aa .left,
    .tree li .aa .right {
      display: inline-block;
      /* 修改为 inline-block 实现水平布局 */
      width: 60px;
      /* 设置固定宽度 */
      height: 80px;
      /* 设置固定高度 */
      vertical-align: top;
      /* 顶部对齐 */
      padding: 5px;
      box-sizing: border-box;
      /* 包含内边距和边框 */
    }

    .tree li .aa .right {
      margin-left: 5px;
      padding-left: 5px;
    }


    .tree li .aa .image-container {
      width: 50px;
      height: 50px;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      margin: 0 auto 5px auto;
      /* 居中显示 */
      border-radius: 50%;
    }

    .Searchmore .image-container img,
    .tree li .aa .image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 10px;
    }

    .tree li .aa p {
      margin: 0;
      padding: 0;
      line-height: 1.2;
      text-align: center;
    }

    .tree li .aa:hover,
    .tree li .aa:hover+ul li .aa {
      background: #c8e4f8;
      color: #000;
      border: 1px solid #94a0b4;
    }

    .tree li .aa:hover+ul li::after,
    .tree li .aa:hover+ul li::before,
    .tree li .aa:hover+ul::before,
    .tree li .aa:hover+ul ul::before {
      border-color: #94a0b4;
    }

    /* Horizontal layout */
    .tree.horizontal {
      width: 100%;
      overflow-x: auto;
      height: calc(100Vh - 40px);
    }

    .tree.horizontal ul {
      padding: 20px;
      position: relative;
      transition: all 0.5s;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .tree.horizontal li {
      text-align: center;
      list-style-type: none;
      position: relative;
      padding: 5px 0 5px 20px;
      display: flex;
      transition: all 0.5s;
    }

    .tree.horizontal li::before,
    .tree.horizontal li::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 50%;
      border-left: 1px solid #ccc;
      width: 20px;
      height: 50%;
    }

    .tree.horizontal li::after {
      bottom: auto;
      top: 50%;
      border-top: 1px solid #ccc;
    }

    .tree.horizontal li:only-child::after,
    .tree.horizontal li:only-child::before {
      display: none;
    }

    .tree.horizontal li:only-child {
      padding-left: 0;
    }

    .tree.horizontal li:first-child::before,
    .tree.horizontal li:last-child::after {
      border: 0 none;
    }

    .tree.horizontal li:last-child::before {
      border-bottom: 1px solid #ccc;
      border-radius: 0 0 5px 0;
    }

    .tree.horizontal li:first-child::after {
      border-radius: 5px 0 0 0;
    }

    .tree.horizontal ul ul::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      border-top: 1px solid #ccc;
      width: 20px;
      height: 0;
    }

    .tree.horizontal li .aa {
      align-self: center;
    }

    /* Vertical layout */
    .tree.vertical {
      width: 100%;
      overflow-x: auto;
      height: calc(100Vh - 40px);
    }

    .tree.vertical ul {
      padding: 20px;
      position: relative;
      transition: all 0.5s;
      white-space: nowrap;
      text-align: center;
    }

    .tree.vertical li {
      display: inline-block;
      text-align: center;
      list-style-type: none;
      position: relative;
      padding: 20px 5px 0 5px;
      transition: all 0.5s;
      vertical-align: top;
      white-space: normal;
    }

    .tree.vertical li::before,
    .tree.vertical li::after {
      content: '';
      position: absolute;
      top: 0;
      right: 50%;
      border-top: 1px solid #ccc;
      width: 50%;
      height: 20px;
    }

    .tree.vertical li::after {
      right: auto;
      left: 50%;
      border-left: 1px solid #ccc;
    }

    .tree.vertical li:only-child::after,
    .tree.vertical li:only-child::before {
      display: none;
    }

    .tree.vertical li:only-child {
      padding-top: 0;
    }

    .tree.vertical li:first-child::before,
    .tree.vertical li:last-child::after {
      border: 0 none;
    }

    .tree.vertical li:last-child::before {
      border-right: 1px solid #ccc;
      border-radius: 0 5px 0 0;
    }

    .tree.vertical li:first-child::after {
      border-radius: 5px 0 0 0;
    }

    .tree.vertical ul ul::before {
      content: '';
      position: absolute;
      top: 0;
      left: 50%;
      border-left: 1px solid #ccc;
      width: 0;
      height: 20px;
    }

    /* Toggle button styles */
    .toggle-container {
      text-align: center;
      padding: 10px;
	  background-color: #007bff;
	  color: #fff;
    }
	.toggle-container a {color: #fff;}

    .toggle-btn {
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
	  background-color: transparent;
	  margin-right: 20px;
    }
	.toggle-container a:hover,
    .toggle-btn:hover {
      color:#00f5ff;
    }

    /* Search box styles */
    #searchBox {
      display: none;
      position: absolute;
      top: 60px;
      left: 50%;
      width: 600px;
      max-width: 80%;
      transform: translateX(-50%);
      background: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    #searchInput {
      padding: 8px;
      width: calc(100% - 16px);
      border: 1px solid #ccc;
      border-radius: 4px;
      outline: none;
    }

    #searchResults {
      max-height: 70vh;
      overflow-y: auto;
      margin-top: 10px;
    }

    .search-result {
      border: 1px solid #eee;
      margin-bottom: 15px;
      border-radius: 10px;
      cursor: pointer;
    }

    .search-result:hover {
      background-color: #f5f5f5;
    }

    /* Add to your existing styles */
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 100;
      display: none;
    }

    .more {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 10px;
      border-radius: 5px;
      z-index: 101;
      width: 500px;
      height: 150px;
      max-width: 80%;
      max-height: 80%;
      overflow: auto;
    }

    .more .image-container {
      width: 100px !important;
      height: 150px !important;
      margin: 0 !important;
      border-radius: 10px !important;
    }

    .more h1 {
      text-align: center;
      margin-bottom: 10px;
    }

    .desc {
      margin-left: 20px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      width: calc(100% - 100px);
      height: 150px;
      overflow-y: auto;
    }

    .Searchmore .desc {
      margin: 10px;
      height: 100px;
      overflow-y: auto;
    }

    .desc p {
      word-wrap: break-word;
      /* 保证长单词可以换行 */
      overflow-wrap: break-word;
      /* 防止超出容器 */
      white-space: normal;
      /* 允许换行 */
      text-align: left !important;
      margin-bottom: 5px !important;
    }

    .Searchmore .image-container {
      width: 90px;
      height: 120px;
      display: flex;
      overflow: hidden;

    }

    .Searchmore {

      display: flex;

    }
  </style>
    <style>
        .announcement {
            position: fixed;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fff;
            color: red;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
			width: 100%;
            max-width: 80%;position: absolute;
			letter-spacing: 1px;
			line-height: 1.8;
        }

        
		.announcement-close {
			position: absolute;
			top: 5px;          /* 距离顶部 5px */
			right: 10px;       /* 距离右侧 10px */
			font-weight: bold;
			font-size: 18px;
			cursor: pointer;
		}
        
        .announcement-close:hover {
            color: #000;
        }
    </style>  
    <div class="announcement" id="announcement">
        <span >
		<p>尊敬的各位亲友：</p>
		<p>&emsp;&emsp;衷心感谢大家此前提供的西坡村林氏人员信息！由于个人能力和精力有限，再加上一些家庭成员的特殊情况，当前整理的资料还不够完善。请各位在百忙中抽空核对一下自己家族成员的信息，如有遗漏或错误，或者了解其他宗亲的情况，还请不吝告知。<br>&emsp;&emsp;您的每一次核对和补充，都是为我们子孙后代留下珍贵的家族记忆，帮助大家更好地认祖归宗，也为后续的族谱制作提供宝贵的支持。再次感谢亲友们的帮助！</p>
		<p>整理人：林廷佳 (13068104364 / 微信同号)</p>	
		</span>
        <span class="announcement-close">&times;</span>
    </div> 
 <div class="toggle-container">
    <button class="toggle-btn" id="toggleLayout">左右布局</button>
    <button class="toggle-btn" id="searchButton">搜索</button>
	<a href="?logout=1">退出登录</a>
  </div>

  <div id="searchBox">
    <input type="text" id="searchInput" placeholder="Enter search term...">
    <div id="searchResults"></div>
  </div>

  <div class="tree horizontal" id="familyTree"></div>

 <script>
// 定义家族树数据 名字,出生,电话,图片,描述,更多描述   名字,,,描述
const familyTree = `
未知
	林时昌,,三兄弟之一，暂不知 林茂扬 是谁的后代,
	林时芳,,三兄弟之一，暂不知 林茂扬 是谁的后代,
	林时茂,,三兄弟之一，暂不知 林茂扬 是不是后代,
		林茂扬,,;未知,
			林振华,;吴翠琼,,,茂名人，随生产队来到高西生产队，在生产过程中与先祖林进华认识，并产下四儿三女
				林隆海,~2025,;欧朝建,,15914610339,出生于广东省曲界镇西坡村
					林春花,1980.01,1371568867,,出生于广东省曲界镇西坡村，膝下育两个女儿，现在中山市工作;金康富,,13924979779,浙江人，现居住中山市
						金雪怡,,
						金伊辰,
					林春香,1982.07,13549877674,,出生于广东省曲界镇西坡村，膝下育一儿一女，现在中山市工作;王敏侠,1970.05,13726020838,中山市宫花村人
						王泽勇,2006.07,,boy.svg
						王昕妤,2016.11,,,wáng xīn yú
					林廷佳,1984.10,13068104364,,出生于广东省曲界镇西坡村，膝下育一儿一女，现在中山市工作;游凤春,1989-,13112968142,广东省前山镇山狗吼村，被人领养
						林彦彤,2017~,生于2017年2月14日
						林昌平,2018~,生于2018年7月13日
					林春果,1986,13549947032,出生于广东省曲界镇西坡村，膝下育一儿三女，现在广西陆川工作;龙华林,广西人
						龙慧敏,
						龙丽萍,,1915270890
						龙慧语,
						龙杰文,,,boy.svg
				林可少,,,girl.svg,少时在曲界镇竹林寺出家，法号释善贤
				林隆陆,,15875940639,已退休，现居住广东省曲界镇西坡村;蓝小鹏,,13420123916,,出生于广东省曲界镇调六村，现曲界中心小学教书
					林思思,,18813630474,出生于广东省曲界镇西坡村，膝下育一儿，现在在雷州市工作;朱轩民,,17507595896
						朱泓宇,,,boy.svg,zhū hóng yǔ
					林廷楠,,15766400663,出生于广东省曲界镇西坡村，现在在深圳市工作;曾惠云,,15767497594
					
				林隆空,;吴中荣,,,出生于广东省曲界镇马家村
					林春苗,,18721181459,出生于广东省曲界镇西坡村，膝下育一儿一女，现在上海市工作;尤振伟,,13771989181,江苏省
						尤泽翰,,,boy.svg,yóu zé hàn 
						尤烨瑄,,,,yóu yè xuān
					林春蕾,,15800450141,出生于广东省曲界镇西坡村，膝下育一女，现在上海市工作;胡伟,,18621635228,江西省
						胡林菲,
					林廷立,,18218940039
					林廷奇,,18820431120,出生于广东省曲界镇西坡村，膝下育一女，现在上海市工作;孙桂华,,电话：18675986446
						林清芸,
				林隆军,,13432880586;戴玉锦,,15816049902,出生于广东省曲界镇龙门村
					林廷杰,,15099992263,出生于广东省曲界镇西坡村，膝下育一儿，现在曲界镇华丰糖厂工作;戴清品,,15875983695
						林昌樾,,,,lín chāng yuè
					林春银,,
					林彩虹,,
				林少微,,15768671379,出生于广东省曲界镇西坡村，现在居住曲界镇金满堂，膝下育一女两儿;刘家尊,,13435229313,出生于广东省曲界镇金满堂
					刘婷婷,,18402062900出生于广东省曲界镇金满堂，膝下育一儿，现在惠州工作;曾志鸿,,电话：15099919964
						曾景颢,,,boy.svg,zēng jǐng hào
					刘之鼎,,13420151909,boy.svg
					刘之立,,18211513240,boy.svg
				林少兰,,,出生于广东省曲界镇西坡村，现在居住徐闻县，膝下育一女一儿;游相耀,,,出生于广东省前山镇山狗吼村
					游姗姗,
					游国辉,,,boy.svg
					
			林振达,;不详,
				林隆世,;*洪珠,
					林珍,;未知,
						未知,
					林廷卓,;未知,
						林昌**,
						林昌**,
				林隆光,;欧淑娟,
					林修文,1984~,,,出生于广东省曲界镇西坡村，现在在珠海市工作
					林廷乔,;莫美琼,
						林思颖,
						林昌烨,,,,lín chāng yè
					林琳,;樊家活,,,,fán jiā huó 
						樊明慧,
						樊友钺,,,,fán yǒu yuè
			林振达,;林惠连,
				林屏,;董祥安,
					董译励,
					董坤美,
					董译靖,
					董译泽,,,boy.svg
				林闯,;谭水贵,
					谭斯尹,
					谭舒,
					谭斐,,,,tán fěi
					谭翔,,,boy.svg
				林隆保,,,出生于广东省曲界镇下桥村，膝下育两儿，现在 ** 里工作;王丽曼,,
					林廷濠,,,,lín tíng háo
					林廷川,
					
			林振德,,育有6个子女，其中符方勤被抱养; 欧帮舜,
				林少杏,1964~,13729151018,出生于西坡村，膝下一儿一女，现居住红星农场十二队;王大辉,,
					王德君,,,boy.svg
					王思慧,
				林少霞,1966~,17820417542,出生于西坡村，膝下一子女，现居住曲界镇华丰糖厂;张贵尧,,
					张悦文,
				林少眉,1970~,13172083702,出生于西坡村，膝下两儿，现居住居住广州市;陈真胜,,
					陈威,,,boy.svg
					陈多,,,boy.svg		
				林少玉,1972~,18218593661,出生于西坡村，膝下两儿一女，现居住雷州市杨家镇扶桥西村;纪明券,,
					纪力勇,,,boy.svg
					纪乃诗,
					纪力威,,,boy.svg
				符方勤,1974~,,信息不社
				林隆业,1974~,13434641608,出生于西坡村，膝下一儿一女，现居住曲界镇西坡村;王妃米,,
					林小靖,
					林廷顺,
				
			林振诗; 资料未提供	
				林隆浩,;未知,
					未知,
				林*舰,;未知,
					未知,
				林隆伦,;未知,
					未知,
				林*荣,;未知,
					未知,
					
			林振良,,; *少* ,
				林隆祥,;何琴,
					林廷凤,,华侨姐;未知,
					林廷辉,;*洪英,
						林** ,
						林玉丽,
						林昌慧,
					林廷煌,;
					林廷南?,
				林**,
				其它,
				
	林时*,
		林茂*,
			林振*,
				林隆发,;*妹二,
					林*妹,,出嫁本村;未知,
					林廷种,
					林廷*,,木匠弟			
				林隆庭,,养子，;*锦英,
					林少葵,;未知,
					林少*,;未知,
					林少*,;未知,
					林少敏,;未知,
					林少愧,;未知,
					林廷就,;未知,
						林昌**,
						林昌**,		
					
`;


// 新增变量：图片前缀
const IMAGE_PREFIX = 'https://zimg.oaoo.top/images/';

// 特殊字符数组
const specialChars = ['时', '茂', '振', '隆', '廷', '昌'];
let currentImage = null;

// 添加一个变量来设置默认布局，true 1 表示垂直布局，false 0表示水平布局
const defaultVerticalLayout = 1;

// 为图片URL添加前缀
function addImagePrefix(url) {
    // 检查是否是完整URL，如果是则不添加前缀
    if (url.startsWith('http://') || url.startsWith('https://')) {
        return url;
    }
    return IMAGE_PREFIX + url;
}

// 解析文本数据并生成树结构
function parseTreeData(textData) {
    const lines = textData.trim().split('\n').filter(line => line.trim()!== ''); // 忽略空行
    const root = { name: '', children: [] };
    const stack = [{ level: -1, node: root }];
    lines.forEach(line => {
        const level = line.match(/^\t*/)[0].length;
        const name = line.trim();
        while (level <= stack[stack.length - 1].level) {
            stack.pop();
        }
        const parent = stack[stack.length - 1].node;
        const newNode = { name, children: [] };
        parent.children.push(newNode);
        stack.push({ level, node: newNode });
    });
    return root.children[0];
}

// 检查是否有自定义图片
function getCustomImage(content) {
    const parts = content.split(',');
    // 查找第一个图片路径
    for (let i = 2; i < parts.length; i++) {
        const part = parts[i].trim();
        if (part.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
            return {
                image: part,
                content: parts.filter((_, index) => index!== i).join(',').trim()
            };
        }
    }
    return null;
}

// 生成节点内容部分
// 生成节点内容部分
function generateContentSection(content, isLeft = true) {
    const customImage = getCustomImage(content);
    let actualContent = content;
    let imageSrc = 'girl.svg'; // Default to girl

    if (customImage) {
        actualContent = customImage.content || ' ';
        imageSrc = customImage.image;
    } else {
        if (isLeft) {
            // 左侧节点：根据特殊字符判断性别
            const hasSpecialChar = specialChars.some(char => content.includes(char));
            imageSrc = hasSpecialChar? 'boy.svg' : 'girl.svg';
            // 记录左侧图片，供右侧节点使用
            currentImage = imageSrc;
        } else {
            // 右侧节点：根据左侧图片决定使用相反性别的图片
            imageSrc = currentImage === 'boy.svg'? 'girl.svg' : 'boy.svg';
        }
    }

    // 添加图片前缀
    imageSrc = addImagePrefix(imageSrc);

    const contentParts = actualContent.split(',');
    let contentHtml = `<p>${contentParts[0] || ''}</p>`;
    let moreHtml = '';

    if (contentParts.length > 1) {
        moreHtml = `<div class="more">
          <div class="image-container">
            <img src="${imageSrc}" alt="节点图片">
          </div>
          <div class="desc"><p><strong>${contentParts[0] || ''}</strong>  ${contentParts[1] || ''}</p><p>电话：${contentParts[2] || ''}</p><p>个人信息：</p>`;

        // 从第三部分开始循环显示内容
        for (let i = 3; i < contentParts.length; i++) {
            moreHtml += `<p>${contentParts[i] || ''}</p>`;
        }
        moreHtml += `</div></div>`;
    }

    return `
        <div class="${isLeft? 'left' : 'right'}">
          <div class="image-container">
            <img src="${imageSrc}" alt="节点图片">
          </div>
          ${contentHtml}
          ${moreHtml}
        </div>
      `;
}

// 生成节点内容
function generateNodeContent(name) {
    if (name.includes(';')) {
        const parts = name.split(';');
        const leftContent = parts[0].trim()? generateContentSection(parts[0].trim(), true) :
            generateContentSection('空', true);

        const rightContent = parts.length > 1 && parts[1].trim()?
            generateContentSection(parts[1].trim(), false) : '';

        return `<div class="aa">${leftContent}${rightContent}</div>`;
    } else {
        return `<div class="aa">${generateContentSection(name, true)}</div>`;
    }
}

// 生成家族树HTML
function generateFamilyTree(node, isRoot = true) {
    let html = isRoot? '<ul>' : '';
    html += '<li>';
    html += generateNodeContent(node.name);
    if (node.children && node.children.length > 0) {
        html += '<ul>';
        node.children.forEach(child => {
            html += generateFamilyTree(child, false);
        });
        html += '</ul>';
    }
    html += '</li>';
    return isRoot? html + '</ul>' : html;
}

// 解析数据并生成家族树
const treeData = parseTreeData(familyTree);
document.getElementById('familyTree').innerHTML = generateFamilyTree(treeData);

// 切换布局的函数
function toggleLayout() {
    const treeElement = document.getElementById('familyTree');
    const toggleBtn = document.getElementById('toggleLayout');

    if (treeElement.classList.contains('horizontal')) {
        treeElement.classList.remove('horizontal');
        treeElement.classList.add('vertical');
        toggleBtn.textContent = '上下布局';
    } else {
        treeElement.classList.remove('vertical');
        treeElement.classList.add('horizontal');
        toggleBtn.textContent = '左右布局';
    }
}

// 添加按钮点击事件
document.getElementById('toggleLayout').addEventListener('click', toggleLayout);

// 设置默认布局
if (defaultVerticalLayout) {
    const treeElement = document.getElementById('familyTree');
    const toggleBtn = document.getElementById('toggleLayout');
    treeElement.classList.remove('horizontal');
    treeElement.classList.add('vertical');
    toggleBtn.textContent = '上下布局';
}

// Search functionality
document.getElementById('searchButton').addEventListener('click', function (e) {
    const searchBox = document.getElementById('searchBox');
    searchBox.style.display = searchBox.style.display === 'block'? 'none' : 'block';
    if (searchBox.style.display === 'block') {
        document.getElementById('searchInput').focus();
    }
    e.stopPropagation();
});

document.getElementById('searchBox').addEventListener('click', function (e) {
    e.stopPropagation();
});

document.addEventListener('click', function () {
    document.getElementById('searchBox').style.display = 'none';
});

function searchFamilyTree() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const results = document.getElementById('searchResults');
    results.innerHTML = '';

    if (input === '') return;

    const lines = familyTree.split('\n');
    const matches = [];

    lines.forEach(line => {
        const items = line.split(';').map(item => item.trim());
        items.forEach((item, index) => {
            if (item.toLowerCase().includes(input)) {
                const parts = item.split(',');
                const name = parts[0].trim();
                
                // 提取自定义图片（如果有）
                let customImage = null;
                const filteredParts = [];
                
                for (let i = 1; i < parts.length; i++) {
                    const part = parts[i].trim();
                    if (part.match(/\.(jpg|jpeg|png|gif|svg)$/i)) {
                        customImage = part;
                    } else {
                        filteredParts.push(part);
                    }
                }
                
                // 判断性别并选择默认图片 - 这里需要与主树结构一致
                let image = 'girl.svg';
                if (customImage) {
                    image = customImage;
                } else {
                    // 如果是右侧节点(index > 0)，则使用相反的性别图片
                    if (index > 0) {
                        // 查找左侧节点是否有特殊字符
                        const leftItem = items[0];
                        const leftParts = leftItem.split(',');
                        const leftName = leftParts[0].trim();
                        const hasSpecialChar = specialChars.some(char => leftName.includes(char));
                        image = hasSpecialChar ? 'girl.svg' : 'boy.svg';
                    } else {
                        // 左侧节点：根据特殊字符判断性别
                        const hasSpecialChar = specialChars.some(char => name.includes(char));
                        image = hasSpecialChar ? 'boy.svg' : 'girl.svg';
                    }
                }
                
                // 添加图片前缀
                image = addImagePrefix(image);

                matches.push({
                    name,
                    image,
                    part1: filteredParts[0] || '',
                    part2: filteredParts[1] || '',
                    part3: filteredParts[2] || '',
                    // 剩余部分作为描述内容
                    details: filteredParts.slice(2)
                });
            }
        });
    });

    if (matches.length === 0) {
        results.innerHTML = '<div class="search-result">No results found</div>';
        return;
    }

    matches.forEach(match => {
        const resultDiv = document.createElement('div');
        resultDiv.className = 'search-result';

        let detailsHtml = '';
        match.details.forEach((detail, index) => {
            detailsHtml += `<p>${detail || ''}</p>`;
        });

        resultDiv.innerHTML = `
          <div class="Searchmore">
            <div class="image-container">
              <img src="${match.image}" alt="节点图片">
            </div>
            <div class="desc">
              <p><strong>${match.name}</strong>  ${match.part1? `${match.part1}` : ''}</p><p>电话：${match.part2? `${match.part2}` : ''}</p><p>个人信息：</p>
              ${match.part3? `<p>${match.part3}</p>` : ''}
              ${detailsHtml}
            </div>
          </div>
        `;

        resultDiv.addEventListener('click', function () {
            document.getElementById('searchBox').style.display = 'none';
        });

        results.appendChild(resultDiv);
    });
}

document.getElementById('searchInput').addEventListener('input', searchFamilyTree);

// 点击显示更多信息
document.getElementById('familyTree').addEventListener('click', function (e) {
    const leftRightDiv = e.target.closest('.left, .right');
    if (leftRightDiv) {
        const moreDiv = leftRightDiv.querySelector('.more');
        if (moreDiv) {
            const existingOverlay = document.querySelector('.overlay');
            if (existingOverlay) {
                existingOverlay.style.display = 'none';
                document.body.removeChild(existingOverlay);
            }

            const overlay = document.createElement('div');
            overlay.className = 'overlay';
            document.body.appendChild(overlay);

            moreDiv.style.display = 'flex';
            overlay.style.display = 'flex';

            overlay.addEventListener('click', function () {
                moreDiv.style.display = 'none';
                overlay.style.display = 'none';
                document.body.removeChild(overlay);
            });
        }
    }
});
  </script> 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const announcement = document.getElementById('announcement');
            const closeBtn = announcement.querySelector('.announcement-close');
            
            // 点击关闭按钮关闭公告
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation(); // 阻止事件冒泡，避免触发 document 的点击事件
                announcement.style.display = 'none';
            });
            
            // 点击公告区域本身关闭公告
            announcement.addEventListener('click', function() {
                announcement.style.display = 'none';
            });
            
            // 点击页面任意位置（公告区域外）关闭公告
            document.addEventListener('click', function(e) {
                // 如果点击的不是公告区域，则关闭公告
                if (!announcement.contains(e.target)) {
                    announcement.style.display = 'none';
                }
            });
        });
    </script>
  <?php else: ?>
 <style>
    /* 全局样式 */
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
	  flex-direction: column;
      min-height: 100vh;
    }

    h1 {
      color: #333;
      text-align: center;
    }

    form {
      background-color: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
      width: 300px;
      text-align: center; /* 让表单内元素整体居中 */
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #666;
	      text-align: left;
    }

    input[type="text"],
    input[type="password"] {
      width: calc(100% - 22px);
	  outline: none;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 3px;
    }

    button[type="submit"] {
      background-color: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      display: block; /* 让按钮独占一行 */
      margin: 0 auto; /* 水平居中 */
    }

    p {
      color: #333;
    }
	.hy {
		background-color: #007bff;
		color: white;
	    width: 320px;
		padding: 10px;
		margin-bottom: 20px;
		text-align: center;
		border-radius: 5px;
		letter-spacing: 1px; 
	}

  </style>


    <?php
      echo '<div class="hy">欢迎来到西坡村林氏成员信息库</div>';
    ?>
    <form method="POST">
      <label for="username">帐号</label>
      <input type="text" id="username" name="username" required><br>
      <label for="password">密码</label>
      <input type="password" id="password" name="password" required><br>
      <button type="submit">登录</button>
    </form>
	<p style="font-size:1.2em;color: #007bff;">注意：为了保护信息安全  密码 2025.05.20 后过期</p>
        <?php if (isset($login_error)): ?>
		<p style="color:red;"><?php echo htmlspecialchars($login_error); ?></p>
		<?php endif; ?>
  <?php endif; ?>
</body>
</html>