<?php
// タイムゾーンを日本に設定
date_default_timezone_set('Asia/Tokyo');

// 今日の日付を取得する
$today = new DateTime('now');
$today_str = $today->format('Y-m-j');

// 表示する年月を設定する
// 表示したい年月を指定する場合には、URLに含まれるGETパラーメーターとして
// 'ym=2021-08' のようにする
if (isset($_GET['ym'])) {
    $ym = $_GET['ym'];
} else {
    // 指定がない場合今月とする
    $ym = $today->format('Y-m');
}

// 作成するカレンダーのタイムスタンプを作成
// 入力パラメーターが不正の場合strtotimeが失敗するので今月のカレンダーとする
$calendar_date = strtotime($ym.'-01');
if ($calendar_date === false) {
    $ym = $today->format('Y-m');
    $calendar_date = strtotime($ym.'-01');
}

// カレンダーのタイトルを作成
$calendar_title = date('Y年n月', $calendar_date);

// 前月・翌月を表示するためのパラーメーターを作成 yyyy-mm
$prev = date('Y-m', mktime(0, 0, 0, date('m', $calendar_date) - 1, 1, date('Y', $calendar_date)));
$next = date('Y-m', mktime(0, 0, 0, date('m', $calendar_date) + 1, 1, date('Y', $calendar_date)));

// 1日の曜日を取得 0:日曜日 ... 6:土曜日
$day_week = date('w', mktime(0, 0, 0, date('m', $calendar_date), 1, date('Y', $calendar_date)));
// 月曜始まりとするので 0:月曜日 ... 6:日曜日 に変換する
$day_week = ($day_week + 6) % 7;

// 月末日を求める
$eom = date('t', $calendar_date);

// 週ごとのHTML文字列を格納する
$weeks = array();

// 1週目のHTMLを作成
$week = '<tr>';
// 1日までの空セルを追加する
$week .= str_repeat('<td></td>', $day_week);

for ($day = 1; $day <= $eom; $day++) {

    if ($today_str == ($ym.'-'.$day)) {
        // 今日を強調表示する
        $week .= '<td class="today">'.$day.'</td>';
    } elseif ($day_week == 5) {
        // 土曜日
        $week .= '<td class="saturday">'.$day.'</td>';
    } elseif ($day_week == 6) {
        // 日曜日
        $week .= '<td class="sunday">'.$day.'</td>';
    } else {
        $week .= '<td>'.$day.'</td>';
    }

    // 最終日なら週末までの空セルを追加する
    if ($day == $eom) {
        $week .= str_repeat('<td></td>', 6 - $day_week);
        $day_week = 6;
    }

    // 週末ならタグを閉じる
    if ($day_week == 6) {
        $week .= '</tr>';
        $weeks[] = $week;
        $week = '<tr>';
        // 曜日を週初めにリセット
        $day_week = 0;
    } else {
        $day_week++;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>PHPで作ったカレンダー</title>
    <meta name="description" content="Cheap calendar made with PHP">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="calendar">
    <h1><a href="?ym=<?php echo $prev; ?>">&lt;=</a> <?php echo $calendar_title; ?> <a href="?ym=<?php echo $next; ?>">=&gt;</a></h1>
    <table>
        <tr>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th class="saturday">土</th>
            <th class="sunday">日</th>
        </tr>
        <?php
            foreach ($weeks as $week) {
                echo $week;
            }
        ?>
    </table>
</div>
</body>
</html>
