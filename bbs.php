<?php
// データベース接続（PHP7以降では機能しない）
// $dbconnection = mysql_connect("localhost", "root", "root");
// if (!$dbconnection) {
//   die('データベースに接続できません：'.mysql_error());
// }

// データベース接続
$dbconnection = new mysqli("localhost", "root", "", "dev_online_bbs");
if ($dbconnection->connect_error) {
  die('データベースに接続できません：'.$dbconnection->connect_error);
}

$errors = array();

// メソッド：POSTなら保存処理実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 名前が正しく入力されているかチェック
  $name = null;
  if (!isset($_POST['name']) || !strlen($_POST['name'])) { // 入力有無、名前の文字数
    $errors['name'] = '名前を入力してください';
  } else if (strlen($_POST['name'] > 40)) { // 文字数制限
    $errors['name'] = '名前は40文字以内で入力してください';
  } else {
    $name = $_POST['name'];
  }

  // ひとことが正しく入力されているかチェック
  $comment = null;
  if (!isset($_POST['comment']) || !strlen($_POST['comment'])) {
    $errors['comment'] = 'ひとことを入力してください';
  } else if (strlen($_POST['comment']) > 200) {
    $errors['comment'] = 'ひとことは200文字以内で入力してください';
  } else {
    $comment = $_POST['comment'];
  }

  // エラーがなければ保存
  if (count($errors) === 0) {
    $sql = "INSERT INTO `post` (`name`, `comment`, `created_at`) VALUES ('"
    .mysqli_real_escape_string($dbconnection, $name)."', '"
    .mysqli_real_escape_string($dbconnection, $comment)."','"
    .date('Y-m-d H:i:s')."')";

    // 保存処理
    mysqli_query($dbconnection, $sql);
    // コメント投稿後に画面がリロードされた際の二重投稿を防ぐ
    header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>ひとこと掲示板</title>
</head>
<body>
  <h1>ひとこと掲示板</h1>

  <form action="bbs.php" method="post">
    <?php if (count($errors)): ?>
    <ul class="error_list">
      <?php foreach ($errors as $error): ?>
      <li>
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    名前：<input type="text" name="name" /><br />
    ひとこと：<input type="text" name="comment" size="60"/><br />
    <input type="submit" name="submit" value="送信" />
  </form>

  <?php
  // 投稿された内容を取得するSQLを作成して結果を取得
  $sql = "SELECT * FROM `post` ORDER BY `created_at` DESC";
  $result = mysqli_query($dbconnection, $sql);
  ?>

  <?php if ($result !== false && mysqli_num_rows($result)): ?>
  <ul>
    <?php while ($post = mysqli_fetch_assoc($result)): ?>
    <li>
      <?php echo htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8'); ?>:
      <?php echo htmlspecialchars($post['comment'], ENT_QUOTES, 'UTF-8'); ?> -
      <?php echo htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8'); ?>
    </li>
    <?php endwhile; ?>
  </ul>
  <?php endif; ?>

  <?php
  // 取得結果を解放して接続を閉じる
  mysqli_free_result($result);
  mysqli_close($dbconnection);
  ?>
</body>
</html>
