<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>アクセスカウンタ</title>
    <meta name="description" content="PHPで作ったアクセスカウンタ">
</head>
<body>
    <h1>PHPで作ったアクセスカウンタ</h1>
    <?php
        // PHPファイルを読み込みます
        require_once("CCounter.php");
        // データベースを使う準備をします
        $cnt_db = new CCounter();
        // データベースに接続します
        $cnt_db->connectDb();
        // アクセスカウンタを1加算し、値を取得します
        $count = $cnt_db->countUp();
        // データベースの後始末します
        $cnt_db->closeDb();
        // アクセスカウンタの値を文字で表示します
        echo "アクセスカウンタ：".$count;
    ?>
</body>
</html>
